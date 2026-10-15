<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\Master\MasterDivision;
use App\Models\Master\MasterStandard;
use App\Services\SchoolDatabaseManager;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClassReportController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title'       => 'Class Wise Report',
            'folder_path' => 'software.module.class-report',
            'route'       => 'class-report',
        ];
    }

    public function index(Request $request)
    {
        $this->authorizeView();
        $modules = $this->modules;
        View::share('modules', $modules);

        $filters = $this->filters(new Request(session(self::SESSION_KEY, [])));
        $standards = $this->standardNames();
        $divisions = MasterDivision::where('status', 'active')->orderBy('name')->pluck('name');
        $students = $filters['standard'] ? $this->students($filters) : collect();

        return view($modules['folder_path'] . '.index', compact('modules', 'filters', 'standards', 'divisions', 'students'));
    }

    public function filter(Request $request)
    {
        $this->authorizeView();

        if ($request->boolean('clear')) {
            session()->forget(self::SESSION_KEY);
        } else {
            session()->put(self::SESSION_KEY, $request->only(['standard', 'division']));
        }

        return redirect()->route('class-report.index');
    }

    public function printList(Request $request)
    {
        $this->authorizeView();
        $filters = $this->filters($request);
        $summary = $this->summary($filters);
        $students = $filters['standard'] ? $this->students($filters) : collect();
        $school = SchoolDatabaseManager::getActiveSchool();

        return view($this->modules['folder_path'] . '.print', compact('filters', 'summary', 'students', 'school'));
    }

    public function exportExcel(Request $request)
    {
        $this->authorizeView();
        $filters = $this->filters($request);
        $school = SchoolDatabaseManager::getActiveSchool();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $blankColumns = 0;
        if ($filters['standard']) {
            $sheet->setTitle('Students');
            $blankHeaders = $this->blankColumnHeaders($filters);
            $blankColumns = count($blankHeaders);
            $headers = array_merge(['Roll No', 'Student Name', 'GR No'], $blankHeaders);
            $rows = $this->students($filters)->map(fn ($s) => array_merge(
                [$s->roll_no, $this->fullName($s), $s->gr_no],
                array_fill(0, $blankColumns, '')
            ))->all();
            $title = 'Class Report - ' . $this->classLabel($filters);
        } else {
            $sheet->setTitle('Summary');
            $headers = ['Sr No.', 'Standard', 'Division', 'Boys', 'Girls', 'Other', 'Total'];
            $rows = $this->summary($filters)->values()->map(fn ($r, $i) => [
                $i + 1, $r['standard'], $r['division'], $r['boys'], $r['girls'], $r['other'], $r['total'],
            ])->all();
            $title = 'Class Wise Summary';
        }

        $lastCol = Coordinate::stringFromColumnIndex(count($headers));

        $sheet->setCellValue('A1', $school['name'] ?? config('app.name'));
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A2', $title);
        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->getStyle("A1:{$lastCol}2")->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(16);
        $sheet->getStyle('A2')->getFont()->setSize(12);
        $sheet->getStyle("A1:{$lastCol}2")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->fromArray($headers, null, 'A4');
        $sheet->getStyle("A4:{$lastCol}4")->getFont()->setBold(true);
        $sheet->getStyle("A4:{$lastCol}4")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E7E7FF');

        if ($rows) {
            $sheet->fromArray($rows, null, 'A5', true);
        }

        $lastRow = 4 + count($rows);
        if (!$filters['standard'] && $rows) {
            $lastRow++;
            $sheet->setCellValue("A{$lastRow}", 'Total');
            $sheet->mergeCells("A{$lastRow}:C{$lastRow}");
            foreach (['D', 'E', 'F', 'G'] as $col) {
                $sheet->setCellValue("{$col}{$lastRow}", "=SUM({$col}5:{$col}" . ($lastRow - 1) . ')');
            }
            $sheet->getStyle("A{$lastRow}:{$lastCol}{$lastRow}")->getFont()->setBold(true);
        }

        $sheet->getStyle("A4:{$lastCol}{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $firstBlankIndex = count($headers) - $blankColumns + 1;
        for ($i = 1; $i <= count($headers); $i++) {
            $dimension = $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i));
            if ($i >= $firstBlankIndex) {
                $dimension->setWidth(14);
            } else {
                $dimension->setAutoSize(true);
            }
        }
        if ($filters['standard']) {
            $sheet->getStyle("A4:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D4:{$lastCol}4")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $fileName = str_replace([' ', '/'], '_', $title) . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $response = new StreamedResponse(fn () => $writer->save('php://output'));
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $fileName));

        return $response;
    }

    public const MAX_EXTRA_COLUMNS = 20;

    protected const SESSION_KEY = 'class_report_filters';

    protected function filters(Request $request): array
    {
        return [
            'standard'      => trim((string) $request->input('standard', '')),
            'division'      => trim((string) $request->input('division', '')),
            'extra_columns' => max(0, min(self::MAX_EXTRA_COLUMNS, (int) $request->input('extra_columns', 0))),
            'column_names'  => trim((string) $request->input('column_names', '')),
        ];
    }

    /**
     * Headings for the empty columns added after GR No. Names typed by the user
     * (comma separated) are used in order; the rest stay blank.
     */
    public function blankColumnHeaders(array $filters): array
    {
        if ($filters['extra_columns'] < 1) {
            return [];
        }
        $names = array_map('trim', explode(',', $filters['column_names']));

        return array_map(fn ($i) => $names[$i] ?? '', range(0, $filters['extra_columns'] - 1));
    }

    protected function standardNames(): Collection
    {
        return MasterStandard::where('status', 'active')->orderBy('id')->pluck('name');
    }

    /**
     * One row per Standard + Division with Boys / Girls / Other / Total counts,
     * ordered by the Standard Master order.
     */
    protected function summary(array $filters): Collection
    {
        $query = Admission::query()
            ->leftJoin('master_standards as ms', 'ms.id', '=', 'admission.current_std_id')
            ->selectRaw("ms.name AS std_name, admission.division,
                SUM(CASE WHEN LOWER(admission.gender) IN ('male','boy','m') THEN 1 ELSE 0 END) AS boys,
                SUM(CASE WHEN LOWER(admission.gender) IN ('female','girl','f') THEN 1 ELSE 0 END) AS girls,
                COUNT(*) AS total")
            ->groupBy('ms.name', 'admission.division');

        if ($filters['standard']) {
            $query->where('ms.name', $filters['standard']);
        }
        if ($filters['division']) {
            $query->where('admission.division', $filters['division']);
        }

        $order = $this->standardNames()->flip();

        return $query->get()
            ->map(fn ($r) => [
                'standard' => $r->std_name ?: '-',
                'division' => $r->division ?: '-',
                'boys'     => (int) $r->boys,
                'girls'    => (int) $r->girls,
                'other'    => (int) $r->total - (int) $r->boys - (int) $r->girls,
                'total'    => (int) $r->total,
            ])
            ->sortBy([
                fn ($a, $b) => ($order[$a['standard']] ?? PHP_INT_MAX) <=> ($order[$b['standard']] ?? PHP_INT_MAX),
                fn ($a, $b) => strcmp($a['division'], $b['division']),
            ])
            ->values();
    }

    protected function students(array $filters): Collection
    {
        $query = Admission::with('currentStandard')
            ->whereHas('currentStandard', fn ($q) => $q->where('name', $filters['standard']));
        if ($filters['division']) {
            $query->where('division', $filters['division']);
        }

        return $query
            ->orderBy('division')
            ->orderByRaw('roll_no IS NULL')
            ->orderBy('roll_no')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    public function fullName($student): string
    {
        return trim(preg_replace('/\s+/', ' ', $student->first_name . ' ' . $student->last_name . ' ' . $student->father_name));
    }

    public function formatDate($date): string
    {
        if (empty($date)) {
            return '';
        }
        try {
            return Carbon::parse($date)->format('d-m-Y');
        } catch (\Throwable $e) {
            return (string) $date;
        }
    }

    public function classLabel(array $filters): string
    {
        return $filters['standard'] . ($filters['division'] ? ' - ' . $filters['division'] : ' (All Divisions)');
    }

    protected function authorizeView(): void
    {
        if (!Helper::directCan('class-report-list')) {
            abort(403, 'User does not have the right permissions.');
        }
    }
}
