<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\FeeReceiptEntry;
use App\Models\Master\FeeMaster;
use App\Models\Master\MasterAcademicYear;
use App\Models\Master\MasterDivision;
use App\Models\Master\MasterStandard;
use App\Services\SchoolDatabaseManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;
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
            'permission_prefix' => 'fees-collection',
        ];
    }

    public function index(Request $request)
    {
        $modules = $this->modules;
        $modules['permission_list'] = Helper::directCan($modules['permission_prefix'] . '-list');
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');

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
                    $printUrl = route($modules['route'] . '.print', $row->id);

                    return '<a href="' . $printUrl . '" target="_blank" class="btn btn-sm btn-primary" title="Print">'
                        . '<i class="ti ti-printer"></i></a>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view($modules['folder_path'] . '.index', compact('modules'));
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
            'less_amount'      => 'nullable|numeric|min:0',
            'print_copies'     => 'nullable|integer|min:1|max:10',
            'is_preview'       => 'nullable|boolean',
            'mode'             => 'nullable|string|max:50',
            'remark'           => 'nullable|string|max:500',
        ]);

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
            'total'            => $total,
            'less_amount'      => $less,
            'net_amount'       => $net,
            'print_copies'     => (int) ($data['print_copies'] ?? 1),
            'is_preview'       => (bool) ($data['is_preview'] ?? false),
            'mode'             => $data['mode'] ?? 'Cash',
            'remark'           => $data['remark'] ?? null,
            'fee_snapshot'     => $fees,
            'status'           => 'active',
            'created_by'       => Auth::id(),
        ]);

        if ($request->boolean('print_after_save')) {
            return redirect()->route('fee-receipt-entry.print', $row->id)
                ->withSuccess('Fee receipt saved successfully.');
        }

        return redirect()->route('fee-receipt-entry.index')
            ->withSuccess('Fee receipt saved successfully.');
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

    public function students(Request $request)
    {
        $standardId = (int) $request->input('standard_id');
        $division = trim((string) $request->input('division', ''));

        if (!$standardId || $division === '') {
            return response()->json(['success' => true, 'data' => []]);
        }

        $query = Admission::query()
            ->where('current_std_id', $standardId)
            ->where('division', $division);

        $rows = $query
            ->orderByRaw('roll_no IS NULL')
            ->orderBy('roll_no')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(function ($student) {
                $name = trim(preg_replace(
                    '/\s+/',
                    ' ',
                    $student->first_name . ' ' . $student->last_name . ' ' . $student->father_name
                ));

                return [
                    'admission_id' => $student->id,
                    'gr_no'        => (string) $student->gr_no,
                    'roll_no'      => (string) ($student->roll_no ?? ''),
                    'student_name' => $name,
                    'division'     => $student->division,
                    'standard_id'  => $student->current_std_id,
                    'label'        => trim($student->gr_no . ' — ' . $name),
                ];
            })
            ->values();

        return response()->json(['success' => true, 'data' => $rows]);
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

        return response()->json([
            'success' => true,
            'data'    => [
                'admission_id' => $student->id,
                'student_name' => $name,
                'gr_no'        => $student->gr_no,
                'roll_no'      => $student->roll_no,
                'standard_id'  => $student->current_std_id,
                'standard'     => $student->currentStandard->name ?? '',
                'division'     => $student->division,
            ],
        ]);
    }

    public function loadFeeMaster(Request $request)
    {
        $data = $request->validate([
            'academic_year_id' => 'required|exists:master_academic_years,id',
            'standard_id'      => 'required|exists:master_standards,id',
            'term'             => ['required', Rule::in(array_keys(FeeMaster::TERMS))],
        ]);

        $row = FeeMaster::where('academic_year_id', $data['academic_year_id'])
            ->where('standard_id', $data['standard_id'])
            ->where('term', $data['term'])
            ->where('status', 'active')
            ->first();

        if (!$row) {
            return response()->json([
                'success' => false,
                'message' => 'Fee Master not found for selected year / standard / term.',
                'data'    => [
                    'admission_fee' => 0,
                    'deposit_fee'  => 0,
                    'term_fee'     => 0,
                    'school_fee'   => 0,
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'admission_fee' => $row->admission_fee,
                'deposit_fee'  => $row->deposit_fee,
                'term_fee'     => $row->term_fee,
                'school_fee'   => $row->school_fee,
                'total'        => $row->total,
            ],
        ]);
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
