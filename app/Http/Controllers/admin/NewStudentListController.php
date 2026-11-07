<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\Master\MasterAcademicYear;
use App\Services\SchoolDatabaseManager;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NewStudentListController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title'       => 'New Student List',
            'folder_path' => 'software.module.new-student-list',
            'route'       => 'new-student-list',
            'permission'  => 'new-student-list-list',
        ];
    }

    public function index(Request $request)
    {
        $this->authorizeList();
        $modules = $this->modules;
        View::share('modules', $modules);
        View::share('sidebar_active', 'new-student-list');

        $filters = $this->filtersFromRequest($request);
        $range = $this->resolveDateRange($filters);
        $submitted = $request->filled('from_ym') || $request->filled('to_ym');
        $students = collect();
        $grouped = collect();

        if ($submitted && $range['from'] && $range['to']) {
            $students = $this->queryStudents($range['from'], $range['to'])->get();
            $grouped = $this->groupByStandardClass($students);
        }

        $rangeLabel = $this->formatRangeLabel($filters);

        return view($modules['folder_path'] . '.index', compact(
            'modules',
            'filters',
            'students',
            'grouped',
            'rangeLabel',
            'submitted',
            'range'
        ));
    }

    public function print(Request $request)
    {
        $this->authorizeList();

        $filters = $this->filtersFromRequest($request);

        $range = $this->resolveDateRange($filters);
        if (!$range['from'] || !$range['to']) {
            return redirect()->route('new-student-list.index')
                ->with('error', 'Please select valid From and To month.');
        }

        $students = $this->queryStudents($range['from'], $range['to'])->get();
        $grouped = $this->groupByStandardClass($students);

        $school = SchoolDatabaseManager::getActiveSchool();
        $schoolName = strtoupper($school['name'] ?? 'Ultra School');
        if (SchoolDatabaseManager::getActiveSchoolCode() === 'ues') {
            $schoolName .= '-KESHOD';
        }

        $rangeLabel = $this->formatRangeLabel($filters);

        return view($this->modules['folder_path'] . '.print', compact(
            'students',
            'grouped',
            'filters',
            'school',
            'schoolName',
            'rangeLabel',
            'range'
        ));
    }

    public function exportExcel(Request $request)
    {
        $this->authorizeList();

        $filters = $this->filtersFromRequest($request);
        $range = $this->resolveDateRange($filters);
        if (!$range['from'] || !$range['to']) {
            return redirect()->route('new-student-list.index')
                ->with('error', 'Please select valid From and To month.');
        }

        $students = $this->queryStudents($range['from'], $range['to'])->get();
        $grouped = $this->groupByStandardClass($students);
        $rangeLabel = $this->formatRangeLabel($filters);

        $school = SchoolDatabaseManager::getActiveSchool();
        $schoolName = strtoupper($school['name'] ?? 'Ultra School');
        if (SchoolDatabaseManager::getActiveSchoolCode() === 'ues') {
            $schoolName .= '-KESHOD';
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('New Student List');
        $sheet->getDefaultRowDimension()->setRowHeight(18);

        // Title block
        $sheet->setCellValue('A1', $schoolName);
        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A2', 'New Student List');
        $sheet->mergeCells('A2:E2');
        $sheet->setCellValue('A3', 'Period : ' . $rangeLabel);
        $sheet->mergeCells('A3:E3');

        $sheet->getRowDimension(1)->setRowHeight(24);
        $sheet->getRowDimension(2)->setRowHeight(20);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->setColor(new Color('1F2580'));
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14)->setColor(new Color('C62828'));
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A1:E3')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        // Column headers
        $headers = ['Sr.No.', 'GR No.', 'Name', 'Adm. Date', 'Note'];
        $sheet->fromArray($headers, null, 'A5');
        $sheet->getStyle('A5:E5')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A5:E5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D6EAF8');
        $sheet->getStyle('A5:E5')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(5)->setRowHeight(20);

        $rowNum = 6;
        $sr = 0;
        foreach ($grouped as $groupKey => $rows) {
            $parts = explode('||', (string) $groupKey);
            $stdName = $parts[0] ?? '-';
            $clsName = $parts[1] ?? '-';

            $sheet->setCellValue("A{$rowNum}", 'Standard : ' . $stdName . '   Class : ' . $clsName);
            $sheet->mergeCells("A{$rowNum}:E{$rowNum}");
            $sheet->getStyle("A{$rowNum}:E{$rowNum}")->getFont()->setBold(true)->setSize(11);
            $sheet->getStyle("A{$rowNum}:E{$rowNum}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D6EAF8');
            $sheet->getStyle("A{$rowNum}:E{$rowNum}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getRowDimension($rowNum)->setRowHeight(20);
            $rowNum++;

            foreach ($rows as $student) {
                $sr++;
                $name = strtoupper(trim(preg_replace(
                    '/\s+/',
                    ' ',
                    ($student->first_name ?? '') . ' ' . ($student->last_name ?? '') . ' ' . ($student->father_name ?? '')
                )));
                $admDate = $student->admission_date
                    ? Carbon::parse($student->admission_date)->format('d-m-Y')
                    : '-';
                $note = '';

                $sheet->setCellValueExplicit("A{$rowNum}", $sr, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicit("B{$rowNum}", (string) ($student->gr_no ?: '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue("C{$rowNum}", $name ?: '-');
                $sheet->setCellValue("D{$rowNum}", $admDate);
                $sheet->setCellValue("E{$rowNum}", $note);

                $sheet->getStyle("A{$rowNum}:B{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("D{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A{$rowNum}:E{$rowNum}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $rowNum++;
            }
        }

        if ($sr === 0) {
            $sheet->setCellValue("A{$rowNum}", 'No records found.');
            $sheet->mergeCells("A{$rowNum}:E{$rowNum}");
            $sheet->getStyle("A{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $rowNum++;
        }

        $lastDataRow = max(5, $rowNum - 1);
        $sheet->getStyle("A5:E{$lastDataRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A5:E{$lastDataRow}")->getFont()->setName('Arial');

        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(42);
        $sheet->getColumnDimension('D')->setWidth(14);
        $sheet->getColumnDimension('E')->setWidth(36);
        $sheet->freezePane('A6');
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);

        $fileName = 'new-student-list-' . now()->format('Ymd-His') . '.xlsx';

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment;filename="' . $fileName . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    protected function queryStudents(Carbon $from, Carbon $to)
    {
        return Admission::query()
            ->with(['currentStandard', 'admissionStandard', 'academicYear'])
            ->where(function ($q) {
                $q->whereNull('status')->orWhereNotIn('status', ['Cancel', 'cancel', 'rejected']);
            })
            ->whereNotNull('admission_date')
            ->whereDate('admission_date', '>=', $from->toDateString())
            ->whereDate('admission_date', '<=', $to->toDateString())
            ->orderBy('current_std_id')
            ->orderBy('division')
            ->orderBy('admission_date')
            ->orderByRaw('CAST(gr_no AS UNSIGNED)')
            ->orderBy('gr_no');
    }

    protected function groupByStandardClass($students)
    {
        return $students->groupBy(function (Admission $row) {
            $std = $row->currentStandard?->name
                ?: ($row->admissionStandard?->name ?: '-');
            $cls = trim((string) ($row->division ?: '-'));

            return $std . '||' . $cls;
        });
    }

    /**
     * @return array{from_ym:string,to_ym:string}
     */
    protected function filtersFromRequest(Request $request): array
    {
        $defaults = $this->defaultRange();

        return [
            'from_ym' => trim((string) $request->input('from_ym', $defaults['from_ym'])),
            'to_ym'   => trim((string) $request->input('to_ym', $defaults['to_ym'])),
        ];
    }

    /**
     * @param  array{from_ym:string,to_ym:string}  $filters
     * @return array{from:?Carbon,to:?Carbon}
     */
    protected function resolveDateRange(array $filters): array
    {
        $fromYm = trim((string) ($filters['from_ym'] ?? ''));
        $toYm = trim((string) ($filters['to_ym'] ?? ''));

        if (!preg_match('/^\d{4}-\d{2}$/', $fromYm) || !preg_match('/^\d{4}-\d{2}$/', $toYm)) {
            return ['from' => null, 'to' => null];
        }

        try {
            $from = Carbon::createFromFormat('Y-m-d', $fromYm . '-01')->startOfDay();
            $to = Carbon::createFromFormat('Y-m-d', $toYm . '-01')->endOfMonth()->startOfDay();
        } catch (\Throwable $e) {
            return ['from' => null, 'to' => null];
        }

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfMonth(), $from->copy()->endOfMonth()->startOfDay()];
        }

        return ['from' => $from, 'to' => $to];
    }

    /**
     * @return array{from_ym:string,to_ym:string}
     */
    protected function defaultRange(): array
    {
        $now = Carbon::now();
        $currentYear = MasterAcademicYear::current();

        $fromYm = $now->copy()->startOfYear()->format('Y-m');
        if ($currentYear?->start_date) {
            $fromYm = $currentYear->start_date->format('Y-m');
        }

        return [
            'from_ym' => $fromYm,
            'to_ym'   => $now->format('Y-m'),
        ];
    }

    /**
     * @param  array{from_ym:string,to_ym:string}  $filters
     */
    protected function formatRangeLabel(array $filters): string
    {
        return $this->formatYmLabel($filters['from_ym'] ?? '') . ' to ' . $this->formatYmLabel($filters['to_ym'] ?? '');
    }

    protected function formatYmLabel(string $ym): string
    {
        if (!preg_match('/^\d{4}-\d{2}$/', $ym)) {
            return '-';
        }
        try {
            return Carbon::createFromFormat('Y-m-d', $ym . '-01')->format('F - Y');
        } catch (\Throwable $e) {
            return $ym;
        }
    }

    protected function authorizeList(): void
    {
        if (!Helper::directCan('new-student-list-list')) {
            abort(403, 'User does not have the right permissions.');
        }
    }
}
