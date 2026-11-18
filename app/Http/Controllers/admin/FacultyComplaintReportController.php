<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\FacultyComplaintReport;
use App\Models\Master\MasterAcademicYear;
use App\Models\Master\MasterDivision;
use App\Models\Master\MasterStandard;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\DataTables;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Database\Eloquent\Builder;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class FacultyComplaintReportController extends Controller
{
    public $modules = [];
    protected array $exportableColumns = [
        'gr_no' => 'GR No.',
        'student_name' => 'Student Name',
        'date' => 'Date',
        'faculty_name' => 'Teacher Name',
        'complaint' => 'Complaint',
    ];

    protected array $defaultExportColumns = [
        'gr_no',
        'student_name',
        'date',
        'faculty_name',
        'complaint',
    ];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Teacher Complaint Report',
            'folder_path' => 'software.module.teacher-complaint-report',
            'route' => 'teacher-complaint-report',
            'table_name' => (new FacultyComplaintReport())->getTable(),
            'permission_prefix' => 'teacher-complaint-report',
            'public_folder' => public_path(),
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
        View::share('modules', $modules);

        try {
            if (!$modules['permission_list']) {
                return Redirect::route('software.dashboard')->withErrors('User does not have the right permissions.');
            }

            $columns = [
                (object)['data' => "gr_no", 'name' => 'gr_no', 'td_label' => 'GR No.'],
                (object)['data' => "student_name", 'name' => 'student_name', 'td_label' => 'Student Name', 'orderable' => false],
                (object)['data' => "date", 'name' => 'date', 'td_label' => 'Date'],
                (object)['data' => "faculty_name", 'name' => 'faculty_name', 'td_label' => 'Teacher', 'orderable' => false],
                (object)['data' => "complaint", 'name' => 'complaint', 'td_label' => 'Complaint'],
                (object)['data' => "action", 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' => 'w-10 text-center'],
            ];
            View::share("columns", $columns);

            if ($request->ajax()) {
                $data = FacultyComplaintReport::with(['admission', 'faculty'])
                    ->select('teacher_complaint_report.*')
                    ->orderBy('teacher_complaint_report.id', 'desc');

                return DataTables::of($data)
                    ->filter(function ($query) use ($request) {
                        $searchInput = $request->input('search');
                        $searchValue = is_array($searchInput)
                            ? ($searchInput['value'] ?? '')
                            : (string) ($searchInput ?? '');
                        $searchValue = trim($searchValue);

                        if ($searchValue === '') {
                            return;
                        }

                        $query->where(function ($q) use ($searchValue) {
                            $q->where('gr_no', 'like', "%{$searchValue}%")
                                ->orWhere('complaint', 'like', "%{$searchValue}%")
                                ->orWhereHas('admission', function ($sq) use ($searchValue) {
                                    $sq->where('first_name', 'like', "%{$searchValue}%")
                                        ->orWhere('last_name', 'like', "%{$searchValue}%")
                                        ->orWhere('father_name', 'like', "%{$searchValue}%")
                                        ->orWhere('gr_no', 'like', "%{$searchValue}%");
                                })
                                ->orWhereHas('faculty', function ($sq) use ($searchValue) {
                                    $sq->where('name', 'like', "%{$searchValue}%");
                                });
                        });
                    })
                    ->editColumn('date', function ($record) {
                        return $this->formatComplaintDate($record->date);
                    })
                    ->addColumn('student_name', function ($record) {
                        if ($record->admission) {
                            $first = $record->admission->first_name ?? '';
                            $last = $record->admission->last_name ?? '';
                            $father = $record->admission->father_name ?? '';
                            return trim($first . ' ' . $last . ' ' . $father);
                        }
                        return '-';
                    })
                    ->addColumn('faculty_name', function ($record) {
                        return $record->faculty ? $record->faculty->name : '-';
                    })
                    ->addColumn('action', function ($row) use ($modules) {
                        $btn = '';

                        if (!$row->deleted_at) {
                            if ($modules['permission_edit']) {
                                $btn .= '
                                    <a href="' . route($modules["route"] . ".edit", [$row->id]) . '"
                                       class="btn btn-light btn-icon mx-1">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>';
                            }

                            if ($modules['permission_delete']) {
                                $btn .= '
                                    <a href="javascript:void(0)"
                                       data-id="' . $row->id . '"
                                       data-did="' . route($modules["route"] . ".destroy", [$row->id]) . '"
                                       class="btn btn-danger btn-icon deletebutton mx-1">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>';

                                $btn .= '
                                    <a href="javascript:void(0)"
                                       data-id="' . $row->id . '"
                                       data-did="' . route($modules["route"] . ".permanent-delete", [$row->id]) . '"
                                       class="btn btn-dark btn-icon permanentDeleteButton mx-1"
                                       title="Permanent Delete">
                                        <i class="fa-solid fa-trash-arrow-up"></i>
                                    </a>';
                            }
                        } else {
                            $btn .= '
                                <a href="javascript:void(0)"
                                   data-restore="' . route($modules["route"] . ".restore", ['id' => $row->id]) . '"
                                   class="btn btn-light mx-1 record-restore" title="Restore Data">
                                    <i class="ti ti-history"></i> Restore
                                </a>';
                        }

                        return $btn ?: '-';
                    })
                    ->rawColumns(['action'])
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
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');
        View::share('modules', $modules);

        if (!$modules['permission_add']) {
            return Redirect::route($modules['route'] . '.index')->withErrors('User does not have the right permissions.');
        }

        return view($modules['folder_path'] . '.form', array_merge(
            compact('modules'),
            $this->formLookups()
        ));
    }

    public function store(Request $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        $canCreate = Helper::directCan($modules['permission_prefix'] . '-create');
        if (!$canCreate) {
            return Redirect::route($modules['route'] . '.index')->withErrors('User does not have the right permissions.');
        }

        $request->validate([
            'academic_year_id' => 'required|exists:master_academic_years,id',
            'standard_id' => 'required|exists:master_standards,id',
            'division' => 'required|string|max:50',
            'gr_no' => 'required|array|min:1',
            'gr_no.*' => 'required|string|exists:admission,gr_no',
            'date' => 'required|date',
            'complaint' => 'required|string',
            'faculty_id' => 'required|exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            $grNos = array_values(array_unique(array_map('strval', $request->gr_no)));
            $admissions = Admission::whereIn('gr_no', $grNos)->get()->keyBy(function ($row) {
                return (string) $row->gr_no;
            });

            foreach ($grNos as $grNo) {
                $admission = $admissions->get((string) $grNo);

                FacultyComplaintReport::create([
                    'academic_year_id' => $request->academic_year_id,
                    'standard_id' => $request->standard_id,
                    'division' => $request->division,
                    'admission_id' => $admission?->id,
                    'gr_no' => $grNo,
                    'date' => $request->date,
                    'complaint' => $request->complaint,
                    'faculty_id' => $request->faculty_id,
                ]);
            }

            DB::commit();

            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage())->withInput();
        }
    }

    public function edit(string $id)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        $canEdit = Helper::directCan($modules['permission_prefix'] . '-edit');
        if (!$canEdit) {
            return Redirect::route($modules['route'] . '.index')->withErrors('User does not have the right permissions.');
        }

        try {
            $edit = FacultyComplaintReport::with('admission')->findOrFail($id);
            $selectedAdmission = $edit->admission;
            $selectedAcademicYearId = $edit->academic_year_id ?? $selectedAdmission->academic_year_id ?? null;
            $selectedStandardId = $edit->standard_id ?? $selectedAdmission->current_std_id ?? null;
            $selectedDivision = $edit->division ?? $selectedAdmission->division ?? null;

            return view($modules['folder_path'] . '.form', array_merge(
                compact(
                    'modules',
                    'edit',
                    'selectedAdmission',
                    'selectedAcademicYearId',
                    'selectedStandardId',
                    'selectedDivision'
                ),
                $this->formLookups()
            ));
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage())->withInput();
        }
    }

    public function update(Request $request, string $id)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        $canEdit = Helper::directCan($modules['permission_prefix'] . '-edit');
        if (!$canEdit) {
            return Redirect::route($modules['route'] . '.index')->withErrors('User does not have the right permissions.');
        }

        $request->validate([
            'academic_year_id' => 'required|exists:master_academic_years,id',
            'standard_id' => 'required|exists:master_standards,id',
            'division' => 'required|string|max:50',
            'gr_no' => 'required|string|exists:admission,gr_no',
            'date' => 'required|date',
            'complaint' => 'required|string',
            'faculty_id' => 'required|exists:users,id',
        ]);

        try {
            $report = FacultyComplaintReport::findOrFail($id);
            $admission = Admission::where('gr_no', $request->gr_no)->first();

            $report->update([
                'academic_year_id' => $request->academic_year_id,
                'standard_id' => $request->standard_id,
                'division' => $request->division,
                'admission_id' => $admission?->id,
                'gr_no' => $request->gr_no,
                'date' => $request->date,
                'complaint' => $request->complaint,
                'faculty_id' => $request->faculty_id,
            ]);

            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' updated successfully');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage())->withInput();
        }
    }

    public function destroy(Request $request, string $id)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        $canDelete = Helper::directCan($modules['permission_prefix'] . '-delete');
        if (!$canDelete) {
            return response()->json(['success' => false, 'message' => 'User does not have the right permissions.']);
        }

        try {
            $report = FacultyComplaintReport::findOrFail($id);
            if ($report) {
                if ($report->delete()) {
                    return response()->json([
                        'success' => true,
                        'message' => $modules['title'] . ' deleted successfully.'
                    ]);
                }
            }
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete ' . $modules['title']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function restore($id)
    {
        $canDelete = Helper::directCan($this->modules['permission_prefix'] . '-delete');
        if (!$canDelete) {
            return response()->json(['success' => false, 'message' => 'User does not have the right permissions.']);
        }

        try {
            $report = FacultyComplaintReport::withTrashed()->findOrFail($id);
            $report->restore();

            return response()->json([
                'success' => true,
                'message' => 'Teacher Complaint Report restored successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function permanentDelete($id)
    {
        $canDelete = Helper::directCan($this->modules['permission_prefix'] . '-delete');
        if (!$canDelete) {
            return response()->json(['success' => false, 'message' => 'User does not have the right permissions.']);
        }

        try {
            $report = FacultyComplaintReport::withTrashed()->findOrFail($id);
            $report->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Teacher Complaint Report permanently deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function exportExcel(Request $request)
    {
        $this->authorizeFacultyComplaintReportList();

        $scope = strtolower($request->get('scope', 'filtered')) === 'all' ? 'all' : 'filtered';
        $selectedColumns = $this->resolveSelectedColumns($request->input('columns', []));
        $columnLabels = $this->mapColumnLabels($selectedColumns);

        $records = $this->buildFacultyComplaintReportExportQuery($request, $scope)->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach (array_values($columnLabels) as $index => $label) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $label);
        }

        $rowNumber = 2;
        foreach ($records as $record) {
            foreach ($selectedColumns as $columnIndex => $columnKey) {
                $sheet->setCellValueByColumnAndRow(
                    $columnIndex + 1,
                    $rowNumber,
                    $this->formatColumnValueForExcel($record, $columnKey)
                );
            }
            $rowNumber++;
        }

        if (!empty($selectedColumns)) {
            for ($i = 1; $i <= count($selectedColumns); $i++) {
                $columnLetter = Coordinate::stringFromColumnIndex($i);
                $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
            }
        }

        $fileName = 'teacher-complaint-report-' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function printView(Request $request)
    {
        $this->authorizeFacultyComplaintReportList();

        $scope = strtolower($request->get('scope', 'filtered')) === 'all' ? 'all' : 'filtered';
        $selectedColumns = $this->resolveSelectedColumns($request->input('columns', []));
        $columnLabels = $this->mapColumnLabels($selectedColumns);
        $searchTerm = $scope === 'filtered' ? trim((string) $request->get('search', '')) : null;

        $records = $this->buildFacultyComplaintReportExportQuery($request, $scope)->get();

        return view($this->modules['folder_path'] . '.print', [
            'records' => $records,
            'columnLabels' => $columnLabels,
            'columnKeys' => $selectedColumns,
            'scope' => $scope,
            'searchTerm' => $searchTerm,
            'generatedAt' => now(),
        ]);
    }

    protected function authorizeFacultyComplaintReportList(): void
    {
        $canList = Helper::directCan($this->modules['permission_prefix'] . '-list');
        if (!$canList) {
            abort(403, 'User does not have the right permissions.');
        }
    }

    protected function formLookups(): array
    {
        $faculties = User::where('status', 'active')
            ->whereHas('roles', function ($q) {
                $q->where(DB::raw('LOWER(name)'), 'like', '%faculty%');
            })->where('name', 'not like', '%Ocean%')->get();

        if ($faculties->isEmpty()) {
            $faculties = User::where('status', 'active')->where('name', 'not like', '%Ocean%')->get();
        }

        return [
            'faculties' => $faculties,
            'standards' => MasterStandard::where('status', 'active')->orderBy('id')->get(['id', 'name']),
            'divisions' => MasterDivision::where('status', 'active')->orderBy('name')->pluck('name'),
            'academicYears' => MasterAcademicYear::where('status', 'active')->orderByDesc('start_date')->get(),
            'currentAcademicYear' => MasterAcademicYear::current(),
        ];
    }

    protected function resolveSelectedColumns($columns): array
    {
        if (!is_array($columns) || empty($columns)) {
            return $this->defaultExportColumns;
        }

        $validColumns = array_keys($this->exportableColumns);
        $filtered = array_values(array_filter($columns, function ($column) use ($validColumns) {
            return in_array($column, $validColumns, true);
        }));

        return !empty($filtered) ? $filtered : $this->defaultExportColumns;
    }

    protected function buildFacultyComplaintReportExportQuery(Request $request, string $scope): Builder
    {
        $query = FacultyComplaintReport::with(['admission', 'faculty'])->select('teacher_complaint_report.*');

        if ($scope === 'all') {
            return $query;
        }

        $search = trim((string) $request->get('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                if (is_numeric($search) && \App\Models\Admission::where('id', $search)->exists()) {
                    $q->where('admission_id', $search);
                } else {
                    $q->whereHas('admission', function ($sq) use ($search) {
                        $sq->where('first_name', 'like', "%{$search}%")
                           ->orWhere('last_name', 'like', "%{$search}%")
                           ->orWhere('father_name', 'like', "%{$search}%")
                           ->orWhere('gr_no', 'like', "%{$search}%");
                    });
                }
            });
        }

        return $query;
    }

    protected function mapColumnLabels(array $selectedColumns): array
    {
        $labels = [];
        foreach ($selectedColumns as $columnKey) {
            $labels[$columnKey] = $this->exportableColumns[$columnKey] ?? ucfirst(str_replace('_', ' ', $columnKey));
        }
        return $labels;
    }

    protected function formatColumnValueForExcel(FacultyComplaintReport $record, string $columnKey)
    {
        switch ($columnKey) {
            case 'gr_no':
                return $record->gr_no ?? '-';
            case 'student_name':
                if ($record->admission) {
                    $first = $record->admission->first_name ?? '';
                    $last = $record->admission->last_name ?? '';
                    $father = $record->admission->father_name ?? '';
                    return trim($first . ' ' . $last . ' ' . $father);
                }
                return '-';
            case 'date':
                return $this->formatComplaintDate($record->date);
            case 'faculty_name':
                return $record->faculty ? $record->faculty->name : '-';
            case 'complaint':
                return $record->complaint ?? '-';
            default:
                return '';
        }
    }

    protected function formatComplaintDate($date): string
    {
        if (empty($date)) {
            return '-';
        }

        if ($date instanceof \Carbon\CarbonInterface) {
            return $date->format('d-m-Y');
        }

        $dateString = trim((string) $date);
        if ($dateString === '') {
            return '-';
        }

        try {
            if (preg_match('/^\d{4}-\d{2}-\d{2}/', $dateString)) {
                return \Carbon\Carbon::parse($dateString)->format('d-m-Y');
            }

            return Helper::convert_date($dateString, 'Y-m-d', 'd-m-Y');
        } catch (\Throwable $e) {
            return $dateString;
        }
    }
}
