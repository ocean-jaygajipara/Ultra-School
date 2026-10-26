<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\AdmissionClassHistory;
use App\Models\FeeReceiptEntry;
use App\Models\Master\FeeMaster;
use App\Models\Master\MasterAcademicYear;
use App\Models\Master\MasterBusRouteVillage;
use App\Models\Master\MasterDivision;
use App\Models\Master\MasterStandard;
use App\Services\SchoolDatabaseManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yajra\DataTables\Facades\DataTables;

class FeeReceiptEntryController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title'             => 'Fee Receipt Entry',
            'folder_path'       => 'software.module.fee-receipt-entry',
            'route'             => 'fee-receipt-entry',
            'table_name'        => (new FeeReceiptEntry())->getTable(),
            'permission_prefix' => 'fee-receipt-entry',
        ];
    }

    public function index(Request $request)
    {
        $modules = $this->modules;
        $modules['permission_list'] = Helper::directCan($modules['permission_prefix'] . '-list');
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');
        $modules['permission_edit'] = Helper::directCan($modules['permission_prefix'] . '-edit');
        $modules['permission_delete'] = Helper::directCan($modules['permission_prefix'] . '-delete');

        if (!$modules['permission_list']) {
            if ($request->ajax()) {
                return response()->json(['error' => 'User does not have permission.'], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }

        View::share('modules', $modules);

        $columns = [
            (object) ['data' => 'roll_no', 'name' => 'roll_no', 'td_label' => 'Roll No'],
            (object) ['data' => 'date', 'name' => 'date', 'td_label' => 'Date'],
            (object) ['data' => 'gr_no', 'name' => 'gr_no', 'td_label' => 'GR No'],
            (object) ['data' => 'student_name', 'name' => 'student_name', 'td_label' => 'Student Name'],
            (object) ['data' => 'standard_name', 'name' => 'standard_name', 'td_label' => 'Std'],
            (object) ['data' => 'term', 'name' => 'term', 'td_label' => 'Term'],
            (object) ['data' => 'net_amount', 'name' => 'net_amount', 'td_label' => 'Net Amt', 'className' => 'text-end'],
            (object) ['data' => 'action', 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' => 'text-center'],
        ];
        View::share('columns', $columns);

        if ($request->ajax()) {
            $query = FeeReceiptEntry::query()
                ->leftJoin('master_standards as ms', 'ms.id', '=', 'fee_receipt_entries.standard_id')
                ->select('fee_receipt_entries.*', 'ms.name as standard_name')
                ->orderByDesc('fee_receipt_entries.id');

            return DataTables::of($query)
                ->filter(function ($q) use ($request) {
                    $search = $request->input('search.value');
                    if ($search) {
                        $q->where(function ($w) use ($search) {
                            $w->where('fee_receipt_entries.receipt_no', 'like', "%{$search}%")
                                ->orWhere('fee_receipt_entries.roll_no', 'like', "%{$search}%")
                                ->orWhere('fee_receipt_entries.gr_no', 'like', "%{$search}%")
                                ->orWhere('fee_receipt_entries.student_name', 'like', "%{$search}%");
                        });
                    }
                })
                ->editColumn('date', fn ($row) => $row->date ? $row->date->format('d-m-Y') : '—')
                ->editColumn('net_amount', fn ($row) => '₹ ' . number_format((float) $row->net_amount, 2))
                ->addColumn('action', function ($row) use ($modules) {
                    $btn = '<div class="d-flex gap-1 justify-content-center">';
                    $btn .= '<a href="' . route($modules['route'] . '.print', $row->id) . '" target="_blank" class="btn btn-sm btn-primary" title="Print"><i class="ti ti-printer"></i></a>';
                    if ((float) $row->vehicle_fee > 0) {
                        $btn .= '<a href="' . route($modules['route'] . '.bus-print', $row->id) . '" target="_blank" class="btn btn-sm btn-warning" title="Bus Receipt"><i class="ti ti-bus"></i></a>';
                    }
                    if (!empty($modules['permission_edit'])) {
                        $btn .= '<a href="' . route($modules['route'] . '.edit', $row->id) . '" class="btn btn-sm btn-info" title="Edit"><i class="ti ti-edit"></i></a>';
                    }
                    if (!empty($modules['permission_delete'])) {
                        $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules['route'] . '.destroy', $row->id) . '" class="btn btn-sm btn-danger deletebutton" title="Delete"><i class="ti ti-trash"></i></a>';
                    }
                    $btn .= '</div>';

                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view($modules['folder_path'] . '.index', compact('modules'));
    }

    public function exportExcel()
    {
        $this->authorizeFeeList();

        $rows = FeeReceiptEntry::with('standard')->orderByDesc('date')->orderByDesc('id')->get();
        $data = $rows->map(fn ($row) => [
            $row->roll_no,
            $row->date?->format('d-m-Y'),
            $row->gr_no,
            $row->student_name,
            trim(($row->standard->name ?? '') . ($row->division ? ' - ' . $row->division : '')),
            $row->term,
            (float) $row->net_amount,
        ])->all();

        return $this->downloadExcel('fee-receipt-list.xlsx', ['Roll No', 'Date', 'GR No', 'Student Name', 'Std', 'Term', 'Net Amt'], $data);
    }

    public function printList()
    {
        $this->authorizeFeeList();

        $rows = FeeReceiptEntry::with('standard')->orderByDesc('date')->orderByDesc('id')->get();
        $school = SchoolDatabaseManager::getActiveSchool();

        return view($this->modules['folder_path'] . '.list-print', compact('rows', 'school'));
    }

    public function exportDayReport()
    {
        $this->authorizeDayReport();

        $rows = FeeReceiptEntry::with('standard')->whereNotNull('date')->orderByDesc('date')->orderByDesc('id')->get();
        $data = $rows->map(fn ($row) => [
            $row->date?->format('d-m-Y'),
            $row->receipt_no,
            $row->gr_no,
            $row->student_name,
            trim(($row->standard->name ?? '') . ($row->division ? ' - ' . $row->division : '')),
            $row->term,
            (float) $row->total,
            (float) $row->less_amount,
            (float) $row->net_amount,
        ])->all();
        $data[] = ['', '', '', '', '', 'Grand total', (float) $rows->sum('total'), (float) $rows->sum('less_amount'), (float) $rows->sum('net_amount')];

        return $this->downloadExcel('day-to-day-fees.xlsx', ['Date', 'Receipt No', 'GR No', 'Student', 'Std', 'Term', 'Total', 'Less', 'Net Amt'], $data);
    }

    public function exportLessReport()
    {
        $this->authorizeLessReport();

        $rows = $this->lessReportQuery($this->currentLessReportFilters())->get();
        $data = $rows->values()->map(fn ($row, $index) => [
            $index + 1,
            $row->receipt_no,
            $row->date?->format('d-m-Y'),
            $row->gr_no,
            $row->student_name,
            trim(($row->standard->name ?? '') . ($row->division ? ' - ' . $row->division : '')),
            $row->term,
            (float) $row->total,
            (float) $row->less_amount,
            (float) $row->net_amount,
        ])->all();

        return $this->downloadExcel('less-report.xlsx', ['Sr', 'Receipt No', 'Date', 'GR No', 'Student Name', 'Std', 'Term', 'Total', 'Less', 'Net Amt'], $data);
    }

    protected function authorizeFeeList(): void
    {
        if (!Helper::directCan($this->modules['permission_prefix'] . '-list')) {
            abort(403, 'User does not have the right permissions.');
        }
    }

    protected function authorizeDayReport(): void
    {
        if (!Helper::directCan('fee-receipt-entry-day-report')) {
            abort(403, 'User does not have the right permissions.');
        }
    }

    protected function authorizeLessReport(): void
    {
        if (!Helper::directCan('fee-receipt-entry-less-report')) {
            abort(403, 'User does not have the right permissions.');
        }
    }

    protected function authorizeFeeStatusReport(string $kind): void
    {
        $perm = $kind === 'paid' ? 'fee-receipt-entry-paid-report' : 'fee-receipt-entry-unpaid-report';
        if (!Helper::directCan($perm)) {
            abort(403, 'User does not have the right permissions.');
        }
    }

    protected function downloadExcel(string $fileName, array $headers, array $rows)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($headers as $index => $label) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $label);
            $sheet->getStyleByColumnAndRow($index + 1, 1)->getFont()->setBold(true);
        }

        foreach ($rows as $rowIndex => $row) {
            foreach (array_values($row) as $columnIndex => $value) {
                $sheet->setCellValueByColumnAndRow($columnIndex + 1, $rowIndex + 2, $value);
            }
        }

        for ($i = 1; $i <= count($headers); $i++) {
            $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function create()
    {
        $modules = $this->modules;
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');
        if (!$modules['permission_add']) {
            abort(403, 'User does not have the right permissions.');
        }
        View::share('modules', $modules);

        $standards = MasterStandard::where('status', 'active')->orderBy('id')->get(['id', 'name']);
        $divisions = MasterDivision::where('status', 'active')->orderBy('name')->pluck('name');
        $academicYears = MasterAcademicYear::where('status', 'active')->orderByDesc('start_date')->get();
        $currentAcademicYear = MasterAcademicYear::current();
        $terms = FeeMaster::TERMS;

        return view($modules['folder_path'] . '.form', compact(
            'modules',
            'standards',
            'divisions',
            'academicYears',
            'currentAcademicYear',
            'terms'
        ));
    }

    public function store(Request $request)
    {
        if (!Helper::directCan($this->modules['permission_prefix'] . '-create')) {
            abort(403);
        }

        $data = $request->validate([
            'receipt_no'       => 'nullable|string|max:50',
            'date'             => 'required|date',
            'academic_year_id' => 'required|exists:master_academic_years,id',
            'admission_id'     => 'nullable|exists:admission,id',
            'standard_id'      => 'required|exists:master_standards,id',
            'division'         => 'nullable|string|max:50',
            'term'             => ['required', Rule::in(array_keys(FeeMaster::TERMS))],
            'gr_no'            => 'nullable|string|max:50',
            'roll_no'          => 'nullable|string|max:50',
            'student_name'     => 'required|string|max:255',
            'last_yr_pending'  => 'nullable|numeric|min:0',
            'admission_fee'    => 'nullable|numeric|min:0',
            'term_fee'         => 'nullable|numeric|min:0',
            'school_fee'       => 'nullable|numeric|min:0',
            'vehicle_fee'      => 'nullable|numeric|min:0',
            'vehicle_fee_part' => 'nullable|in:fee_i,fee_ii,both',
            'less_amount'      => 'nullable|numeric|min:0',
            'mode'             => 'nullable|string|max:50',
            'remark'           => 'nullable|string|max:500',
        ]);

        if ($existing = $this->termAlreadyPaid($data)) {
            return redirect()->back()->withInput()->withErrors([
                'term' => $existing->term . ' term is already paid. Receipt No ' . ($existing->receipt_no ?: '—') . '.',
            ]);
        }

        $data = $this->clearVehicleFeeWithoutRoute($data);

        if ($request->boolean('print_bus_after_save') && (float) ($data['vehicle_fee'] ?? 0) <= 0) {
            return redirect()->back()->withInput()->withErrors([
                'vehicle_fee' => 'Bus fee receipt needs a vehicle fee.',
            ]);
        }

        [$fees, $total, $less, $net] = $this->feeTotals($data);

        $receiptNo = $this->nextReceiptNo();

        $row = FeeReceiptEntry::create([
            'receipt_no'       => $receiptNo,
            'roll_no'          => $data['roll_no'] ?? null,
            'date'             => $data['date'],
            'academic_year_id' => $data['academic_year_id'],
            'admission_id'     => $data['admission_id'] ?? null,
            'standard_id'      => $data['standard_id'],
            'division'         => $data['division'] ?? null,
            'term'             => $data['term'],
            'gr_no'            => $data['gr_no'] ?? null,
            'student_name'     => $data['student_name'],
            'last_yr_pending'  => $fees['last_yr_pending'],
            'admission_fee'    => $fees['admission_fee'],
            'term_fee'         => $fees['term_fee'],
            'school_fee'       => $fees['school_fee'],
            'vehicle_fee'      => $fees['vehicle_fee'],
            'vehicle_fee_part' => $data['vehicle_fee_part'] ?? null,
            'total'            => $total,
            'less_amount'      => $less,
            'net_amount'       => $net,
            'mode'             => $data['mode'] ?? 'Cash',
            'remark'           => $data['remark'] ?? null,
            'fee_snapshot'     => $fees,
            'status'           => 'active',
            'created_by'       => Auth::id(),
        ]);

        if ($request->boolean('print_bus_after_save')) {
            return redirect()->route('fee-receipt-entry.bus-print', $row->id)
                ->withSuccess('Fee receipt saved successfully.');
        }

        if ($request->boolean('print_after_save')) {
            return redirect()->route('fee-receipt-entry.print', $row->id)
                ->withSuccess('Fee receipt saved successfully.');
        }

        return redirect()->route('fee-receipt-entry.index')
            ->withSuccess('Fee receipt saved successfully.');
    }

    public function edit(string $id)
    {
        $modules = $this->modules;
        $modules['permission_edit'] = Helper::directCan($modules['permission_prefix'] . '-edit');
        if (!$modules['permission_edit']) {
            abort(403, 'User does not have the right permissions.');
        }
        View::share('modules', $modules);

        $edit = FeeReceiptEntry::findOrFail($id);
        $standards = MasterStandard::where('status', 'active')->orderBy('id')->get(['id', 'name']);
        $divisions = MasterDivision::where('status', 'active')->orderBy('name')->pluck('name');
        $academicYears = MasterAcademicYear::where('status', 'active')->orderByDesc('start_date')->get();
        $currentAcademicYear = MasterAcademicYear::current();
        $terms = FeeMaster::TERMS;

        return view($modules['folder_path'] . '.form', compact(
            'modules',
            'edit',
            'standards',
            'divisions',
            'academicYears',
            'currentAcademicYear',
            'terms'
        ));
    }

    public function update(Request $request, string $id)
    {
        if (!Helper::directCan($this->modules['permission_prefix'] . '-edit')) {
            abort(403);
        }

        $row = FeeReceiptEntry::findOrFail($id);
        $data = $request->validate([
            'date'             => 'required|date',
            'academic_year_id' => 'required|exists:master_academic_years,id',
            'admission_id'     => 'nullable|exists:admission,id',
            'standard_id'      => 'required|exists:master_standards,id',
            'division'         => 'nullable|string|max:50',
            'term'             => ['required', Rule::in(array_keys(FeeMaster::TERMS))],
            'gr_no'            => 'nullable|string|max:50',
            'roll_no'          => 'nullable|string|max:50',
            'student_name'     => 'required|string|max:255',
            'last_yr_pending'  => 'nullable|numeric|min:0',
            'admission_fee'    => 'nullable|numeric|min:0',
            'term_fee'         => 'nullable|numeric|min:0',
            'school_fee'      => 'nullable|numeric|min:0',
            'vehicle_fee'      => 'nullable|numeric|min:0',
            'vehicle_fee_part' => 'nullable|in:fee_i,fee_ii,both',
            'less_amount'      => 'nullable|numeric|min:0',
            'mode'             => 'nullable|string|max:50',
            'remark'           => 'nullable|string|max:500',
        ]);

        if ($existing = $this->termAlreadyPaid($data, (int) $row->id)) {
            return redirect()->back()->withInput()->withErrors([
                'term' => $existing->term . ' term is already paid. Receipt No ' . ($existing->receipt_no ?: '—') . '.',
            ]);
        }

        $data = $this->clearVehicleFeeWithoutRoute($data);

        if ($request->boolean('print_bus_after_save') && (float) ($data['vehicle_fee'] ?? 0) <= 0) {
            return redirect()->back()->withInput()->withErrors([
                'vehicle_fee' => 'Bus fee receipt needs a vehicle fee.',
            ]);
        }

        [$fees, $total, $less, $net] = $this->feeTotals($data);

        $row->update([
            'roll_no'          => $data['roll_no'] ?? null,
            'date'             => $data['date'],
            'academic_year_id' => $data['academic_year_id'],
            'admission_id'     => $data['admission_id'] ?? null,
            'standard_id'      => $data['standard_id'],
            'division'         => $data['division'] ?? null,
            'term'             => $data['term'],
            'gr_no'            => $data['gr_no'] ?? null,
            'student_name'     => $data['student_name'],
            'last_yr_pending'  => $fees['last_yr_pending'],
            'admission_fee'    => $fees['admission_fee'],
            'term_fee'         => $fees['term_fee'],
            'school_fee'       => $fees['school_fee'],
            'vehicle_fee'      => $fees['vehicle_fee'],
            'vehicle_fee_part' => $data['vehicle_fee_part'] ?? null,
            'total'            => $total,
            'less_amount'      => $less,
            'net_amount'       => $net,
            'mode'             => $data['mode'] ?? 'Cash',
            'remark'           => $data['remark'] ?? null,
            'fee_snapshot'     => $fees,
            'updated_by'       => Auth::id(),
        ]);

        if ($request->boolean('print_bus_after_save')) {
            return redirect()->route('fee-receipt-entry.bus-print', $row->id)
                ->withSuccess('Fee receipt updated successfully.');
        }

        if ($request->boolean('print_after_save')) {
            return redirect()->route('fee-receipt-entry.print', $row->id)
                ->withSuccess('Fee receipt updated successfully.');
        }

        return redirect()->route('fee-receipt-entry.index')
            ->withSuccess('Fee receipt updated successfully.');
    }

    public function destroy(Request $request, string $id)
    {
        if (!Helper::directCan($this->modules['permission_prefix'] . '-delete')) {
            return response()->json([
                'success' => false,
                'message' => 'User does not have the right permissions.',
            ], 403);
        }

        $row = FeeReceiptEntry::findOrFail($id);
        $row->deleted_by = Auth::id();
        $row->save();
        $row->delete();

        return response()->json([
            'success' => true,
            'message' => 'Fee receipt deleted successfully.',
        ]);
    }

    public function dayReport(Request $request)
    {
        $this->authorizeDayReport();

        $modules = $this->modules;
        $modules['title'] = 'Day to Day Fees';
        View::share('modules', $modules);

        $datePage = FeeReceiptEntry::with('standard')
            ->whereNotNull('date')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(100);

        $days = collect($datePage->items())->groupBy(fn ($row) => $row->date->format('Y-m-d'));
        $totals = FeeReceiptEntry::query()
            ->whereNotNull('date')
            ->selectRaw('COUNT(*) as receipts, COALESCE(SUM(total),0) as total, COALESCE(SUM(less_amount),0) as less_amount, COALESCE(SUM(net_amount),0) as net_amount')
            ->first();

        return view($modules['folder_path'] . '.day-report', compact('modules', 'days', 'datePage', 'totals'));
    }

    public function dayReportPrint(Request $request)
    {
        $this->authorizeDayReport();

        $rows = FeeReceiptEntry::with('standard')->whereNotNull('date')->orderBy('date')->orderBy('id')->get();
        $days = $rows->groupBy(fn ($row) => $row->date->format('Y-m-d'));
        $school = SchoolDatabaseManager::getActiveSchool();

        return view($this->modules['folder_path'] . '.day-report-print', compact('rows', 'days', 'school'));
    }

    public function lessReport(Request $request)
    {
        $this->authorizeLessReport();

        if ($request->isMethod('post') || $request->query->count() > 0) {
            if ($request->boolean('reset_filter')) {
                session()->forget($this->lessReportSessionKey());
            } else {
                $this->rememberLessReportFilters($request);
            }

            return redirect()->route('fee-receipt-entry.less-report');
        }

        $modules = $this->modules;
        $modules['title'] = 'Less Report';
        View::share('modules', $modules);

        $academicYears = MasterAcademicYear::where('status', 'active')->orderByDesc('start_date')->get();
        $standards = MasterStandard::where('status', 'active')->orderBy('id')->get(['id', 'name']);
        $filters = $this->currentLessReportFilters();

        $rows = $this->lessReportQuery($filters)->get();

        return view($modules['folder_path'] . '.less-report', compact(
            'modules',
            'rows',
            'filters',
            'academicYears',
            'standards'
        ));
    }

    public function lessReportPrint(Request $request)
    {
        $this->authorizeLessReport();

        $filters = $this->currentLessReportFilters();
        $rows = $this->lessReportQuery($filters)->get();
        $school = SchoolDatabaseManager::getActiveSchool();
        $yearLabel = !empty($filters['academic_year_id'])
            ? (MasterAcademicYear::whereKey($filters['academic_year_id'])->value('name') ?: 'All Years')
            : 'All Years';
        $stdLabel = !empty($filters['standard_id'])
            ? (MasterStandard::whereKey($filters['standard_id'])->value('name') ?: 'All Standards')
            : 'All Standards';

        return view($this->modules['folder_path'] . '.less-report-print', compact(
            'rows',
            'filters',
            'school',
            'yearLabel',
            'stdLabel'
        ));
    }

    protected function rememberLessReportFilters(Request $request): void
    {
        $filters = [];
        foreach (['academic_year_id', 'standard_id', 'from_date', 'to_date'] as $key) {
            $filters[$key] = trim((string) $request->input($key, ''));
        }
        session([$this->lessReportSessionKey() => $filters]);
    }

    protected function lessReportSessionKey(): string
    {
        return 'fee_receipt_less_report.month.' . SchoolDatabaseManager::getActiveSchoolCode();
    }

    protected function currentLessReportFilters(): array
    {
        $saved = session($this->lessReportSessionKey());
        $from = is_array($saved) ? trim((string) ($saved['from_date'] ?? '')) : '';
        $to = is_array($saved) ? trim((string) ($saved['to_date'] ?? '')) : '';
        if ($from === '' && $to === '') {
            $from = now()->startOfMonth()->toDateString();
            $to = now()->endOfMonth()->toDateString();
        }

        return [
            'academic_year_id' => is_array($saved)
                ? (string) ($saved['academic_year_id'] ?? '')
                : (string) (MasterAcademicYear::current()?->id ?? ''),
            'standard_id' => is_array($saved) ? (string) ($saved['standard_id'] ?? '') : '',
            'from_date' => $from,
            'to_date' => $to,
        ];
    }

    protected function lessReportQuery(array $filters)
    {
        $query = FeeReceiptEntry::query()
            ->with(['standard', 'academicYear'])
            ->where('less_amount', '>', 0);

        if (!empty($filters['academic_year_id'])) {
            $query->where('academic_year_id', $filters['academic_year_id']);
        }
        if (!empty($filters['standard_id'])) {
            $query->where('standard_id', $filters['standard_id']);
        }
        if (!empty($filters['from_date'])) {
            $query->whereDate('date', '>=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $query->whereDate('date', '<=', $filters['to_date']);
        }

        return $query->orderByDesc('date')->orderByDesc('id');
    }

    public function paidReport(Request $request)
    {
        return $this->renderFeeStatusReport($request, 'paid');
    }

    public function unpaidReport(Request $request)
    {
        return $this->renderFeeStatusReport($request, 'unpaid');
    }

    public function paidReportPrint()
    {
        return $this->printFeeStatusReport('paid');
    }

    public function unpaidReportPrint()
    {
        return $this->printFeeStatusReport('unpaid');
    }

    public function exportPaidReport()
    {
        return $this->exportFeeStatusReport('paid');
    }

    public function exportUnpaidReport()
    {
        return $this->exportFeeStatusReport('unpaid');
    }

    protected function renderFeeStatusReport(Request $request, string $kind)
    {
        $this->authorizeFeeStatusReport($kind);

        if ($request->ajax()) {
            return $this->feeStatusDataTable($kind);
        }

        if ($request->isMethod('post') || $request->query->count() > 0) {
            if ($request->boolean('reset_filter')) {
                session()->forget($this->feeStatusSessionKey($kind));
            } else {
                $this->rememberFeeStatusFilters($request, $kind);
            }

            return redirect()->route('fee-receipt-entry.' . $kind . '-report');
        }

        $meta = $this->feeStatusMeta($kind);
        $modules = $this->modules;
        $modules['title'] = $meta['title'];
        View::share('modules', $modules);

        $academicYears = MasterAcademicYear::where('status', 'active')->orderByDesc('start_date')->get();
        $standards = MasterStandard::where('status', 'active')->orderBy('id')->get(['id', 'name']);
        $filters = $this->currentFeeStatusFilters($kind);
        $columns = $meta['dt_columns'];

        return view($modules['folder_path'] . '.status-report', compact(
            'modules',
            'meta',
            'filters',
            'academicYears',
            'standards',
            'columns'
        ));
    }

    protected function feeStatusDataTable(string $kind)
    {
        $meta = $this->feeStatusMeta($kind);
        $rows = $this->feeStatusRows($kind, $this->currentFeeStatusFilters($kind));
        $total = (float) $rows->sum('amount');
        $flat = $rows->map(function ($row) use ($meta) {
            $item = [];
            foreach ($meta['keys'] as $index => $key) {
                $item[$key] = $row['cells'][$index]['text'] ?? '';
            }

            return $item;
        })->values();

        return DataTables::of($flat)
            ->editColumn('gr_no', fn ($row) => '<span class="crx-gr">' . e(data_get($row, 'gr_no')) . '</span>')
            ->editColumn('student', fn ($row) => '<span class="crx-name">' . e(data_get($row, 'student')) . '</span>')
            ->rawColumns(['gr_no', 'student'])
            ->with('total_amount_text', number_format($total, 2))
            ->make(true);
    }

    protected function printFeeStatusReport(string $kind)
    {
        $this->authorizeFeeStatusReport($kind);

        $meta = $this->feeStatusMeta($kind);
        $filters = $this->currentFeeStatusFilters($kind);
        $rows = $this->feeStatusRows($kind, $filters);
        $school = SchoolDatabaseManager::getActiveSchool();
        $yearLabel = MasterAcademicYear::whereKey($filters['academic_year_id'])->value('name') ?: '—';
        $stdLabel = $filters['standard_id'] !== ''
            ? (MasterStandard::whereKey($filters['standard_id'])->value('name') ?: 'All Standards')
            : 'All Standards';
        $termLabel = $this->feeStatusTermLabel($filters['term']);

        return view($this->modules['folder_path'] . '.status-report-print', compact(
            'meta',
            'rows',
            'filters',
            'school',
            'yearLabel',
            'stdLabel',
            'termLabel'
        ));
    }

    protected function exportFeeStatusReport(string $kind)
    {
        $this->authorizeFeeStatusReport($kind);

        $meta = $this->feeStatusMeta($kind);
        $rows = $this->feeStatusRows($kind, $this->currentFeeStatusFilters($kind));
        $data = $rows->map(function ($row) {
            return array_map(fn ($cell) => $cell['text'], $row['cells']);
        })->all();
        $data[] = array_merge(
            array_fill(0, max(0, count($meta['columns']) - 2), ''),
            ['Total', round((float) $rows->sum('amount'), 2)]
        );

        return $this->downloadExcel($meta['file'], $meta['columns'], $data);
    }

    protected function feeStatusMeta(string $kind): array
    {
        if ($kind === 'unpaid') {
            return [
                'kind' => 'unpaid',
                'title' => 'Unpaid Report',
                'subtitle' => 'Students who have not paid the selected term.',
                'icon' => 'ti-alert-circle',
                'empty' => 'Every student in this filter has paid.',
                'amount_label' => 'Due',
                'file' => 'unpaid-report.xlsx',
                'columns' => ['Sr', 'GR No', 'Roll', 'Student', 'Std', 'Division', 'Term', 'Due'],
                'keys' => ['sr', 'gr_no', 'roll', 'student', 'std', 'division', 'term', 'amount'],
                'dt_columns' => [
                    (object) ['data' => 'sr', 'name' => 'sr', 'td_label' => 'Sr'],
                    (object) ['data' => 'gr_no', 'name' => 'gr_no', 'td_label' => 'GR No'],
                    (object) ['data' => 'roll', 'name' => 'roll', 'td_label' => 'Roll'],
                    (object) ['data' => 'student', 'name' => 'student', 'td_label' => 'Student'],
                    (object) ['data' => 'std', 'name' => 'std', 'td_label' => 'Std'],
                    (object) ['data' => 'division', 'name' => 'division', 'td_label' => 'Division'],
                    (object) ['data' => 'term', 'name' => 'term', 'td_label' => 'Term'],
                    (object) ['data' => 'amount', 'name' => 'amount', 'td_label' => 'Due', 'className' => 'text-end'],
                ],
            ];
        }

        return [
            'kind' => 'paid',
            'title' => 'Paid Fee Report',
            'subtitle' => 'Fee receipts already saved for the selected year and term.',
            'icon' => 'ti-circle-check',
            'empty' => 'No paid fee in this filter.',
            'amount_label' => 'Net Amt',
            'file' => 'paid-fee-report.xlsx',
            'columns' => ['Sr', 'Date', 'Receipt No', 'GR No', 'Roll', 'Student', 'Std', 'Term', 'Net Amt'],
            'keys' => ['sr', 'date', 'receipt_no', 'gr_no', 'roll', 'student', 'std', 'term', 'amount'],
            'dt_columns' => [
                (object) ['data' => 'sr', 'name' => 'sr', 'td_label' => 'Sr'],
                (object) ['data' => 'date', 'name' => 'date', 'td_label' => 'Date'],
                (object) ['data' => 'receipt_no', 'name' => 'receipt_no', 'td_label' => 'Receipt No'],
                (object) ['data' => 'gr_no', 'name' => 'gr_no', 'td_label' => 'GR No'],
                (object) ['data' => 'roll', 'name' => 'roll', 'td_label' => 'Roll'],
                (object) ['data' => 'student', 'name' => 'student', 'td_label' => 'Student'],
                (object) ['data' => 'std', 'name' => 'std', 'td_label' => 'Std'],
                (object) ['data' => 'term', 'name' => 'term', 'td_label' => 'Term'],
                (object) ['data' => 'amount', 'name' => 'amount', 'td_label' => 'Net Amt', 'className' => 'text-end'],
            ],
        ];
    }

    protected function feeStatusSessionKey(string $kind): string
    {
        return 'fee_receipt_' . $kind . '_report.' . SchoolDatabaseManager::getActiveSchoolCode();
    }

    protected function rememberFeeStatusFilters(Request $request, string $kind): void
    {
        $filters = [];
        foreach (['academic_year_id', 'standard_id', 'term'] as $key) {
            $filters[$key] = trim((string) $request->input($key, ''));
        }
        session([$this->feeStatusSessionKey($kind) => $filters]);
    }

    protected function currentFeeStatusFilters(string $kind): array
    {
        $saved = session($this->feeStatusSessionKey($kind));
        $year = is_array($saved) ? trim((string) ($saved['academic_year_id'] ?? '')) : '';
        if ($year === '') {
            $year = (string) (MasterAcademicYear::current()?->id ?? '');
        }

        return [
            'academic_year_id' => $year,
            'standard_id' => is_array($saved) ? trim((string) ($saved['standard_id'] ?? '')) : '',
            'term' => is_array($saved) ? trim((string) ($saved['term'] ?? '')) : '',
        ];
    }

    protected function feeStatusTermLabel(string $term): string
    {
        return match ($term) {
            'FIRST' => 'First Term',
            'SECOND' => 'Second Term',
            default => 'Both Terms',
        };
    }

    protected function feeStatusRows(string $kind, array $filters)
    {
        return $kind === 'unpaid'
            ? $this->unpaidReportRows($filters)
            : $this->paidReportRows($filters);
    }

    protected function paidReportRows(array $filters)
    {
        $query = FeeReceiptEntry::query()->with('standard');
        if ($filters['academic_year_id'] !== '') {
            $query->where('academic_year_id', $filters['academic_year_id']);
        }
        if ($filters['standard_id'] !== '') {
            $query->where('standard_id', $filters['standard_id']);
        }
        if ($filters['term'] !== '') {
            $query->where('term', $filters['term']);
        }

        return $query
            ->orderBy('standard_id')
            ->orderBy('division')
            ->orderByRaw('roll_no IS NULL')
            ->orderBy('roll_no')
            ->orderBy('date')
            ->orderBy('id')
            ->get()
            ->values()
            ->map(function ($row, $index) {
                $std = trim(($row->standard->name ?? '') . ($row->division ? ' - ' . $row->division : ''));

                return [
                    'amount' => (float) $row->net_amount,
                    'cells' => [
                        ['text' => (string) ($index + 1)],
                        ['text' => $row->date ? $row->date->format('d-m-Y') : '—'],
                        ['text' => $row->receipt_no ?: '—'],
                        ['text' => $row->gr_no ?: '—', 'gr' => true],
                        ['text' => $row->roll_no ?: '—'],
                        ['text' => $row->student_name ?: '—', 'name' => true],
                        ['text' => $std !== '' ? $std : '—'],
                        ['text' => $this->feeStatusTermLabel((string) $row->term)],
                        ['text' => number_format((float) $row->net_amount, 2), 'end' => true],
                    ],
                ];
            });
    }

    protected function unpaidReportRows(array $filters)
    {
        $terms = $filters['term'] !== '' ? [$filters['term']] : ['FIRST', 'SECOND'];
        $yearId = (int) ($filters['academic_year_id'] ?? 0);
        $stdId = (int) ($filters['standard_id'] ?? 0);
        $reportYear = $yearId ? MasterAcademicYear::find($yearId) : null;

        $histories = AdmissionClassHistory::query()
            ->with('standard')
            ->when($yearId, fn ($query) => $query->where('academic_year_id', $yearId))
            ->when($stdId, fn ($query) => $query->where('standard_id', $stdId))
            ->get()
            ->keyBy('admission_id');

        $students = Admission::query()
            ->with('currentStandard')
            ->whereNotNull('current_std_id')
            ->whereNotIn('status', ['rejected', 'cancelled', 'Cancel', 'cancel'])
            ->where(function ($query) use ($histories, $yearId, $stdId) {
                $query->whereIn('id', $histories->keys()->filter()->all() ?: [0]);
                $query->orWhere(function ($current) use ($histories, $yearId, $stdId) {
                    $current->whereNotIn('id', $histories->keys()->filter()->all() ?: [0]);
                    if ($yearId) {
                        $current->where('academic_year_id', $yearId);
                    }
                    if ($stdId) {
                        $current->where('current_std_id', $stdId);
                    }
                });
            })
            ->orderBy('current_std_id')
            ->orderBy('division')
            ->orderByRaw('roll_no IS NULL')
            ->orderBy('roll_no')
            ->orderBy('first_name')
            ->get();

        $receipts = FeeReceiptEntry::query()->get([
            'admission_id',
            'gr_no',
            'academic_year_id',
            'standard_id',
            'term',
            'last_yr_pending',
        ]);
        $years = MasterAcademicYear::query()->get()->keyBy('id');
        $masters = FeeMaster::query()
            ->where('status', 'active')
            ->get()
            ->keyBy(fn ($row) => $row->academic_year_id . '|' . $row->standard_id . '|' . $row->term);

        $rows = collect();
        foreach ($students as $student) {
            $history = $histories->get($student->id);
            $studentStd = (int) ($history->standard_id ?? $student->current_std_id);
            $gr = mb_strtolower(trim((string) $student->gr_no));
            $ownReceipts = $receipts->filter(function ($receipt) use ($student, $gr) {
                if ((int) $receipt->admission_id === (int) $student->id) {
                    return true;
                }

                return $gr !== '' && mb_strtolower(trim((string) $receipt->gr_no)) === $gr;
            });

            $pool = 0.0;
            if ($reportYear?->start_date) {
                foreach ($ownReceipts as $receipt) {
                    $receiptYear = $years->get($receipt->academic_year_id);
                    if ($receiptYear?->start_date && $receiptYear->start_date->gt($reportYear->start_date)) {
                        $pool += (float) $receipt->last_yr_pending;
                    }
                }
            }

            $open = [];
            foreach ($terms as $term) {
                $paidDirect = $ownReceipts->contains(function ($receipt) use ($yearId, $studentStd, $term) {
                    return (string) $receipt->term === $term
                        && (
                            ($yearId && (int) $receipt->academic_year_id === $yearId)
                            || ((int) $receipt->standard_id === $studentStd && (!$yearId || (int) $receipt->academic_year_id === $yearId))
                        );
                });
                if ($paidDirect) {
                    continue;
                }

                $master = $masters->get($yearId . '|' . $studentStd . '|' . $term)
                    ?: $masters->first(fn ($row) => (int) $row->standard_id === $studentStd && (string) $row->term === $term);
                $open[] = ['term' => $term, 'due' => $master ? (float) $master->total : 0.0];
            }

            foreach ($open as $item) {
                if ($item['due'] > 0 && ($pool + 0.009) >= $item['due']) {
                    $pool = round($pool - $item['due'], 2);
                    continue;
                }

                $rows->push([
                    'student_id' => $student->id,
                    'sort_std' => $studentStd,
                    'sort_div' => (string) ($history->division ?? $student->division),
                    'sort_roll' => (string) ($history->roll_no ?? $student->roll_no),
                    'sort_name' => (string) $student->full_name,
                    'term_sort' => (string) $item['term'],
                    'amount' => (float) $item['due'],
                    'term' => (string) $item['term'],
                    'gr' => $student->gr_no ?: '—',
                    'roll' => ($history->roll_no ?? $student->roll_no) ?: '—',
                    'name' => $student->full_name ?: '—',
                    'std' => $history->standard->name ?? $student->currentStandard->name ?? '—',
                    'division' => ($history->division ?? $student->division) ?: '—',
                ]);
            }
        }

        $sorted = $rows
            ->sortBy([
                ['sort_std', 'asc'],
                ['sort_div', 'asc'],
                ['sort_roll', 'asc'],
                ['sort_name', 'asc'],
                ['term_sort', 'asc'],
            ])
            ->values();

        $currentStudentId = null;
        $studentSr = 0;

        return $sorted->map(function ($row) use (&$currentStudentId, &$studentSr) {
            if ($row['student_id'] !== $currentStudentId) {
                $currentStudentId = $row['student_id'];
                $studentSr++;
            }

            return [
                'student_id' => $row['student_id'],
                'student_key' => $row['student_id'] . '|' . $row['gr'] . '|' . $row['name'],
                'amount' => $row['amount'],
                'cells' => [
                    ['text' => (string) $studentSr],
                    ['text' => $row['gr'], 'gr' => true],
                    ['text' => $row['roll']],
                    ['text' => $row['name'], 'name' => true],
                    ['text' => $row['std']],
                    ['text' => $row['division']],
                    ['text' => $this->feeStatusTermLabel($row['term'])],
                    ['text' => number_format((float) $row['amount'], 2), 'end' => true],
                ],
            ];
        });
    }

    public function print($id)
    {
        if (!Helper::directCan($this->modules['permission_prefix'] . '-list')
            && !Helper::directCan($this->modules['permission_prefix'] . '-create')) {
            abort(403);
        }

        $entry = FeeReceiptEntry::with(['standard', 'academicYear', 'admission'])->findOrFail($id);
        $school = SchoolDatabaseManager::getActiveSchool();
        $modules = $this->modules;

        return view($modules['folder_path'] . '.print', compact('entry', 'school', 'modules'));
    }

    public function busPrint($id)
    {
        if (!Helper::directCan($this->modules['permission_prefix'] . '-list')
            && !Helper::directCan($this->modules['permission_prefix'] . '-create')) {
            abort(403);
        }

        $entry = FeeReceiptEntry::with(['standard', 'academicYear'])->findOrFail($id);
        $school = SchoolDatabaseManager::getActiveSchool();
        if ((float) $entry->vehicle_fee <= 0) {
            return redirect()->route('fee-receipt-entry.edit', $entry->id)
                ->withErrors(['vehicle_fee' => 'Bus fee receipt needs a vehicle fee.']);
        }

        $lines = [
            [
                'description' => 'Vehicle Fee',
                'amount' => (float) $entry->vehicle_fee,
            ],
        ];

        return view($this->modules['folder_path'] . '.bus-print', compact('entry', 'lines', 'school'));
    }

    public function students(Request $request)
    {
        $standardId = (int) $request->input('standard_id');
        $division = trim((string) $request->input('division', ''));
        $yearId = (int) $request->input('academic_year_id');

        if (!$standardId || $division === '') {
            return response()->json(['success' => true, 'data' => []]);
        }

        $classByAdmission = $this->classSeatsForYear($yearId, $standardId, $division);
        if ($classByAdmission === []) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $rows = Admission::query()
            ->whereIn('id', array_keys($classByAdmission))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(function ($student) use ($classByAdmission) {
                $seat = $classByAdmission[$student->id];
                $name = trim(preg_replace(
                    '/\s+/',
                    ' ',
                    $student->first_name . ' ' . $student->last_name . ' ' . $student->father_name
                ));

                return array_merge([
                    'admission_id' => $student->id,
                    'gr_no'        => (string) $student->gr_no,
                    'roll_no'      => (string) ($seat['roll_no'] ?? ''),
                    'student_name' => $name,
                    'division'     => $seat['division'],
                    'standard_id'  => $seat['standard_id'],
                    'label'        => trim($student->gr_no . ' — ' . $name),
                ], $this->vehicleFeesFor($student->bus_route_village));
            })
            ->sortBy(function ($row) {
                $roll = trim((string) $row['roll_no']);

                return sprintf('%08d-%s', $roll === '' ? 999999 : (int) $roll, $row['student_name']);
            })
            ->values();

        return response()->json(['success' => true, 'data' => $rows]);
    }

    protected function classSeatsForYear(int $yearId, int $standardId, string $division): array
    {
        $divisionKey = mb_strtolower($division);
        $seats = [];

        if ($yearId) {
            $histories = AdmissionClassHistory::query()
                ->where('academic_year_id', $yearId)
                ->where('standard_id', $standardId)
                ->get();
            foreach ($histories as $history) {
                $histDiv = mb_strtolower(trim((string) $history->division));
                if ($histDiv !== '' && $histDiv !== $divisionKey) {
                    continue;
                }
                $seats[$history->admission_id] = [
                    'standard_id' => $standardId,
                    'division' => $history->division ?: $division,
                    'roll_no' => (string) ($history->roll_no ?? ''),
                ];
            }
        }

        $current = Admission::query()
            ->where('current_std_id', $standardId)
            ->whereRaw('LOWER(TRIM(division)) = ?', [$divisionKey])
            ->when($yearId, fn ($query) => $query->where('academic_year_id', $yearId))
            ->get(['id', 'division', 'roll_no']);

        foreach ($current as $student) {
            if (isset($seats[$student->id])) {
                continue;
            }
            $seats[$student->id] = [
                'standard_id' => $standardId,
                'division' => $student->division,
                'roll_no' => (string) ($student->roll_no ?? ''),
            ];
        }

        return $seats;
    }

    public function findStudent(Request $request)
    {
        $gr = trim((string) $request->input('gr_no', ''));
        if ($gr === '') {
            return response()->json(['success' => false, 'message' => 'Enter GR No.']);
        }

        $student = Admission::with('currentStandard')
            ->where('gr_no', $gr)
            ->first();

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student not found.']);
        }

        $name = trim(preg_replace(
            '/\s+/',
            ' ',
            $student->first_name . ' ' . $student->last_name . ' ' . $student->father_name
        ));

        $yearId = (int) ($request->input('academic_year_id') ?: ($student->academic_year_id ?: MasterAcademicYear::current()?->id));
        $history = AdmissionClassHistory::query()
            ->with('standard')
            ->where('admission_id', $student->id)
            ->where('academic_year_id', $yearId)
            ->first();
        $standardId = (int) ($history->standard_id ?? $student->current_std_id);
        $pending = $this->calculatePreviousPendingFee($student->id, $student->gr_no, $standardId, $yearId);

        return response()->json([
            'success' => true,
            'data'    => array_merge([
                'admission_id'         => $student->id,
                'student_name'         => $name,
                'gr_no'                => $student->gr_no,
                'roll_no'              => $history->roll_no ?? $student->roll_no,
                'standard_id'          => $standardId,
                'standard'             => $history->standard->name ?? $student->currentStandard->name ?? '',
                'division'             => $history->division ?? $student->division,
                'last_yr_pending'      => $pending['amount'],
                'last_yr_pending_help' => $pending['help'],
            ], $this->vehicleFeesFor($student->bus_route_village)),
        ]);
    }

    public function paidTerms(Request $request)
    {
        $yearId = (int) $request->input('academic_year_id');
        $admissionId = (int) $request->input('admission_id');
        $gr = trim((string) $request->input('gr_no', ''));
        $ignoreId = (int) $request->input('ignore_id');

        if (!$yearId || (!$admissionId && $gr === '')) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $rows = FeeReceiptEntry::query()
            ->where('academic_year_id', $yearId)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where(function ($query) use ($admissionId, $gr) {
                if ($admissionId) {
                    $query->where('admission_id', $admissionId);
                }
                if ($gr !== '') {
                    $admissionId ? $query->orWhere('gr_no', $gr) : $query->where('gr_no', $gr);
                }
            })
            ->orderBy('id')
            ->get(['id', 'term', 'receipt_no', 'date', 'net_amount', 'vehicle_fee', 'vehicle_fee_part']);

        $paid = [];
        $feeIPaid = false;
        $feeIIPaid = false;
        foreach ($rows as $row) {
            if (!isset($paid[$row->term])) {
                $paid[$row->term] = [
                    'term' => $row->term,
                    'receipt_no' => $row->receipt_no,
                    'date' => $row->date ? $row->date->format('d-m-Y') : '',
                    'net_amount' => number_format((float) $row->net_amount, 2),
                ];
            }
            $part = (string) ($row->vehicle_fee_part ?? '');
            if ($part === 'both') {
                $feeIPaid = true;
                $feeIIPaid = true;
            } elseif ($part === 'fee_i') {
                $feeIPaid = true;
            } elseif ($part === 'fee_ii') {
                $feeIIPaid = true;
            } elseif ((float) $row->vehicle_fee > 0) {
                if (!$feeIPaid) {
                    $feeIPaid = true;
                } else {
                    $feeIIPaid = true;
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => array_values($paid),
            'vehicle' => [
                'fee_i' => $feeIPaid,
                'fee_ii' => $feeIIPaid,
            ],
        ]);
    }

    public function loadFeeMaster(Request $request)
    {
        $data = $request->validate([
            'academic_year_id' => 'required|exists:master_academic_years,id',
            'standard_id'      => 'required|exists:master_standards,id',
            'term'             => ['required', Rule::in(array_keys(FeeMaster::TERMS))],
            'admission_id'     => 'nullable|integer',
            'gr_no'            => 'nullable|string|max:50',
            'ignore_id'        => 'nullable|integer',
        ]);

        $row = FeeMaster::where('academic_year_id', $data['academic_year_id'])
            ->where('standard_id', $data['standard_id'])
            ->where('term', $data['term'])
            ->where('status', 'active')
            ->first();

        $pending = $this->calculatePreviousPendingFee(
            !empty($data['admission_id']) ? (int) $data['admission_id'] : null,
            !empty($data['gr_no']) ? trim((string) $data['gr_no']) : null,
            (int) $data['standard_id'],
            (int) $data['academic_year_id'],
            !empty($data['ignore_id']) ? (int) $data['ignore_id'] : null
        );

        $yearFees = $this->annualAndCreditFees($data);

        if (!$row) {
            return response()->json([
                'success' => false,
                'message' => 'Fee Master not found for selected year / standard / term.',
                'data'    => array_merge([
                    'admission_fee'         => 0,
                    'deposit_fee'          => 0,
                    'term_fee'             => 0,
                    'school_fee'           => 0,
                    'total'                => 0,
                    'last_yr_pending'      => $pending['amount'],
                    'last_yr_pending_help' => $pending['help'],
                    'last_yr_breakdown'    => $pending['breakdown'],
                ], $yearFees),
            ]);
        }

        return response()->json([
            'success' => true,
            'data'    => array_merge([
                'admission_fee'         => $row->admission_fee,
                'deposit_fee'          => $row->deposit_fee,
                'term_fee'             => $row->term_fee,
                'school_fee'           => $row->school_fee,
                'total'                => $row->total,
                'last_yr_pending'      => $pending['amount'],
                'last_yr_pending_help' => $pending['help'],
                'last_yr_breakdown'    => $pending['breakdown'],
            ], $yearFees),
        ]);
    }

    protected function annualAndCreditFees(array $data): array
    {
        $heads = ['last_yr_pending', 'admission_fee', 'term_fee', 'school_fee', 'vehicle_fee'];
        $annual = array_fill_keys($heads, 0.0);
        $credit = array_fill_keys($heads, 0.0);

        $masters = FeeMaster::query()
            ->where('academic_year_id', $data['academic_year_id'])
            ->where('standard_id', $data['standard_id'])
            ->where('status', 'active')
            ->whereIn('term', array_keys(FeeMaster::TERMS))
            ->get();

        foreach ($masters as $master) {
            foreach (['admission_fee', 'term_fee', 'school_fee'] as $head) {
                $annual[$head] += (float) $master->{$head};
            }
        }

        $admissionId = (int) ($data['admission_id'] ?? 0);
        $gr = trim((string) ($data['gr_no'] ?? ''));
        if ($admissionId || $gr !== '') {
            $receipts = FeeReceiptEntry::query()
                ->where('academic_year_id', $data['academic_year_id'])
                ->where('standard_id', $data['standard_id'])
                ->where('term', '!=', $data['term'])
                ->when(!empty($data['ignore_id']), fn ($query) => $query->where('id', '!=', (int) $data['ignore_id']))
                ->where(function ($query) use ($admissionId, $gr) {
                    if ($admissionId) {
                        $query->where('admission_id', $admissionId);
                    }
                    if ($gr !== '') {
                        $admissionId ? $query->orWhere('gr_no', $gr) : $query->where('gr_no', $gr);
                    }
                })
                ->get();

            foreach ($receipts as $receipt) {
                foreach ($heads as $head) {
                    $credit[$head] += (float) $receipt->{$head};
                }
            }
        }

        $round = fn (array $amounts) => array_map(fn ($amount) => round($amount, 2), $amounts);

        return [
            'annual' => $round($annual),
            'credit' => $round($credit),
        ];
    }

    public function calculatePreviousPendingFee(?int $admissionId, ?string $grNo, int $currentStandardId, int $currentYearId, ?int $ignoreReceiptId = null): array
    {
        $student = null;
        if ($admissionId) {
            $student = Admission::find($admissionId);
        }
        if (!$student && !empty($grNo)) {
            $student = Admission::where('gr_no', $grNo)->first();
        }

        if (!$student) {
            return [
                'amount'       => 0.0,
                'total_unpaid' => 0.0,
                'already_paid' => 0.0,
                'breakdown'    => [],
                'help'         => '',
            ];
        }

        $allStandards = MasterStandard::where('status', 'active')->orderBy('id')->get();
        $currentStdIndex = $allStandards->search(fn ($s) => (int) $s->id === (int) $currentStandardId);

        if ($currentStdIndex === false || $currentStdIndex <= 0) {
            return [
                'amount'       => 0.0,
                'total_unpaid' => 0.0,
                'already_paid' => 0.0,
                'breakdown'    => [],
                'help'         => '',
            ];
        }

        $prevStandards = $allStandards->slice(0, $currentStdIndex)->values();
        $currentYear = MasterAcademicYear::find($currentYearId) ?? MasterAcademicYear::current();
        $prevYear = MasterAcademicYear::previous($currentYear);
        $prevYearId = $prevYear?->id;

        $totalUnpaid = 0.0;
        $breakdown = [];
        $terms = array_keys(FeeMaster::TERMS);

        foreach ($prevStandards as $prevStd) {
            foreach ($terms as $term) {
                $isPaid = FeeReceiptEntry::query()
                    ->where('standard_id', $prevStd->id)
                    ->where('term', $term)
                    ->when($ignoreReceiptId, fn ($q) => $q->where('id', '!=', $ignoreReceiptId))
                    ->where(function ($q) use ($student) {
                        if ($student->id) {
                            $q->where('admission_id', $student->id);
                        }
                        if ($student->gr_no) {
                            $student->id ? $q->orWhere('gr_no', $student->gr_no) : $q->where('gr_no', $student->gr_no);
                        }
                    })
                    ->exists();

                $feeMaster = null;
                if ($prevYearId) {
                    $feeMaster = FeeMaster::where('standard_id', $prevStd->id)
                        ->where('term', $term)
                        ->where('academic_year_id', $prevYearId)
                        ->where('status', 'active')
                        ->first();
                }
                if (!$feeMaster) {
                    $feeMaster = FeeMaster::where('standard_id', $prevStd->id)
                        ->where('term', $term)
                        ->where('academic_year_id', $currentYearId)
                        ->where('status', 'active')
                        ->first();
                }
                if (!$feeMaster) {
                    $feeMaster = FeeMaster::where('standard_id', $prevStd->id)
                        ->where('term', $term)
                        ->where('status', 'active')
                        ->first();
                }

                $termFee = $feeMaster ? (float) $feeMaster->total : 0.0;

                $breakdown[] = [
                    'standard_id'   => $prevStd->id,
                    'standard_name' => $prevStd->name,
                    'term'          => $term,
                    'term_fee'      => $termFee,
                    'is_paid'       => $isPaid,
                ];

                if (!$isPaid && $termFee > 0) {
                    $totalUnpaid += $termFee;
                }
            }
        }

        $alreadyPaidPending = (float) FeeReceiptEntry::query()
            ->when($ignoreReceiptId, fn ($q) => $q->where('id', '!=', $ignoreReceiptId))
            ->where(function ($q) use ($student) {
                if ($student->id) {
                    $q->where('admission_id', $student->id);
                }
                if ($student->gr_no) {
                    $student->id ? $q->orWhere('gr_no', $student->gr_no) : $q->where('gr_no', $student->gr_no);
                }
            })
            ->sum('last_yr_pending');

        $netPending = max(0.0, round($totalUnpaid - $alreadyPaidPending, 2));

        $unpaidItems = [];
        foreach ($breakdown as $item) {
            if (!$item['is_paid'] && $item['term_fee'] > 0) {
                $unpaidItems[] = $item['standard_name'] . ' ' . $item['term'] . ' (₹' . number_format($item['term_fee'], 2) . ')';
            }
        }

        $help = '';
        if ($netPending > 0) {
            $help = 'Pending: ' . implode(', ', $unpaidItems);
            if ($alreadyPaidPending > 0) {
                $help .= ' — Paid ₹' . number_format($alreadyPaidPending, 2);
            }
        }

        return [
            'amount'       => $netPending,
            'total_unpaid' => $totalUnpaid,
            'already_paid' => $alreadyPaidPending,
            'breakdown'    => $breakdown,
            'help'         => $help,
        ];
    }

    protected function clearVehicleFeeWithoutRoute(array $data): array
    {
        $admission = null;
        if (!empty($data['admission_id'])) {
            $admission = Admission::find($data['admission_id']);
        } elseif (!empty($data['gr_no'])) {
            $admission = Admission::where('gr_no', $data['gr_no'])->first();
        }

        $fees = $this->vehicleFeesFor($admission->bus_route_village ?? null);
        if ($fees['bus_route'] === '') {
            $data['vehicle_fee'] = 0;
            $data['vehicle_fee_part'] = null;
        }

        return $data;
    }

    protected function termAlreadyPaid(array $data, ?int $ignoreId = null): ?FeeReceiptEntry
    {
        $yearId = (int) ($data['academic_year_id'] ?? 0);
        $term = (string) ($data['term'] ?? '');
        $admissionId = (int) ($data['admission_id'] ?? 0);
        $gr = trim((string) ($data['gr_no'] ?? ''));

        if (!$yearId || $term === '' || (!$admissionId && $gr === '')) {
            return null;
        }

        return FeeReceiptEntry::query()
            ->where('academic_year_id', $yearId)
            ->where('term', $term)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where(function ($query) use ($admissionId, $gr) {
                if ($admissionId) {
                    $query->where('admission_id', $admissionId);
                }
                if ($gr !== '') {
                    $admissionId ? $query->orWhere('gr_no', $gr) : $query->where('gr_no', $gr);
                }
            })
            ->first();
    }

    protected function vehicleFeesFor(?string $villageName): array
    {
        $name = trim((string) $villageName);
        $empty = ['bus_route' => '', 'fee_i' => 0, 'fee_ii' => 0];
        if ($name === '') {
            return $empty;
        }

        $row = MasterBusRouteVillage::query()
            ->where('status', 'active')
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();

        if (!$row) {
            return $empty;
        }

        return [
            'bus_route' => $row->name,
            'fee_i' => round((float) $row->fee_i, 2),
            'fee_ii' => round((float) $row->fee_ii, 2),
        ];
    }

    protected function feeTotals(array $data): array
    {
        $fees = [
            'last_yr_pending' => round((float) ($data['last_yr_pending'] ?? 0), 2),
            'admission_fee'   => round((float) ($data['admission_fee'] ?? 0), 2),
            'term_fee'        => round((float) ($data['term_fee'] ?? 0), 2),
            'school_fee'      => round((float) ($data['school_fee'] ?? 0), 2),
            'vehicle_fee'     => round((float) ($data['vehicle_fee'] ?? 0), 2),
        ];
        $total = array_sum($fees);
        $less = round((float) ($data['less_amount'] ?? 0), 2);
        $net = max(0, round($total - $less, 2));

        return [$fees, $total, $less, $net];
    }

    protected function nextReceiptNo(): string
    {
        $last = FeeReceiptEntry::withTrashed()->orderByDesc('id')->value('receipt_no');
        $num = 1;
        if ($last && preg_match('/(\d+)/', (string) $last, $m)) {
            $num = (int) $m[1] + 1;
        }

        return (string) $num;
    }
}
