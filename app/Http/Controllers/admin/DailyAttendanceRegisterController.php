<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\Master\MasterAcademicYear;
use App\Models\Master\MasterDivision;
use App\Models\Master\MasterHoliday;
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
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DailyAttendanceRegisterController extends Controller
{
    public $modules = [];

    protected const SESSION_KEY = 'daily_attendance_register_filters';

    public function __construct()
    {
        $this->modules = [
            'title'       => 'Daily Attendance Register',
            'folder_path' => 'software.module.daily-attendance-register',
            'route'       => 'daily-attendance-register',
        ];
    }

    public function index(Request $request)
    {
        $this->authorizeRegister();
        $modules = $this->modules;
        View::share('modules', $modules);

        $filters = $this->filters(new Request(session(self::SESSION_KEY, [])));
        $standards = MasterStandard::where('status', 'active')->orderBy('id')->pluck('name');
        $divisions = MasterDivision::where('status', 'active')->orderBy('name')->pluck('name');
        $years = range((int) date('Y') - 2, (int) date('Y') + 1);
        $months = collect(range(1, 12))->mapWithKeys(fn ($m) => [$m => Carbon::create(null, $m, 1)->format('F')]);

        $report = ($filters['standard'] && $filters['month'] && $filters['year'])
            ? $this->buildReport($filters)
            : null;

        return view($modules['folder_path'] . '.index', compact(
            'modules', 'filters', 'standards', 'divisions', 'years', 'months', 'report'
        ));
    }

    public function filter(Request $request)
    {
        $this->authorizeRegister();

        if ($request->boolean('clear')) {
            session()->forget(self::SESSION_KEY);
        } else {
            session()->put(self::SESSION_KEY, [
                'standard'      => trim((string) $request->input('standard', '')),
                'division'      => trim((string) $request->input('division', '')),
                'month'         => (int) $request->input('month', (int) date('n')),
                'year'          => (int) $request->input('year', (int) date('Y')),
                'class_teacher' => trim((string) $request->input('class_teacher', '')),
            ]);
        }

        return redirect()->route('daily-attendance-register.index');
    }

    public function printList(Request $request)
    {
        $this->authorizeRegister();
        $filters = $this->filters($request);
        abort_unless($filters['standard'] && $filters['month'] && $filters['year'], 422, 'Select Standard, Month and Year.');

        $report = $this->buildReport($filters);
        $school = SchoolDatabaseManager::getActiveSchool();

        return view($this->modules['folder_path'] . '.print', compact('filters', 'report', 'school'));
    }

    public function exportExcel(Request $request)
    {
        $this->authorizeRegister();
        $filters = $this->filters($request);
        abort_unless($filters['standard'] && $filters['month'] && $filters['year'], 422, 'Select Standard, Month and Year.');

        $report = $this->buildReport($filters);
        $school = SchoolDatabaseManager::getActiveSchool();

        // Match print layout: always 31 day columns
        $dayCols = 31;
        $firstDayCol = 6;
        $lastDayCol = $firstDayCol + $dayCols - 1; // 36
        $thisMonthCol = $lastDayCol + 1;           // 37
        $prevMonthCol = $thisMonthCol + 1;         // 38
        $endMonthCol = $prevMonthCol + 1;          // 39
        $lastCol = $endMonthCol;
        $col = fn (int $i) => Coordinate::stringFromColumnIndex($i);
        $last = $col($lastCol);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Attendance Register');
        $sheet->setShowGridlines(false);
        $sheet->getDefaultRowDimension()->setRowHeight(15);
        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Arial')->setSize(9);

        $center = Alignment::HORIZONTAL_CENTER;
        $middle = Alignment::VERTICAL_CENTER;
        $left = Alignment::HORIZONTAL_LEFT;
        $right = Alignment::HORIZONTAL_RIGHT;
        $thinBorder = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];
        $sundayFill = [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F3F3F3'],
            ],
        ];
        $headerFill = [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'EEF0FA'],
            ],
        ];

        $schoolName = strtoupper($school['name'] ?? 'Ultra School');
        if (SchoolDatabaseManager::getActiveSchoolCode() === 'ues') {
            $schoolName .= '-KESHOD';
        }
        $classLabel = $filters['division'] !== '' ? $filters['division'] : '';
        $classTeacher = trim((string) ($filters['class_teacher'] ?? ''));
        $daysByNum = collect($report['days'])->keyBy('day');
        $sundayCols = [];
        for ($day = 1; $day <= $dayCols; $day++) {
            $metaDay = $daysByNum->get($day);
            if ($metaDay && !empty($metaDay['is_sunday'])) {
                $sundayCols[] = $firstDayCol + $day - 1;
            }
        }

        $maxNameLen = 18;
        $maxCasteLen = 10;
        foreach ($report['students'] as $s) {
            $maxNameLen = max($maxNameLen, mb_strlen((string) $s['name']));
            $maxCasteLen = max($maxCasteLen, mb_strlen((string) $s['caste']));
        }
        $nameWidth = min(34, max(22, $maxNameLen * 1.05));
        $casteWidth = min(16, max(11, $maxCasteLen * 1.05));

        // Row 1 — School title (print blue)
        $sheet->mergeCells("A1:{$last}1");
        $sheet->setCellValue('A1', $schoolName);
        $sheet->getRowDimension(1)->setRowHeight(24);
        $sheet->getStyle("A1:{$last}1")->getFont()
            ->setBold(true)->setSize(16)->setColor(new Color('FF1F2580'));
        $sheet->getStyle("A1:{$last}1")->getAlignment()->setHorizontal($center)->setVertical($middle);

        // Row 2 — Title (red) + Date
        $midMeta = 18;
        $sheet->mergeCells('A2:' . $col($midMeta) . '2');
        $sheet->setCellValue('A2', 'Daily Attendance Register');
        $sheet->getStyle('A2')->getFont()
            ->setBold(true)->setSize(12)->setColor(new Color('FFE1141C'));
        $sheet->getStyle('A2')->getAlignment()->setHorizontal($left)->setVertical($middle);

        $sheet->mergeCells($col($midMeta + 1) . '2:' . $last . '2');
        $sheet->setCellValue($col($midMeta + 1) . '2', $report['report_date'] ?? now()->format('d-m-Y'));
        $sheet->getStyle($col($midMeta + 1) . '2:' . $last . '2')->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle($col($midMeta + 1) . '2:' . $last . '2')->getAlignment()->setHorizontal($right)->setVertical($middle);
        $sheet->getRowDimension(2)->setRowHeight(18);

        // Row 3 — Meta (Month / Year / Standard / Class / Class Teacher)
        $sheet->mergeCells("A3:{$last}3");
        $sheet->setCellValue(
            'A3',
            'Month: ' . $report['month_name']
            . '          Year: ' . $filters['year']
            . '          Standard: ' . $report['standard_label']
            . '          Class: ' . $classLabel
            . '          Class Teacher: ' . $classTeacher
        );
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal($left)->setVertical($middle);
        $sheet->getRowDimension(3)->setRowHeight(18);

        // Rows 5-6 — Headers (print style)
        $headerTop = 5;
        $headerBottom = 6;
        foreach (["Roll\nNo.", "GR\nNo.", 'Name of Student', 'Caste', 'Birthdate'] as $i => $label) {
            $c = $col($i + 1);
            $sheet->mergeCells("{$c}{$headerTop}:{$c}{$headerBottom}");
            $sheet->setCellValue("{$c}{$headerTop}", $label);
        }

        for ($day = 1; $day <= $dayCols; $day++) {
            $c = $col($firstDayCol + $day - 1);
            $metaDay = $daysByNum->get($day);
            $sheet->setCellValue($c . $headerTop, $metaDay['dow'] ?? '');
            $sheet->setCellValue($c . $headerBottom, $metaDay['day'] ?? '');
            $sheet->getColumnDimension($c)->setWidth(3.6);
        }

        foreach ([
            $thisMonthCol => "Atten. From\nThis Month",
            $prevMonthCol => "Atten. From\nPre. Month",
            $endMonthCol  => "Atten. From\nEnd of this Month",
        ] as $idx => $label) {
            $c = $col($idx);
            $sheet->mergeCells("{$c}{$headerTop}:{$c}{$headerBottom}");
            $sheet->setCellValue("{$c}{$headerTop}", $label);
            $sheet->getColumnDimension($c)->setWidth(11);
        }

        $sheet->getRowDimension($headerTop)->setRowHeight(28);
        $sheet->getRowDimension($headerBottom)->setRowHeight(16);

        $headerRange = "A{$headerTop}:{$last}{$headerBottom}";
        $sheet->getStyle($headerRange)->applyFromArray($thinBorder);
        $sheet->getStyle($headerRange)->applyFromArray($headerFill);
        $sheet->getStyle("A{$headerTop}:E{$headerBottom}")->getFont()->setBold(true)->setSize(8);
        $sheet->getStyle("A{$headerTop}:E{$headerBottom}")->getAlignment()
            ->setHorizontal($center)->setVertical($middle)->setWrapText(true);

        $dayRange = $col($firstDayCol) . $headerTop . ':' . $col($lastDayCol) . $headerBottom;
        $sheet->getStyle($col($firstDayCol) . $headerTop . ':' . $col($lastDayCol) . $headerTop)
            ->getFont()->setBold(true)->setSize(7);
        $sheet->getStyle($col($firstDayCol) . $headerBottom . ':' . $col($lastDayCol) . $headerBottom)
            ->getFont()->setBold(true)->setSize(8);
        $sheet->getStyle($dayRange)->getAlignment()
            ->setHorizontal($center)->setVertical($middle)->setWrapText(false)->setShrinkToFit(true);

        $attenRange = $col($thisMonthCol) . $headerTop . ':' . $col($endMonthCol) . $headerBottom;
        $sheet->getStyle($attenRange)->getFont()->setBold(true)->setSize(8);
        $sheet->getStyle($attenRange)->getAlignment()
            ->setHorizontal($center)->setVertical($middle)->setWrapText(true);

        foreach ($sundayCols as $sunIdx) {
            $c = $col($sunIdx);
            $sheet->getStyle("{$c}{$headerTop}:{$c}{$headerBottom}")->applyFromArray($sundayFill);
        }

        // Student rows
        $dataStart = 7;
        $row = $dataStart;
        foreach ($report['students'] as $student) {
            $sheet->setCellValue('A' . $row, $student['roll_no']);
            $sheet->setCellValue('B' . $row, $student['gr_no']);
            $sheet->setCellValue('C' . $row, $student['name']);
            $sheet->setCellValue('D' . $row, $student['caste']);
            $sheet->setCellValue('E' . $row, $student['birthdate']);

            for ($day = 1; $day <= $dayCols; $day++) {
                $c = $col($firstDayCol + $day - 1);
                $metaDay = $daysByNum->get($day);
                $mark = ($metaDay && isset($student['marks'][$day])) ? $student['marks'][$day] : '';
                $sheet->setCellValue($c . $row, $mark);
            }

            $sheet->setCellValue($col($thisMonthCol) . $row, $student['this_month'] ?? '');
            $sheet->setCellValue($col($prevMonthCol) . $row, $student['pre_month'] ?? '');
            $sheet->setCellValue($col($endMonthCol) . $row, $student['end_month'] ?? '');
            $sheet->getRowDimension($row)->setRowHeight(16);
            $row++;
        }

        if (!$report['students']) {
            $sheet->mergeCells("A{$row}:{$last}{$row}");
            $sheet->setCellValue("A{$row}", 'No students found for this class.');
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal($center)->setVertical($middle);
            $sheet->getRowDimension($row)->setRowHeight(20);
            $row++;
        }

        $dataEnd = max($dataStart, $row - 1);
        if ($report['students']) {
            $sheet->getStyle("A{$dataStart}:B{$dataEnd}")->getAlignment()->setHorizontal($center)->setVertical($middle);
            $sheet->getStyle("C{$dataStart}:C{$dataEnd}")->getAlignment()
                ->setHorizontal($left)->setVertical($middle)->setWrapText(false)->setShrinkToFit(true);
            $sheet->getStyle("D{$dataStart}:D{$dataEnd}")->getAlignment()
                ->setHorizontal($left)->setVertical($middle)->setWrapText(false)->setShrinkToFit(true);
            $sheet->getStyle("E{$dataStart}:{$last}{$dataEnd}")->getAlignment()->setHorizontal($center)->setVertical($middle);
            $sheet->getStyle("C{$dataStart}:C{$dataEnd}")->getFont()->setBold(true)->setSize(8);
            $sheet->getStyle("D{$dataStart}:D{$dataEnd}")->getFont()->setSize(8);
        }

        // Footer totals (print labels + day cells)
        $footerRows = [
            'Total Present Boys'  => 'present_boys',
            'Total Absent Boys'   => 'absent_boys',
            'Total Present Girls' => 'present_girls',
            'Total Absent Girls'  => 'absent_girls',
            'Total Present'       => 'present_total',
            'Total Absent'        => 'absent_total',
            'Total'               => 'strength',
        ];
        $footerStart = $row;
        foreach ($footerRows as $label => $key) {
            $sheet->mergeCells("A{$row}:E{$row}");
            $sheet->setCellValue("A{$row}", $label);
            for ($day = 1; $day <= $dayCols; $day++) {
                $c = $col($firstDayCol + $day - 1);
                $metaDay = $daysByNum->get($day);
                $val = $metaDay ? ($report['daily_totals'][$day][$key] ?? '') : '';
                if ($val === 0 && $key !== 'strength') {
                    $val = '';
                }
                $sheet->setCellValue($c . $row, $val);
            }
            $sheet->getRowDimension($row)->setRowHeight(15);
            $row++;
        }
        $footerEnd = $row - 1;

        $sheet->getStyle("A{$footerStart}:E{$footerEnd}")->getFont()->setBold(true)->setSize(8);
        $sheet->getStyle("A{$footerStart}:E{$footerEnd}")->getAlignment()->setHorizontal($left)->setVertical($middle);
        $sheet->getStyle($col($firstDayCol) . $footerStart . ':' . $last . $footerEnd)
            ->getAlignment()->setHorizontal($center)->setVertical($middle);
        $sheet->getStyle("A{$headerTop}:{$last}{$footerEnd}")->applyFromArray($thinBorder);

        foreach ($sundayCols as $sunIdx) {
            $c = $col($sunIdx);
            $sheet->getStyle("{$c}{$dataStart}:{$c}{$footerEnd}")->applyFromArray($sundayFill);
        }

        // Avg. Attendance box (print)
        $avgTop = $footerEnd + 2;
        $avgHdr = $avgTop;
        $avgVal = $avgTop + 1;
        $pctHdr = $avgTop + 2;
        $pctVal = $avgTop + 3;

        $sheet->mergeCells("A{$avgHdr}:B{$avgHdr}");
        $sheet->setCellValue("A{$avgHdr}", 'Avg. Attendance');
        $sheet->setCellValue("C{$avgHdr}", 'Boys');
        $sheet->setCellValue("D{$avgHdr}", 'Girls');
        $sheet->setCellValue("E{$avgHdr}", 'Total');
        $sheet->mergeCells("A{$avgVal}:B{$avgVal}");
        $sheet->setCellValue("A{$avgVal}", '1. Boys / 2. Girls / 3. Total');
        $sheet->setCellValue("C{$avgVal}", $report['avg']['boys'] ?? '');
        $sheet->setCellValue("D{$avgVal}", $report['avg']['girls'] ?? '');
        $sheet->setCellValue("E{$avgVal}", $report['avg']['total'] ?? '');

        $sheet->mergeCells("A{$pctHdr}:B{$pctHdr}");
        $sheet->setCellValue("A{$pctHdr}", '% of Avg. Attendance');
        $sheet->setCellValue("C{$pctHdr}", 'Boys');
        $sheet->setCellValue("D{$pctHdr}", 'Girls');
        $sheet->setCellValue("E{$pctHdr}", 'Total');
        $sheet->mergeCells("A{$pctVal}:B{$pctVal}");
        $sheet->setCellValue("A{$pctVal}", '1. Boys / 2. Girls / 3. Total');
        $sheet->setCellValue("C{$pctVal}", $report['avg_pct']['boys'] ?? '');
        $sheet->setCellValue("D{$pctVal}", $report['avg_pct']['girls'] ?? '');
        $sheet->setCellValue("E{$pctVal}", $report['avg_pct']['total'] ?? '');

        $sheet->getStyle("A{$avgHdr}:E{$pctVal}")->applyFromArray($thinBorder);
        $sheet->getStyle("A{$avgHdr}:E{$pctVal}")->getAlignment()->setHorizontal($center)->setVertical($middle);
        $sheet->getStyle("A{$avgHdr}:B{$avgHdr}")->applyFromArray($headerFill);
        $sheet->getStyle("A{$pctHdr}:B{$pctHdr}")->applyFromArray($headerFill);
        $sheet->getStyle("C{$avgHdr}:E{$avgHdr}")->applyFromArray($headerFill);
        $sheet->getStyle("C{$pctHdr}:E{$pctHdr}")->applyFromArray($headerFill);
        $sheet->getStyle("A{$avgHdr}")->getFont()->setBold(true)->setSize(9);
        $sheet->getStyle("A{$pctHdr}")->getFont()->setBold(true)->setSize(9);
        $sheet->getStyle("A{$avgHdr}:B{$avgVal}")->getAlignment()->setHorizontal($left)->setVertical($middle);
        $sheet->getStyle("A{$pctHdr}:B{$pctVal}")->getAlignment()->setHorizontal($left)->setVertical($middle);
        $sheet->getStyle("C{$avgHdr}:E{$avgHdr}")->getFont()->setBold(true);
        $sheet->getStyle("C{$pctHdr}:E{$pctHdr}")->getFont()->setBold(true);

        // Strength / count box (print right side)
        $countLabelCol = $thisMonthCol;
        $countValueCol = $prevMonthCol;
        $sheet->setCellValue($col($countLabelCol) . $avgHdr, 'Boys');
        $sheet->setCellValue($col($countValueCol) . $avgHdr, $report['counts']['boys'] ?? '');
        $sheet->setCellValue($col($countLabelCol) . $avgVal, 'Girls');
        $sheet->setCellValue($col($countValueCol) . $avgVal, $report['counts']['girls'] ?? '');
        $sheet->setCellValue($col($countLabelCol) . $pctHdr, 'Total');
        $sheet->setCellValue($col($countValueCol) . $pctHdr, $report['counts']['total'] ?? '');
        $sheet->getStyle($col($countLabelCol) . $avgHdr . ':' . $col($countValueCol) . $pctHdr)->applyFromArray($thinBorder);
        $sheet->getStyle($col($countLabelCol) . $avgHdr . ':' . $col($countValueCol) . $pctHdr)
            ->getAlignment()->setHorizontal($center)->setVertical($middle);
        $sheet->getStyle($col($countLabelCol) . $avgHdr . ':' . $col($countLabelCol) . $pctHdr)
            ->getFont()->setBold(true);
        $sheet->getStyle($col($countLabelCol) . $avgHdr . ':' . $col($countLabelCol) . $pctHdr)
            ->applyFromArray($headerFill);

        // Signatures
        $signRow = $pctVal + 3;
        $teacherStart = max($firstDayCol, $lastDayCol - 10);
        $teacherEnd = min($lastDayCol, $teacherStart + 5);
        $sheet->setCellValue($col($teacherStart) . $signRow, 'Sign of Teacher');
        $sheet->mergeCells($col($teacherStart) . $signRow . ':' . $col($teacherEnd) . $signRow);
        $sheet->setCellValue($col($thisMonthCol) . $signRow, 'Sign of Principal');
        $sheet->mergeCells($col($thisMonthCol) . $signRow . ':' . $last . $signRow);
        $sheet->getStyle($col($teacherStart) . $signRow . ':' . $last . $signRow)->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle($col($teacherStart) . $signRow . ':' . $last . $signRow)
            ->getAlignment()->setHorizontal($center)->setVertical($middle);
        $sheet->getRowDimension($signRow)->setRowHeight(20);
        $sheet->getStyle($col($teacherStart) . $signRow . ':' . $col($teacherEnd) . $signRow)
            ->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($col($thisMonthCol) . $signRow . ':' . $last . $signRow)
            ->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(7);
        $sheet->getColumnDimension('B')->setWidth(8);
        $sheet->getColumnDimension('C')->setWidth($nameWidth);
        $sheet->getColumnDimension('D')->setWidth($casteWidth);
        $sheet->getColumnDimension('E')->setWidth(11);

        $sheet->freezePane('F7');
        $sheet->getPageSetup()
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
            ->setFitToPage(true)
            ->setFitToWidth(1)
            ->setFitToHeight(0)
            ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4)
            ->setHorizontalCentered(true);
        $sheet->getPageMargins()->setTop(0.25)->setBottom(0.25)->setLeft(0.2)->setRight(0.2);
        $sheet->getPageSetup()->setPrintArea("A1:{$last}{$signRow}");
        $sheet->getHeaderFooter()->setOddFooter('&CDaily Attendance Register - ' . $schoolName);

        $fileName = 'Daily_Attendance_Register_' . $report['standard_label'] . '_' . ($filters['division'] ?: 'All')
            . '_' . $report['month_name'] . '_' . $filters['year'] . '.xlsx';
        $fileName = preg_replace('/[^A-Za-z0-9_\-.]/', '_', $fileName);

        $writer = new Xlsx($spreadsheet);
        $response = new StreamedResponse(fn () => $writer->save('php://output'));
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $fileName
        ));

        return $response;
    }

    protected function filters(Request $request): array
    {
        $session = session(self::SESSION_KEY, []);

        return [
            'standard'      => trim((string) $request->input('standard', $session['standard'] ?? '')),
            'division'      => trim((string) $request->input('division', $session['division'] ?? '')),
            'month'         => (int) $request->input('month', $session['month'] ?? (int) date('n')),
            'year'          => (int) $request->input('year', $session['year'] ?? (int) date('Y')),
            'class_teacher' => trim((string) $request->input('class_teacher', $session['class_teacher'] ?? '')),
        ];
    }

    /**
     * Blank register: student master data filled, attendance cells left empty for manual marking.
     *
     * @return array<string, mixed>
     */
    public function buildReport(array $filters): array
    {
        $month = max(1, min(12, (int) $filters['month']));
        $year = (int) $filters['year'];
        $monthStart = Carbon::create($year, $month, 1)->startOfDay();
        $monthEnd = $monthStart->copy()->endOfMonth();
        $daysInMonth = $monthStart->daysInMonth;

        $students = $this->students($filters);

        $academicYear = MasterAcademicYear::forDate($monthStart) ?? MasterAcademicYear::current();
        $preStart = $academicYear?->start_date
            ? Carbon::parse($academicYear->start_date)->startOfDay()
            : Carbon::create($year, 6, 1)->startOfDay();
        if ($preStart->gt($monthStart)) {
            $preStart = $monthStart->copy()->startOfYear();
        }
        $preEnd = $monthStart->copy()->subDay()->endOfDay();

        $holidayDates = $this->holidayDates($preStart->copy(), $monthEnd->copy());
        $days = [];
        $workingThisMonth = 0;
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($year, $month, $day);
            $dateStr = $date->format('Y-m-d');
            $isSunday = $date->dayOfWeek === Carbon::SUNDAY;
            $isHoliday = isset($holidayDates[$dateStr]);
            $isWorking = !$isSunday && !$isHoliday;
            if ($isWorking) {
                $workingThisMonth++;
            }
            $days[] = [
                'day'        => $day,
                'date'       => $dateStr,
                'dow'        => strtoupper(substr($date->format('D'), 0, 1)), // T/F/S/S/M/T/W
                'dow_full'   => strtoupper($date->format('D')),
                'is_sunday'  => $isSunday,
                'is_holiday' => $isHoliday,
                'is_working' => $isWorking,
            ];
        }

        $workingPreMonth = 0;
        if ($preEnd->gte($preStart)) {
            for ($d = $preStart->copy(); $d->lte($preEnd); $d->addDay()) {
                $ds = $d->format('Y-m-d');
                if ($d->dayOfWeek !== Carbon::SUNDAY && !isset($holidayDates[$ds])) {
                    $workingPreMonth++;
                }
            }
        }
        $workingEndMonth = $workingPreMonth + $workingThisMonth;

        $emptyMarks = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $emptyMarks[$day] = '';
        }

        $dailyTotals = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dailyTotals[$day] = [
                'present_boys'  => '',
                'absent_boys'   => '',
                'present_girls' => '',
                'absent_girls'  => '',
                'present_total' => '',
                'absent_total'  => '',
                'strength'      => '',
            ];
        }

        $boysCount = 0;
        $girlsCount = 0;
        $studentRows = [];

        foreach ($students as $student) {
            $gender = $this->genderBucket($student->gender);
            if ($gender === 'boy') {
                $boysCount++;
            } elseif ($gender === 'girl') {
                $girlsCount++;
            }

            $studentRows[] = [
                'roll_no'    => $student->roll_no ?? '',
                'gr_no'      => $student->gr_no ?? '',
                'name'       => $this->fullName($student),
                'caste'      => $student->cast ?? '',
                'birthdate'  => $this->formatDate($student->date_of_birth),
                'gender'     => $gender,
                'marks'      => $emptyMarks,
                'this_month' => '',
                'pre_month'  => '',
                'end_month'  => '',
            ];
        }

        return [
            'month_name'         => $monthStart->format('F'),
            'month'              => $month,
            'year'               => $year,
            'days_in_month'      => $daysInMonth,
            'days'               => $days,
            'students'           => $studentRows,
            'daily_totals'       => $dailyTotals,
            'working_this_month' => $workingThisMonth,
            'working_pre_month'  => $workingPreMonth,
            'working_end_month'  => $workingEndMonth,
            'standard_label'     => $this->standardLabel($filters['standard']),
            'counts'             => [
                'boys'  => $boysCount,
                'girls' => $girlsCount,
                'total' => $students->count(),
            ],
            'avg' => [
                'boys'  => '',
                'girls' => '',
                'total' => '',
            ],
            'avg_pct' => [
                'boys'  => '',
                'girls' => '',
                'total' => '',
            ],
            'report_date' => now()->format('d-m-Y'),
        ];
    }

    protected function students(array $filters): Collection
    {
        $query = Admission::query()
            ->whereHas('currentStandard', fn ($q) => $q->where('name', $filters['standard']));

        if ($filters['division'] !== '') {
            $query->where('division', $filters['division']);
        }

        return $query
            ->orderByRaw('roll_no IS NULL')
            ->orderByRaw('CAST(roll_no AS UNSIGNED)')
            ->orderBy('roll_no')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    /**
     * @return array<string, string>
     */
    protected function holidayDates(Carbon $from, Carbon $to): array
    {
        $holidays = MasterHoliday::where('status', 'active')
            ->where(function ($q) use ($from, $to) {
                $q->where('from_date', '<=', $to->format('Y-m-d'))
                    ->where('to_date', '>=', $from->format('Y-m-d'));
            })
            ->get();

        $dates = [];
        foreach ($holidays as $h) {
            $curr = Carbon::parse($h->from_date)->startOfDay();
            $end = Carbon::parse($h->to_date)->startOfDay();
            while ($curr->lte($end)) {
                $dates[$curr->format('Y-m-d')] = $h->name ?? 'Holiday';
                $curr->addDay();
            }
        }

        return $dates;
    }

    protected function genderBucket(?string $gender): string
    {
        $g = strtolower(trim((string) $gender));
        if (in_array($g, ['male', 'boy', 'm'], true)) {
            return 'boy';
        }
        if (in_array($g, ['female', 'girl', 'f'], true)) {
            return 'girl';
        }

        return 'other';
    }

    public function fullName($student): string
    {
        return strtoupper(trim(preg_replace(
            '/\s+/',
            ' ',
            ($student->first_name ?? '') . ' ' . ($student->last_name ?? '') . ' ' . ($student->father_name ?? '')
        )));
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

    public function standardLabel(string $standard): string
    {
        if (preg_match('/^(\d+)/', $standard, $m)) {
            return $m[1];
        }

        return $standard;
    }

    protected function authorizeRegister(): void
    {
        if (!Helper::directCan('daily-attendance-register-list')) {
            abort(403, 'User does not have the right permissions.');
        }
    }
}
