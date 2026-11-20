<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Http\Requests\TimetableRequest;
use App\Models\Admission;
use App\Models\Master\MasterStandard;
use App\Models\Timetable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;

class TimetableController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Timetable',
            'folder_path' => 'software.module.timetable',
            'route' => 'timetable',
            'table_name' => (new Timetable())->getTable(),
            'permission_prefix' => 'timetable',
        ];
    }

    public function index(Request $request)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_list'] = Helper::directCan($modules['permission_prefix'] . '-list');
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');
        $modules['permission_edit'] = Helper::directCan($modules['permission_prefix'] . '-edit');
        $modules['permission_delete'] = Helper::directCan($modules['permission_prefix'] . '-delete');

        if (!$modules['permission_list']) {
            if ($request->ajax()) {
                return response()->json(['error' => 'User does not have the right permissions.'], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }

        View::share('modules', $modules);
        View::share('sidebar_active', 'timetable');

        $columns = [
            (object)['data' => 'standard_name', 'name' => 'standard_id', 'td_label' => 'Standard'],
            (object)['data' => 'student_count', 'name' => 'student_count', 'td_label' => 'Students', 'orderable' => false, 'searchable' => false],
            (object)['data' => 'attachment', 'name' => 'attachment', 'td_label' => 'Attachment'],
            (object)[
                'data' => 'action',
                'name' => 'action',
                'td_label' => 'Action',
                'orderable' => false,
                'searchable' => false,
                'className' => 'w-10 text-center',
            ],
        ];

        View::share('columns', $columns);
        $standards = MasterStandard::where('status', 'active')->orderBy('id')->get(['id', 'name']);
        View::share('standards', $standards);

        try {
            if ($request->ajax()) {
                $data = Timetable::with(['standard']);

                return DataTables::of($data)
                    ->filter(function ($query) use ($request) {
                        if ($request->filled('standard_id')) {
                            $query->where('standard_id', (int) $request->standard_id);
                        }
                        if ($request->has('search') && ($request->search['value'] ?? '') !== '') {
                            $search = $request->search['value'];
                            $query->whereHas('standard', function ($q2) use ($search) {
                                $q2->where('name', 'like', "%{$search}%");
                            });
                        }
                    })
                    ->addColumn('standard_name', function ($row) {
                        return $row->standard?->name ?: '-';
                    })
                    ->addColumn('student_count', function ($row) {
                        if (!$row->standard_id) {
                            return '0';
                        }
                        $count = Admission::query()
                            ->where('current_std_id', $row->standard_id)
                            ->where(function ($q) {
                                $q->whereNull('status')
                                    ->orWhereRaw('LOWER(status) NOT IN (?, ?, ?)', ['cancel', 'cancelled', 'rejected']);
                            })
                            ->count();

                        return '<button type="button" class="btn btn-sm btn-outline-primary view-students-btn" data-id="' . $row->id . '">
                                    <i class="fa-solid fa-users me-1"></i> ' . $count . ' Students
                                </button>';
                    })
                    ->editColumn('attachment', function ($row) {
                        if (!$row->attachment) {
                            return '-';
                        }
                        $fileUrl = asset($row->attachment);

                        return '<a href="' . $fileUrl . '" target="_blank" class="text-primary">View Attachment</a>';
                    })
                    ->addColumn('action', function ($row) use ($modules) {
                        if ($row->deleted_at) {
                            return '<a href="javascript:void(0)" data-restore="' . route($modules['route'] . '.restore', ['id' => $row->id]) . '" class="btn btn-light mx-1 record-restore" title="Restore"><i class="ti ti-history"></i> Restore</a>';
                        }

                        $btn = '';
                        if ($modules['permission_edit']) {
                            $btn .= '<a href="' . route($modules['route'] . '.edit', [$row->id]) . '" class="btn btn-light btn-icon mx-1"><i class="fa-solid fa-pen-to-square"></i></a>';
                        }
                        if ($modules['permission_delete']) {
                            $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules['route'] . '.destroy', [$row->id]) . '" class="btn btn-danger btn-icon deletebutton mx-1"><i class="fa-solid fa-trash"></i></a>';
                        }
                        $btn .= '<a href="javascript:void(0)" data-did="' . route($modules['route'] . '.permanent-delete', [$row->id]) . '" class="btn btn-danger btn-icon permanentDeleteButton mx-1"><i class="fa-solid fa-ban"></i></a>';

                        return $btn ?: '-';
                    })
                    ->rawColumns(['action', 'attachment', 'student_count'])
                    ->make(true);
            }

            return view($modules['folder_path'] . '.index');
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }

    public function getStudents($id)
    {
        try {
            $timetable = Timetable::findOrFail($id);

            $students = Admission::query()
                ->where('current_std_id', $timetable->standard_id)
                ->where(function ($q) {
                    $q->whereNull('status')
                        ->orWhereRaw('LOWER(status) NOT IN (?, ?, ?)', ['cancel', 'cancelled', 'rejected']);
                })
                ->orderByRaw('CAST(gr_no AS UNSIGNED)')
                ->orderBy('gr_no')
                ->get()
                ->map(function ($adm) {
                    $name = trim(($adm->first_name ?? '') . ' ' . ($adm->father_name ?? '') . ' ' . ($adm->last_name ?? ''));

                    return [
                        'gr_no'  => $adm->gr_no ?? '-',
                        'name'   => $name !== '' ? $name : '-',
                        'mobile' => $adm->mobile_no ?? ($adm->parent_mobile_no ?? '-'),
                    ];
                });

            return response()->json([
                'status'   => true,
                'students' => $students,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function create()
    {
        $modules = $this->modules;
        View::share('modules', $modules);
        View::share('sidebar_active', 'timetable');
        View::share('standards', MasterStandard::where('status', 'active')->orderBy('id')->get(['id', 'name']));

        return view($modules['folder_path'] . '.form');
    }

    public function store(TimetableRequest $request)
    {
        $validated = $request->validated();

        try {
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $filename = time() . '_' . preg_replace('/[^A-Za-z0-9_\.-]/', '_', $file->getClientOriginalName());
                $destinationPath = public_path('uploads/timetable');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }
                $file->move($destinationPath, $filename);
                $validated['attachment'] = 'uploads/timetable/' . $filename;
            }

            $validated['course_id'] = null;
            $validated['batch_id'] = null;
            $validated['class_id'] = null;
            $validated['created_by'] = Auth::id();

            Timetable::create($validated);

            return Redirect::route($this->modules['route'] . '.index')
                ->withSuccess($this->modules['title'] . ' created successfully');
        } catch (\Exception $e) {
            return Redirect::back()->withInput()->withErrors($e->getMessage());
        }
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        $modules = $this->modules;
        View::share('modules', $modules);
        View::share('sidebar_active', 'timetable');
        View::share('standards', MasterStandard::where('status', 'active')->orderBy('id')->get(['id', 'name']));

        try {
            $edit = Timetable::findOrFail($id);
            View::share('edit', $edit);

            return view($modules['folder_path'] . '.form');
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }

    public function update(TimetableRequest $request, $id)
    {
        $validated = $request->validated();

        try {
            $timetable = Timetable::findOrFail($id);

            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $filename = time() . '_' . preg_replace('/[^A-Za-z0-9_\.-]/', '_', $file->getClientOriginalName());
                $destinationPath = public_path('uploads/timetable');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }
                if (!empty($timetable->attachment) && file_exists(public_path($timetable->attachment))) {
                    unlink(public_path($timetable->attachment));
                }
                $file->move($destinationPath, $filename);
                $validated['attachment'] = 'uploads/timetable/' . $filename;
            }

            $validated['course_id'] = null;
            $validated['batch_id'] = null;
            $validated['class_id'] = null;
            $validated['updated_by'] = Auth::id();

            $timetable->update($validated);

            return Redirect::route($this->modules['route'] . '.index')
                ->withSuccess($this->modules['title'] . ' updated successfully');
        } catch (\Exception $e) {
            return Redirect::back()->withInput()->withErrors($e->getMessage());
        }
    }

    public function destroy(Request $request, string $id)
    {
        $isAjax = $request->ajax();

        try {
            $timetable = Timetable::findOrFail($id);
            if (!empty($timetable->attachment) && file_exists(public_path($timetable->attachment))) {
                unlink(public_path($timetable->attachment));
            }
            $timetable->deleted_by = Auth::user()?->id;
            $timetable->save();
            $timetable->delete();

            if ($isAjax) {
                return response()->json(['success' => true, 'message' => 'Timetable deleted successfully.']);
            }

            return redirect()->back()->withSuccess('Timetable deleted successfully.');
        } catch (\Exception $e) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }

            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            Timetable::withTrashed()->findOrFail($id)->restore();

            return response()->json(['success' => true, 'message' => 'Timetable restored successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function permanentDelete($id)
    {
        try {
            $timetable = Timetable::withTrashed()->findOrFail($id);
            if (!empty($timetable->attachment) && file_exists(public_path($timetable->attachment))) {
                unlink(public_path($timetable->attachment));
            }
            $timetable->forceDelete();

            return response()->json(['success' => true, 'message' => 'Timetable permanently deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
