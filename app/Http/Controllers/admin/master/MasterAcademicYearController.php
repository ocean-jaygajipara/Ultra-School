<?php

namespace App\Http\Controllers\admin\master;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Master\MasterAcademicYear;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class MasterAcademicYearController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Academic Year',
            'folder_path' => 'software.module.master.academic-year',
            'route' => 'academic-year',
            'table_name' => (new MasterAcademicYear())->getTable(),
            'permission_prefix' => 'academic-year',
        ];
    }

    protected function permissions(): array
    {
        $modules = $this->modules;
        $modules['permission_list'] = Helper::directCan($modules['permission_prefix'] . '-list');
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');
        $modules['permission_edit'] = Helper::directCan($modules['permission_prefix'] . '-edit');
        $modules['permission_delete'] = Helper::directCan($modules['permission_prefix'] . '-delete');

        return $modules;
    }

    public function index(Request $request)
    {
        $modules = $this->permissions();
        if (!$modules['permission_list']) {
            if ($request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }
        View::share('modules', $modules);

        $columns = [
            (object) ['data' => 'name', 'name' => 'name', 'td_label' => 'Academic Year'],
            (object) ['data' => 'start_date', 'name' => 'start_date', 'td_label' => 'Start Date'],
            (object) ['data' => 'end_date', 'name' => 'end_date', 'td_label' => 'End Date'],
            (object) ['data' => 'is_current', 'name' => 'is_current', 'td_label' => 'Current', 'className' => 'text-center', 'searchable' => false],
            (object) ['data' => 'status', 'name' => 'status', 'td_label' => 'Status', 'className' => 'text-center', 'searchable' => false],
            (object) ['data' => 'action', 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' => 'text-center'],
        ];
        View::share('columns', $columns);

        if ($request->ajax()) {
            $data = MasterAcademicYear::query()->orderByDesc('start_date');

            return DataTables::of($data)
                ->filter(function ($query) use ($request) {
                    $search = $request->input('search.value');
                    if (!empty($search)) {
                        $query->where('name', 'like', "%{$search}%");
                    }
                })
                ->editColumn('start_date', fn ($row) => $row->start_date?->format('d-m-Y'))
                ->editColumn('end_date', fn ($row) => $row->end_date?->format('d-m-Y'))
                ->editColumn('is_current', function ($row) use ($modules) {
                    if ($row->is_current) {
                        return '<span class="badge bg-success"><i class="ti ti-circle-check me-1"></i>Current</span>';
                    }
                    if ($modules['permission_edit'] && $row->status === 'active') {
                        return '<a href="javascript:void(0)" class="btn btn-sm btn-outline-primary set-current"
                                    data-url="' . route($modules['route'] . '.set-current', $row->id) . '"
                                    data-name="' . e($row->name) . '">Set Current</a>';
                    }
                    return '-';
                })
                ->editColumn('status', function ($row) {
                    $class = $row->status === 'active' ? 'success' : 'danger';
                    return '<span class="badge bg-label-' . $class . '">' . ucfirst($row->status) . '</span>';
                })
                ->addColumn('action', function ($row) use ($modules) {
                    $btn = '';
                    if ($modules['permission_edit']) {
                        $btn .= '<a href="' . route($modules['route'] . '.edit', [$row->id]) . '" class="btn btn-light btn-icon mx-1">
                                    <i class="fa-solid fa-pen-to-square"></i></a>';
                    }
                    if ($modules['permission_delete'] && !$row->is_current) {
                        $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '"
                                    data-did="' . route($modules['route'] . '.destroy', [$row->id]) . '"
                                    class="btn btn-danger btn-icon deletebutton mx-1"><i class="fa-solid fa-trash"></i></a>';
                    }
                    return $btn ?: '-';
                })
                ->rawColumns(['is_current', 'status', 'action'])
                ->make(true);
        }

        return view($modules['folder_path'] . '.index');
    }

    public function create()
    {
        $modules = $this->permissions();
        if (!$modules['permission_add']) {
            abort(403, 'User does not have the right permissions.');
        }
        View::share('modules', $modules);

        return view($modules['folder_path'] . '.form', compact('modules'));
    }

    public function store(Request $request)
    {
        $modules = $this->permissions();
        if (!$modules['permission_add']) {
            abort(403, 'User does not have the right permissions.');
        }

        $validated = $this->validateYear($request);
        $validated['created_by'] = Auth::id();

        DB::transaction(function () use ($validated) {
            if (!empty($validated['is_current'])) {
                MasterAcademicYear::where('is_current', true)->update(['is_current' => false]);
            }
            MasterAcademicYear::create($validated);
        });

        return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' created successfully');
    }

    public function edit(string $id)
    {
        $modules = $this->permissions();
        if (!$modules['permission_edit']) {
            abort(403, 'User does not have the right permissions.');
        }
        View::share('modules', $modules);
        $edit = MasterAcademicYear::findOrFail($id);

        return view($modules['folder_path'] . '.form', compact('modules', 'edit'));
    }

    public function update(Request $request, string $id)
    {
        $modules = $this->permissions();
        if (!$modules['permission_edit']) {
            abort(403, 'User does not have the right permissions.');
        }

        $year = MasterAcademicYear::findOrFail($id);
        $validated = $this->validateYear($request, $year->id);
        $validated['updated_by'] = Auth::id();

        if ($year->is_current && empty($validated['is_current'])) {
            return Redirect::back()->withInput()
                ->withErrors('This is the current academic year. Set another year as current first.');
        }

        DB::transaction(function () use ($year, $validated) {
            if (!empty($validated['is_current'])) {
                MasterAcademicYear::where('id', '!=', $year->id)->where('is_current', true)->update(['is_current' => false]);
            }
            $year->update($validated);
        });

        return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' updated successfully');
    }

    public function setCurrent(Request $request, string $id)
    {
        $modules = $this->permissions();
        if (!$modules['permission_edit']) {
            return $this->sendError('User does not have the right permissions.', [], [], 403);
        }

        $year = MasterAcademicYear::where('status', 'active')->findOrFail($id);
        DB::transaction(function () use ($year) {
            MasterAcademicYear::where('is_current', true)->update(['is_current' => false]);
            $year->update(['is_current' => true, 'updated_by' => Auth::id()]);
        });

        return $this->sendResponse($year, $year->name . ' is now the current academic year.');
    }

    public function destroy(Request $request, string $id)
    {
        $modules = $this->permissions();
        if (!$modules['permission_delete']) {
            return response()->json(['success' => false, 'message' => 'User does not have the right permissions.'], 403);
        }

        $year = MasterAcademicYear::findOrFail($id);
        if ($year->is_current) {
            return response()->json(['success' => false, 'message' => 'Current academic year cannot be deleted.'], 422);
        }

        $year->deleted_by = Auth::id();
        $year->save();
        $year->delete();

        return response()->json(['success' => true, 'message' => $modules['title'] . ' deleted successfully.']);
    }

    protected function validateYear(Request $request, ?int $ignoreId = null): array
    {
        $request->merge([
            'start_date' => $this->toDbDate($request->input('start_date')),
            'end_date'   => $this->toDbDate($request->input('end_date')),
        ]);

        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:20',
                Rule::unique('master_academic_years', 'name')->whereNull('deleted_at')->ignore($ignoreId),
            ],
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
            'status'     => 'required|in:active,inactive',
        ], [
            'end_date.after' => 'End Date must be after Start Date.',
        ]);

        $overlap = MasterAcademicYear::when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->whereDate('start_date', '<=', $validated['end_date'])
            ->whereDate('end_date', '>=', $validated['start_date'])
            ->first();
        if ($overlap) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'start_date' => 'These dates overlap with academic year ' . $overlap->name . '.',
            ]);
        }

        $validated['is_current'] = $request->boolean('is_current') && $validated['status'] === 'active';

        return $validated;
    }

    protected function toDbDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }
        try {
            return Carbon::createFromFormat('d-m-Y', $value)->toDateString();
        } catch (\Throwable $e) {
            return $value;
        }
    }
}
