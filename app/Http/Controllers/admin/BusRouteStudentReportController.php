<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\Master\MasterBusRouteVillage;
use App\Services\SchoolDatabaseManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BusRouteStudentReportController extends Controller
{
    public const MAX_EXTRA_COLUMNS = 20;

    /** @var array<string, string> */
    public const AVAILABLE_COLUMNS = [
        'gr_no'             => 'GR No',
        'roll_no'           => 'Roll No',
        'student_name'      => 'Student Name',
        'standard'          => 'Std',
        'division'          => 'Division',
        'bus_route_village' => 'Bus Route',
        'mobile_no'         => 'Mobile No',
        'address'           => 'Address',
    ];

    /** @var string[] */
    public const DEFAULT_COLUMNS = [
        'gr_no',
        'roll_no',
        'student_name',
        'standard',
        'division',
    ];

    public function index(Request $request)
    {
        $this->authorizeReport();

        if ($request->isMethod('post') && !$request->has('columns') && !$request->has('extra_columns')) {
            session([$this->sessionKey() => trim((string) $request->input('route', ''))]);

            return redirect()->route('bus-route-report.index');
        }

        $modules = [
            'title' => 'Bus Route Wise Students',
            'route' => 'bus-route-report',
        ];
        View::share('modules', $modules);

        $routes = $this->routeOptions();
        $selected = (string) session($this->sessionKey(), '');
        $groups = $this->groups($selected);
        $school = SchoolDatabaseManager::getActiveSchool();

        $filters = $this->filters($request);
        $availableColumns = self::AVAILABLE_COLUMNS;
        $selectedColumns = $this->selectedColumns($filters);
        $blankHeaders = $this->blankColumnHeaders($filters);

        return view('software.module.bus-route-report.index', compact(
            'modules', 'routes', 'selected', 'groups', 'school',
            'filters', 'availableColumns', 'selectedColumns', 'blankHeaders'
        ));
    }

    public function saveSettings(Request $request)
    {
        $this->authorizeReport();

        $columns = $this->normalizeColumns($request->input('columns', self::DEFAULT_COLUMNS));

        session()->put($this->settingsSessionKey(), [
            'columns'       => $columns,
            'extra_columns' => max(0, min(self::MAX_EXTRA_COLUMNS, (int) $request->input('extra_columns', 0))),
            'column_names'  => trim((string) $request->input('column_names', '')),
        ]);

        return redirect()->route('bus-route-report.index')->withSuccess('Print settings saved.');
    }

    public function printList(Request $request)
    {
        $this->authorizeReport();

        $selected = (string) $request->input('route', session($this->sessionKey(), ''));
        $groups = $this->groups($selected);
        $school = SchoolDatabaseManager::getActiveSchool();
        $routeLabel = $selected !== '' ? $selected : 'All Routes';

        $filters = $this->filters($request);
        $dataColumns = $this->selectedColumns($filters);
        $blankHeaders = $this->blankColumnHeaders($filters);

        return view('software.module.bus-route-report.print', compact(
            'groups', 'school', 'routeLabel', 'filters', 'dataColumns', 'blankHeaders'
        ));
    }

    public function exportExcel(Request $request)
    {
        $this->authorizeReport();

        $selected = (string) $request->input('route', session($this->sessionKey(), ''));
        $groups = $this->groups($selected);
        $school = SchoolDatabaseManager::getActiveSchool();
        $routeLabel = $selected !== '' ? $selected : 'All Routes';

        $filters = $this->filters($request);
        $dataColumns = $this->selectedColumns($filters);
        $blankHeaders = $this->blankColumnHeaders($filters);
        $blankColumns = count($blankHeaders);
        $headers = array_merge(array_values($dataColumns), $blankHeaders);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Bus Routes');

        $title = 'Bus Route Wise Students - ' . $routeLabel;
        $lastCol = Coordinate::stringFromColumnIndex(max(1, count($headers)));

        // Title rows
        $sheet->setCellValue('A1', $school['name'] ?? config('app.name'));
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A2', $title);
        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->getStyle("A1:{$lastCol}2")->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(16);
        $sheet->getStyle('A2')->getFont()->setSize(12);
        $sheet->getStyle("A1:{$lastCol}2")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Header row
        $sheet->fromArray($headers, null, 'A4');
        $sheet->getStyle("A4:{$lastCol}4")->getFont()->setBold(true);
        $sheet->getStyle("A4:{$lastCol}4")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E7E7FF');

        $currentRow = 5;
        $totalStudents = 0;

        foreach ($groups as $routeName => $students) {
            $count = $students->count();
            $totalStudents += $count;

            // Route section header
            $sheet->setCellValue("A{$currentRow}", "Route: {$routeName} ({$count} students)");
            $sheet->mergeCells("A{$currentRow}:{$lastCol}{$currentRow}");
            $sheet->getStyle("A{$currentRow}:{$lastCol}{$currentRow}")->getFont()->setBold(true);
            $sheet->getStyle("A{$currentRow}:{$lastCol}{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D7E8FF');
            $currentRow++;

            // Student rows
            foreach ($students as $index => $student) {
                $row = [];
                foreach (array_keys($dataColumns) as $key) {
                    $row[] = $this->columnValue($student, $key, $index + 1);
                }
                $row = array_merge($row, array_fill(0, $blankColumns, ''));
                $sheet->fromArray($row, null, "A{$currentRow}", true);
                $currentRow++;
            }
        }

        // Total row
        $sheet->setCellValue("A{$currentRow}", "Total Students: {$totalStudents}");
        $sheet->mergeCells("A{$currentRow}:{$lastCol}{$currentRow}");
        $sheet->getStyle("A{$currentRow}:{$lastCol}{$currentRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$currentRow}:{$lastCol}{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F0F4FF');

        // Borders
        $sheet->getStyle("A4:{$lastCol}{$currentRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Auto widths
        $firstBlankIndex = count($headers) - $blankColumns + 1;
        for ($i = 1; $i <= count($headers); $i++) {
            $colLetter = Coordinate::stringFromColumnIndex($i);
            $dimension = $sheet->getColumnDimension($colLetter);
            if ($blankColumns > 0 && $i >= $firstBlankIndex) {
                $dimension->setWidth(14);
            } else {
                $dimension->setAutoSize(true);
            }
        }

        $fileName = str_replace([' ', '/', '\\'], '_', $title) . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $response = new StreamedResponse(fn () => $writer->save('php://output'));
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $fileName));

        return $response;
    }

    public function filters(Request $request): array
    {
        $session = session($this->settingsSessionKey(), []);
        $columnsInput = $request->input('columns', $session['columns'] ?? self::DEFAULT_COLUMNS);
        if (is_string($columnsInput)) {
            $columnsInput = array_filter(array_map('trim', explode(',', $columnsInput)));
        }

        return [
            'columns'       => $this->normalizeColumns($columnsInput),
            'extra_columns' => max(0, min(self::MAX_EXTRA_COLUMNS, (int) $request->input('extra_columns', $session['extra_columns'] ?? 0))),
            'column_names'  => trim((string) $request->input('column_names', $session['column_names'] ?? '')),
        ];
    }

    public function normalizeColumns($columns): array
    {
        if (!is_array($columns)) {
            $columns = [];
        }

        $valid = array_values(array_filter(
            $columns,
            fn ($key) => is_string($key) && array_key_exists($key, self::AVAILABLE_COLUMNS)
        ));

        $ordered = array_values(array_intersect(array_keys(self::AVAILABLE_COLUMNS), $valid));

        return $ordered ?: self::DEFAULT_COLUMNS;
    }

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

    public function blankColumnHeaders(array $filters): array
    {
        if (($filters['extra_columns'] ?? 0) < 1) {
            return [];
        }
        $names = array_map('trim', explode(',', (string) ($filters['column_names'] ?? '')));

        return array_map(fn ($i) => $names[$i] ?? '', range(0, $filters['extra_columns'] - 1));
    }

    public function columnValue($student, string $key, int $index = 1): string
    {
        return match ($key) {
            'sr_no'             => (string) $index,
            'gr_no'             => (string) ($student->gr_no ?? '—'),
            'roll_no'           => (string) ($student->roll_no ?? '—'),
            'student_name'      => (string) ($student->full_name ?: trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''))),
            'standard'          => (string) ($student->currentStandard->name ?? '—'),
            'division'          => (string) ($student->division ?? '—'),
            'bus_route_village' => (string) ($student->bus_route_village ?? '—'),
            'mobile_no'         => (string) ($student->mobile_no ?? $student->parent_mobile_no ?? '—'),
            'address'           => (string) ($student->permanent_address ?? $student->temporary_address ?? '—'),
            default             => '',
        };
    }

    protected function authorizeReport(): void
    {
        if (!Helper::directCanAny(['bus-route-village-list', 'class-report-list', 'fees-collection-list'])) {
            abort(403, 'User does not have the right permissions.');
        }
    }

    protected function sessionKey(): string
    {
        return 'bus_route_student_report.' . SchoolDatabaseManager::getActiveSchoolCode();
    }

    protected function settingsSessionKey(): string
    {
        return 'bus_route_student_report_settings.' . SchoolDatabaseManager::getActiveSchoolCode();
    }

    protected function routeOptions()
    {
        $master = MasterBusRouteVillage::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->pluck('name');

        $used = Admission::query()
            ->whereNotNull('bus_route_village')
            ->where('bus_route_village', '!=', '')
            ->distinct()
            ->orderBy('bus_route_village')
            ->pluck('bus_route_village');

        return $master->merge($used)->unique(fn ($name) => mb_strtolower($name))->values();
    }

    protected function groups(string $route)
    {
        $query = Admission::with('currentStandard')
            ->whereNotNull('bus_route_village')
            ->where('bus_route_village', '!=', '');

        if ($route !== '') {
            $query->whereRaw('LOWER(bus_route_village) = ?', [mb_strtolower($route)]);
        }

        return $query
            ->orderBy('bus_route_village')
            ->orderBy('current_std_id')
            ->orderBy('division')
            ->orderByRaw('roll_no IS NULL')
            ->orderBy('roll_no')
            ->orderBy('first_name')
            ->get()
            ->groupBy(fn ($student) => $student->bus_route_village);
    }
}
