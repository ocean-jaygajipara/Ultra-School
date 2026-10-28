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
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClassReportController extends Controller
{
    public $modules = [];

    public const MAX_EXTRA_COLUMNS = 20;

    /** @var array<string, string> */
    public const AVAILABLE_COLUMNS = [
        'roll_no'      => 'Roll No',
        'student_name' => 'Student Name',
        'gr_no'        => 'GR No',
        'phone'        => 'Phone',
        'birth_date'   => 'Birth Date',
    ];

    /** @var string[] */
    public const DEFAULT_COLUMNS = ['roll_no', 'student_name', 'gr_no'];

    protected const SESSION_KEY = 'class_report_filters';

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
        $availableColumns = self::AVAILABLE_COLUMNS;

        return view($modules['folder_path'] . '.index', compact(
            'modules', 'filters', 'standards', 'divisions', 'students', 'availableColumns'
        ));
    }

    public function filter(Request $request)
    {
        $this->authorizeView();

        $current = session(self::SESSION_KEY, []);

        if ($request->boolean('clear')) {
            session()->put(self::SESSION_KEY, array_merge($current, [
                'standard' => '',
                'division' => '',
            ]));
        } else {
            session()->put(self::SESSION_KEY, array_merge($current, $request->only(['standard', 'division'])));
        }

        return redirect()->route('class-report.index');
    }

    public function saveSettings(Request $request)
    {
        $this->authorizeView();

        $current = session(self::SESSION_KEY, []);
        $columns = $this->normalizeColumns($request->input('columns', self::DEFAULT_COLUMNS));

        session()->put(self::SESSION_KEY, array_merge($current, [
            'columns'       => $columns,
            'extra_columns' => max(0, min(self::MAX_EXTRA_COLUMNS, (int) $request->input('extra_columns', 0))),
            'column_names'  => trim((string) $request->input('column_names', '')),
        ]));

        return redirect()->route('class-report.index')->withSuccess('Report settings saved.');
    }

    public function printList(Request $request)
    {
        $this->authorizeView();
        $filters = $this->filters($request);
        $summary = $this->summary($filters);
        $students = $filters['standard'] ? $this->students($filters) : collect();
        $school = SchoolDatabaseManager::getActiveSchool();
        $dataColumns = $this->selectedColumns($filters);

        return view($this->modules['folder_path'] . '.print', compact(
            'filters', 'summary', 'students', 'school', 'dataColumns'
        ));
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
            $dataColumns = $this->selectedColumns($filters);
            $blankHeaders = $this->blankColumnHeaders($filters);
            $blankColumns = count($blankHeaders);
            $headers = array_merge(array_values($dataColumns), $blankHeaders);
            $rows = $this->students($filters)->map(function ($s) use ($dataColumns, $blankColumns) {
                $row = [];
                foreach (array_keys($dataColumns) as $key) {
                    $row[] = $this->columnValue($s, $key);
                }

                return array_merge($row, array_fill(0, $blankColumns, ''));
            })->all();
            $title = 'Class Report - ' . $this->classLabel($filters);
        } else {
            $sheet->setTitle('Summary');
            $headers = ['Sr No.', 'Standard', 'Division', 'Boys', 'Girls', 'Other', 'Total'];
            $rows = $this->summary($filters)->values()->map(fn ($r, $i) => [
                $i + 1, $r['standard'], $r['division'], $r['boys'], $r['girls'], $r['other'], $r['total'],
            ])->all();
            $title = 'Class Wise Summary';
        }

        $schoolName = strtoupper($school['name'] ?? config('app.name', 'Ultra School')) . ' - KESHOD';
        $lastCol = Coordinate::stringFromColumnIndex(max(1, count($headers)));

        // Locate school logo
        $logoPath = public_path('admin/assets/images/logo-light.png');
        if (!is_file($logoPath)) {
            $logoPath = public_path('uploads/logo/report_logo.png');
        }
        if (!is_file($logoPath)) {
            $logoPath = public_path('uploads/logo/logo.png');
        }

        $hasLogo = is_file($logoPath) && count($headers) >= 2;

        if ($hasLogo) {
            $sheet->getRowDimension(1)->setRowHeight(30);
            $sheet->getRowDimension(2)->setRowHeight(22);
            $sheet->getRowDimension(3)->setRowHeight(20);

            // Merge Column A rows 1-3 for logo
            $sheet->mergeCells('A1:A3');
            $sheet->getStyle('A1:A3')->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);

            // Background fill for header block
            $sheet->getStyle("A1:{$lastCol}3")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('FFFFFF');

            // Bottom border under Row 3 to separate header from table cleanly
            $sheet->getStyle("A3:{$lastCol}3")->getBorders()->getBottom()
                ->setBorderStyle(Border::BORDER_MEDIUM)
                ->getColor()->setRGB('1F2580');

            $drawing = new Drawing();
            $drawing->setName('School Logo');
            $drawing->setDescription('School Logo');
            $drawing->setPath($logoPath);
            $drawing->setHeight(60);
            $drawing->setCoordinates('A1');
            $drawing->setOffsetX(8);
            $drawing->setOffsetY(6);
            $drawing->setWorksheet($sheet);

            // Row 1: School Name
            $sheet->setCellValue('B1', $schoolName);
            $sheet->mergeCells("B1:{$lastCol}1");
            $sheet->getStyle("B1:{$lastCol}1")->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('1F2580');
            $sheet->getStyle("B1:{$lastCol}1")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setShrinkToFit(true);

            // Row 2: Report Title & Class Name
            $reportTitleText = $filters['standard']
                ? 'Class Wise Student List - ' . $this->classLabel($filters)
                : 'Class Wise Strength Summary';
            $sheet->setCellValue('B2', $reportTitleText);
            $sheet->mergeCells("B2:{$lastCol}2");
            $sheet->getStyle("B2:{$lastCol}2")->getFont()->setBold(true)->setSize(11)->getColor()->setRGB('222222');
            $sheet->getStyle("B2:{$lastCol}2")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setShrinkToFit(true);

            // Row 3: Meta line (concise to prevent any overflow or clipping)
            $metaText = $filters['standard']
                ? 'Total Students: ' . count($rows) . '   |   Date: ' . now()->format('d-m-Y h:i A')
                : 'Total Classes: ' . count($rows) . '   |   Date: ' . now()->format('d-m-Y h:i A');
            $sheet->setCellValue('B3', $metaText);
            $sheet->mergeCells("B3:{$lastCol}3");
            $sheet->getStyle("B3:{$lastCol}3")->getFont()->setSize(9.5)->getColor()->setRGB('555555');
            $sheet->getStyle("B3:{$lastCol}3")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setShrinkToFit(true);

            $headerRowIndex = 4;
        } else {
            $sheet->setCellValue('A1', $schoolName);
            $sheet->mergeCells("A1:{$lastCol}1");
            $sheet->setCellValue('A2', $title);
            $sheet->mergeCells("A2:{$lastCol}2");
            $sheet->getStyle("A1:{$lastCol}2")->getFont()->setBold(true);
            $sheet->getStyle('A1')->getFont()->setSize(15)->getColor()->setRGB('1F2580');
            $sheet->getStyle('A2')->getFont()->setSize(11);
            $sheet->getStyle("A1:{$lastCol}2")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $headerRowIndex = 3;
        }

        $sheet->fromArray($headers, null, "A{$headerRowIndex}");
        $sheet->getRowDimension($headerRowIndex)->setRowHeight(26);
        $sheet->getStyle("A{$headerRowIndex}:{$lastCol}{$headerRowIndex}")->getFont()->setBold(true)->setSize(10.5)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("A{$headerRowIndex}:{$lastCol}{$headerRowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1F2580');
        $sheet->getStyle("A{$headerRowIndex}:{$lastCol}{$headerRowIndex}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $dataStartRow = $headerRowIndex + 1;
        if ($rows) {
            $sheet->fromArray($rows, null, "A{$dataStartRow}", true);
        }

        $lastRow = $headerRowIndex + count($rows);
        if (!$filters['standard'] && $rows) {
            $lastRow++;
            $sheet->setCellValue("A{$lastRow}", 'Total');
            $sheet->mergeCells("A{$lastRow}:C{$lastRow}");
            foreach (['D', 'E', 'F', 'G'] as $col) {
                $sheet->setCellValue("{$col}{$lastRow}", "=SUM({$col}{$dataStartRow}:{$col}" . ($lastRow - 1) . ')');
            }
            $sheet->getStyle("A{$lastRow}:{$lastCol}{$lastRow}")->getFont()->setBold(true);
            $sheet->getStyle("A{$lastRow}:{$lastCol}{$lastRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E7E7FF');
        }

        $sheet->getStyle("A{$headerRowIndex}:{$lastCol}{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D0D7DE');

        for ($r = $dataStartRow; $r <= $lastRow; $r++) {
            $sheet->getRowDimension($r)->setRowHeight(20);
            if ($r % 2 === 0) {
                $sheet->getStyle("A{$r}:{$lastCol}{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFD');
            }
        }

        $firstBlankIndex = count($headers) - $blankColumns + 1;
        for ($i = 1; $i <= count($headers); $i++) {
            $colLetter = Coordinate::stringFromColumnIndex($i);
            $dimension = $sheet->getColumnDimension($colLetter);

            if ($colLetter === 'A') {
                $dimension->setAutoSize(false)->setWidth(14);
            } elseif ($colLetter === 'B') {
                $dimension->setAutoSize(false)->setWidth(34);
            } elseif ($blankColumns > 0 && $i >= $firstBlankIndex) {
                $dimension->setAutoSize(false)->setWidth(16);
            } else {
                $dimension->setAutoSize(true);
            }
        }

        if ($filters['standard']) {
            $sheet->getStyle("A{$dataStartRow}:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            if ($blankColumns > 0) {
                $blankStart = Coordinate::stringFromColumnIndex($firstBlankIndex);
                $sheet->getStyle("{$blankStart}{$headerRowIndex}:{$lastCol}{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
        }

        $fileName = str_replace([' ', '/'], '_', $title) . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $response = new StreamedResponse(fn () => $writer->save('php://output'));
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $fileName));

        return $response;
    }

    protected function filters(Request $request): array
    {
        $session = session(self::SESSION_KEY, []);

        $columnsInput = $request->input('columns', $session['columns'] ?? self::DEFAULT_COLUMNS);
        if (is_string($columnsInput)) {
            $columnsInput = array_filter(array_map('trim', explode(',', $columnsInput)));
        }

        return [
            'standard'      => trim((string) $request->input('standard', $session['standard'] ?? '')),
            'division'      => trim((string) $request->input('division', $session['division'] ?? '')),
            'columns'       => $this->normalizeColumns($columnsInput),
            'extra_columns' => max(0, min(
                self::MAX_EXTRA_COLUMNS,
                (int) $request->input('extra_columns', $session['extra_columns'] ?? 0)
            )),
            'column_names'  => trim((string) $request->input('column_names', $session['column_names'] ?? '')),
        ];
    }

    /**
     * @param  mixed  $columns
     * @return string[]
     */
    protected function normalizeColumns($columns): array
    {
        if (!is_array($columns)) {
            $columns = [];
        }

        $valid = array_values(array_filter(
            $columns,
            fn ($key) => is_string($key) && array_key_exists($key, self::AVAILABLE_COLUMNS)
        ));

        // Keep master order; always keep Student Name if nothing selected
        $ordered = array_values(array_intersect(array_keys(self::AVAILABLE_COLUMNS), $valid));

        return $ordered ?: self::DEFAULT_COLUMNS;
    }

    /**
     * Selected data columns as key => label (master order).
     *
     * @return array<string, string>
     */
    public function selectedColumns(array $filters): array
    {
        $selected = [];
        foreach ($filters['columns'] as $key) {
            if (isset(self::AVAILABLE_COLUMNS[$key])) {
                $selected[$key] = self::AVAILABLE_COLUMNS[$key];
            }
        }

        return $selected ?: array_intersect_key(self::AVAILABLE_COLUMNS, array_flip(self::DEFAULT_COLUMNS));
    }

    /**
     * Headings for empty columns added after data columns.
     */
    public function blankColumnHeaders(array $filters): array
    {
        if ($filters['extra_columns'] < 1) {
            return [];
        }
        $names = array_map('trim', explode(',', $filters['column_names']));

        return array_map(fn ($i) => $names[$i] ?? '', range(0, $filters['extra_columns'] - 1));
    }

    public function columnValue($student, string $key): string
    {
        return match ($key) {
            'roll_no'      => (string) ($student->roll_no ?? ''),
            'student_name' => $this->fullName($student),
            'gr_no'        => (string) ($student->gr_no ?? ''),
            'phone'        => (string) ($student->mobile_no ?: $student->parent_mobile_no ?: ''),
            'birth_date'   => $this->formatDate($student->date_of_birth),
            default        => '',
        };
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
            return Carbon::parse($date)->format('d/m/y');
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
