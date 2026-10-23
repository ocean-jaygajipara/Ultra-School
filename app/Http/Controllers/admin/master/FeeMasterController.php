<?php

namespace App\Http\Controllers\admin\master;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Master\FeeMaster;
use App\Models\Master\MasterAcademicYear;
use App\Models\Master\MasterFeeDetail;
use App\Models\Master\MasterStandard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class FeeMasterController extends Controller
{
    public $modules = [];

    protected array $exportableColumns = [
        'academic_year' => 'Academic Year',
        'standard'      => 'Standard',
        'term'          => 'Term',
        'admission_fee' => 'Admission Fee',
        'deposit_fee'  => 'Deposit Fee',
        'term_fee'     => 'Term Fee',
        'school_fee'   => 'School Fee',
        'total'         => 'Total',
        'status'        => 'Status',
    ];

    protected array $defaultExportColumns = [
        'academic_year',
        'standard',
        'term',
        'admission_fee',
        'deposit_fee',
        'term_fee',
        'school_fee',
        'total',
        'status',
    ];

    public function __construct()
    {
        $this->modules = [
            'title'             => 'Fee Master',
            'folder_path'       => 'software.module.master.fee-master',
            'route'             => 'fee-master',
            'table_name'        => (new FeeMaster())->getTable(),
            'permission_prefix' => 'fee-master',
        ];
    }

    protected function can(string $action): bool
    {
        $prefix = $this->modules['permission_prefix'];
        $primary = Helper::directCan($prefix . '-' . $action);
        if ($primary) {
            return true;
        }

        // Fallback to legacy fees permissions
        return Helper::directCan('fees-' . $action) || ($action === 'list' && Helper::directCan('fees-list'));
    }

    public function index(Request $request)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_list'] = $this->can('list');
        $modules['permission_add'] = $this->can('create');
        $modules['permission_edit'] = $this->can('edit');
        $modules['permission_delete'] = $this->can('delete');

        if (!$modules['permission_list']) {
            if ($request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }

        View::share('modules', $modules);

        try {
            $columns = [
                (object) ['data' => 'academic_year_name', 'name' => 'academic_year_name', 'td_label' => 'Academic Year'],
                (object) ['data' => 'standard_name', 'name' => 'standard_name', 'td_label' => 'Standard'],
                (object) ['data' => 'term', 'name' => 'term', 'td_label' => 'Term', 'className' => 'text-center'],
                (object) ['data' => 'admission_fee', 'name' => 'admission_fee', 'td_label' => 'Admission Fee', 'className' => 'text-end'],
                (object) ['data' => 'deposit_fee', 'name' => 'deposit_fee', 'td_label' => 'Deposit Fee', 'className' => 'text-end'],
                (object) ['data' => 'term_fee', 'name' => 'term_fee', 'td_label' => 'Term Fee', 'className' => 'text-end'],
                (object) ['data' => 'school_fee', 'name' => 'school_fee', 'td_label' => 'School Fee', 'className' => 'text-end'],
                (object) ['data' => 'total', 'name' => 'total', 'td_label' => 'Total', 'className' => 'text-end'],
                (object) ['data' => 'status', 'name' => 'status', 'td_label' => 'Status', 'className' => 'w-5 text-center'],
                (object) ['data' => 'action', 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' => 'w-10 text-center'],
            ];
            View::share('columns', $columns);

            if ($request->ajax()) {
                $data = FeeMaster::withTrashed()
                    ->with(['standard', 'academicYear'])
                    ->leftJoin('master_standards as ms', 'ms.id', '=', 'fee_masters.standard_id')
                    ->leftJoin('master_academic_years as may', 'may.id', '=', 'fee_masters.academic_year_id')
                    ->select('fee_masters.*', 'ms.name as standard_name', 'may.name as academic_year_name')
                    ->orderBy('fee_masters.id', 'desc');

                return DataTables::of($data)
                    ->filter(function ($query) use ($request) {
                        $search = $request->input('search.value');
                        if (!empty($search)) {
                            $query->where(function ($q) use ($search) {
                                $q->where('ms.name', 'like', "%{$search}%")
                                    ->orWhere('may.name', 'like', "%{$search}%")
                                    ->orWhere('fee_masters.term', 'like', "%{$search}%");
                            });
                        }
                    })
                    ->editColumn('academic_year_name', fn ($row) => e($row->academic_year_name ?: '—'))
                    ->editColumn('admission_fee', fn ($row) => '₹ ' . number_format((float) $row->admission_fee, 2))
                    ->editColumn('deposit_fee', fn ($row) => '₹ ' . number_format((float) $row->deposit_fee, 2))
                    ->editColumn('term_fee', fn ($row) => '₹ ' . number_format((float) $row->term_fee, 2))
                    ->editColumn('school_fee', fn ($row) => '₹ ' . number_format((float) $row->school_fee, 2))
                    ->editColumn('total', fn ($row) => '<strong>₹ ' . number_format((float) $row->total, 2) . '</strong>')
                    ->editColumn('status', function ($row) use ($modules) {
                        if ($row->deleted_at) {
                            return '<span class="badge bg-label-secondary">Deleted</span>';
                        }
                        if ($row->status === 'active') {
                            return '<button class="btn btn-success btn-sm dropdown-toggle" data-bs-toggle="dropdown">' . ucfirst($row->status) . '</button>
                                <ul class="dropdown-menu">
                                    <li><a href="javascript:void(0)" class="dropdown-item update-status" data-url="' . route($modules['route'] . '.status-update') . '" data-id="' . $row->id . '" data-update_status="inactive">Inactive</a></li>
                                </ul>';
                        }
                        if ($row->status === 'inactive') {
                            return '<button class="btn btn-danger btn-sm dropdown-toggle" data-bs-toggle="dropdown">' . ucfirst($row->status) . '</button>
                                <ul class="dropdown-menu">
                                    <li><a href="javascript:void(0)" class="dropdown-item update-status" data-url="' . route($modules['route'] . '.status-update') . '" data-id="' . $row->id . '" data-update_status="active">Active</a></li>
                                </ul>';
                        }

                        return '';
                    })
                    ->addColumn('action', function ($row) use ($modules) {
                        $btn = '';

                        if (!$row->deleted_at) {
                            if ($modules['permission_edit']) {
                                $btn .= '
                                    <a href="' . route($modules['route'] . '.edit', [$row->id]) . '"
                                       class="btn btn-light btn-icon mx-1">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>';
                            }
                            if ($modules['permission_delete']) {
                                $btn .= '
                                    <a href="javascript:void(0)"
                                       data-id="' . $row->id . '"
                                       data-did="' . route($modules['route'] . '.destroy', [$row->id]) . '"
                                       class="btn btn-danger btn-icon deletebutton mx-1">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>';
                            }
                        } else {
                            $btn .= '
                                <a href="javascript:void(0)"
                                   data-restore="' . route($modules['route'] . '.restore', ['id' => $row->id]) . '"
                                   class="btn btn-light mx-1 record-restore" title="Restore Data">
                                    <i class="ti ti-history"></i> Restore
                                </a>';
                        }

                        return $btn ?: '-';
                    })
                    ->rawColumns(['total', 'status', 'action'])
                    ->make(true);
            }

            $availableExportColumns = $this->exportableColumns;
            $defaultExportColumns = $this->defaultExportColumns;

            return view($modules['folder_path'] . '.index', compact('availableExportColumns', 'defaultExportColumns'));
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
        }
    }

    public function create()
    {
        if (!$this->can('create')) {
            abort(403, 'User does not have the right permissions.');
        }

        $modules = $this->modules;
        $modules['permission_add'] = true;
        View::share('modules', $modules);

        $standards = MasterStandard::where('status', 'active')->orderBy('id')->get(['id', 'name']);
        $academicYears = MasterAcademicYear::where('status', 'active')->orderByDesc('start_date')->get();
        $currentAcademicYear = MasterAcademicYear::current();
        $terms = FeeMaster::TERMS;

        return view($modules['folder_path'] . '.form', compact(
            'modules', 'standards', 'terms', 'academicYears', 'currentAcademicYear'
        ));
    }

    public function store(Request $request)
    {
        if (!$this->can('create')) {
            abort(403, 'User does not have the right permissions.');
        }

        $modules = $this->modules;
        $data = $this->validatedPayload($request);

        try {
            DB::transaction(function () use ($data) {
                $row = FeeMaster::create(array_merge($data, [
                    'created_by' => Auth::id(),
                    'status'     => 'active',
                ]));
                $this->syncFeeDetails((int) $row->standard_id, $row->term, $data);
            });

            if ($request->action === 'save_next') {
                return redirect()->route($modules['route'] . '.create')
                    ->withSuccess($modules['title'] . ' created successfully! Add next.');
            }

            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' created successfully');
        } catch (\Exception $e) {
            return Redirect::back()->withErrors($e->getMessage())->withInput();
        }
    }

    public function edit(string $id)
    {
        if (!$this->can('edit')) {
            abort(403, 'User does not have the right permissions.');
        }

        try {
            $modules = $this->modules;
            $modules['permission_edit'] = true;
            View::share('modules', $modules);

            $edit = FeeMaster::findOrFail($id);
            $standards = MasterStandard::where('status', 'active')->orderBy('id')->get(['id', 'name']);
            $academicYears = MasterAcademicYear::where('status', 'active')->orderByDesc('start_date')->get();
            $currentAcademicYear = MasterAcademicYear::current();
            $terms = FeeMaster::TERMS;

            return view($modules['folder_path'] . '.form', compact(
                'modules', 'edit', 'standards', 'terms', 'academicYears', 'currentAcademicYear'
            ));
        } catch (\Exception $e) {
            return Redirect::route($this->modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    public function update(Request $request, string $id)
    {
        if (!$this->can('edit')) {
            abort(403, 'User does not have the right permissions.');
        }

        $modules = $this->modules;
        $data = $this->validatedPayload($request, (int) $id);

        try {
            DB::transaction(function () use ($data, $id) {
                $row = FeeMaster::findOrFail($id);
                $row->update(array_merge($data, [
                    'updated_by' => Auth::id(),
                ]));
                $this->syncFeeDetails((int) $row->standard_id, $row->term, $data);
            });

            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' updated successfully');
        } catch (\Exception $e) {
            return Redirect::back()->withErrors($e->getMessage())->withInput();
        }
    }

    public function status_update(Request $request)
    {
        if (!$this->can('edit')) {
            return $this->sendError('User does not have the right permissions.', [], [], 403);
        }

        $modules = $this->modules;
        $validator = Validator::make($request->all(), [
            'id'            => ['required', Rule::exists($modules['table_name'], 'id')],
            'update_status' => ['required', 'in:active,inactive'],
        ]);

        if ($validator->fails() && $request->ajax()) {
            return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
        }

        try {
            $row = FeeMaster::findOrFail($request->id);
            $row->status = $request->update_status;
            $row->save();

            if ($request->ajax()) {
                return $this->sendResponse($row, $modules['title'] . ' status updated successfully.');
            }

            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' status updated successfully.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return $this->sendError($e->getMessage(), [], [], 401);
            }

            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    public function destroy(Request $request, string $id)
    {
        if (!$this->can('delete')) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'User does not have the right permissions.'], 403);
            }
            abort(403);
        }

        try {
            $row = FeeMaster::findOrFail($id);
            $row->deleted_by = Auth::id();
            $row->save();
            $row->delete();

            $msg = $this->modules['title'] . ' deleted successfully.';
            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => $msg]);
            }

            return redirect()->route($this->modules['route'] . '.index')->withSuccess($msg);
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }

            return redirect()->route($this->modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            $row = FeeMaster::withTrashed()->findOrFail($id);
            $row->restore();

            return response()->json([
                'status'  => 'success',
                'message' => 'Fee Master restored successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function fetch(Request $request)
    {
        if (!$this->can('list') && !$this->can('create') && !$this->can('edit')) {
            abort(403);
        }

        $data = $request->validate([
            'standard_id' => 'required|exists:master_standards,id',
            'term'        => ['required', Rule::in(array_keys(FeeMaster::TERMS))],
            'ignore_id'   => 'nullable|integer',
        ]);

        $query = FeeMaster::where('standard_id', $data['standard_id'])->where('term', $data['term']);
        if (!empty($data['ignore_id'])) {
            $query->where('id', '!=', $data['ignore_id']);
        }
        $row = $query->first();

        return response()->json([
            'success' => true,
            'found'   => (bool) $row,
            'data'    => $row ? [
                'admission_fee' => $row->admission_fee,
                'deposit_fee'  => $row->deposit_fee,
                'term_fee'     => $row->term_fee,
                'school_fee'   => $row->school_fee,
                'total'        => $row->total,
            ] : null,
        ]);
    }

    protected function validatedPayload(Request $request, ?int $ignoreId = null): array
    {
        $unique = Rule::unique('fee_masters', 'term')
            ->where(fn ($q) => $q
                ->where('academic_year_id', $request->input('academic_year_id'))
                ->where('standard_id', $request->input('standard_id'))
                ->whereNull('deleted_at'));

        if ($ignoreId) {
            $unique = $unique->ignore($ignoreId);
        }

        $validated = $request->validate([
            'academic_year_id' => 'required|exists:master_academic_years,id',
            'standard_id'      => 'required|exists:master_standards,id',
            'term'             => ['required', Rule::in(array_keys(FeeMaster::TERMS)), $unique],
            'admission_fee'    => 'nullable|numeric|min:0',
            'deposit_fee'     => 'nullable|numeric|min:0',
            'term_fee'        => 'nullable|numeric|min:0',
            'school_fee'      => 'nullable|numeric|min:0',
        ], [
            'term.unique' => 'Fee Master for this Academic Year, Standard and Term already exists.',
        ]);

        $payload = [
            'academic_year_id' => (int) $validated['academic_year_id'],
            'standard_id'      => (int) $validated['standard_id'],
            'term'             => $validated['term'],
            'admission_fee'    => round((float) ($validated['admission_fee'] ?? 0), 2),
            'deposit_fee'     => round((float) ($validated['deposit_fee'] ?? 0), 2),
            'term_fee'        => round((float) ($validated['term_fee'] ?? 0), 2),
            'school_fee'      => round((float) ($validated['school_fee'] ?? 0), 2),
        ];
        $payload['total'] = round(
            $payload['admission_fee'] + $payload['deposit_fee'] + $payload['term_fee'] + $payload['school_fee'],
            2
        );

        return $payload;
    }

    protected function syncFeeDetails(int $standardId, string $term, array $amounts): void
    {
        foreach (FeeMaster::FEE_TYPES as $field => $label) {
            $feeName = $label . ' (' . $term . ')';
            $existing = MasterFeeDetail::withTrashed()
                ->where('standard_id', $standardId)
                ->where('fee_name', $feeName)
                ->first();

            if ($existing) {
                if ($existing->trashed()) {
                    $existing->restore();
                }
                $existing->update([
                    'amount'     => $amounts[$field] ?? 0,
                    'status'     => 'active',
                    'updated_by' => Auth::id(),
                    'deleted_by' => null,
                ]);
            } else {
                MasterFeeDetail::create([
                    'standard_id' => $standardId,
                    'fee_name'    => $feeName,
                    'amount'      => $amounts[$field] ?? 0,
                    'status'      => 'active',
                    'created_by'  => Auth::id(),
                ]);
            }
        }
    }
}
