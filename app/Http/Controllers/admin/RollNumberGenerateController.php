<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\AdmissionClassHistory;
use App\Models\Master\MasterAcademicYear;
use App\Models\Master\MasterDivision;
use App\Models\Master\MasterStandard;
use App\Services\SchoolDatabaseManager;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class RollNumberGenerateController extends Controller
{
    public const SESSION_KEY = 'roll_number_generate_filters';
    public const FLASH_KEEP = 'roll_number_generate_keep';

    public $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Generate Roll Number',
            'folder_path' => 'software.module.roll-number-generate',
            'route' => 'roll-number-generate',
            'permission_prefix' => 'roll-number-generate',
        ];
    }

    public function index()
    {
        $this->authorizeView();
        $modules = $this->modules;
        $modules['permission_create'] = Helper::directCanAny([
            $modules['permission_prefix'] . '-create',
            $modules['permission_prefix'] . '-edit',
            'admission-edit',
            'roll-number-list-list',
        ]);
        View::share('modules', $modules);

        $standards = MasterStandard::where('status', 'active')->orderBy('id')->get(['id', 'name']);
        $divisions = MasterDivision::where('status', 'active')->orderBy('name')->pluck('name');
        $currentYearModel = MasterAcademicYear::current();
        $currentYear = $currentYearModel?->name;

        // Keep filters on form submit / redirect; clear on direct refresh without flag
        if (!session()->pull(self::FLASH_KEEP)) {
            session()->forget(self::SESSION_KEY);
        }

        $filters = session(self::SESSION_KEY, []);
        $standardId = !empty($filters['standard_id']) ? (int) $filters['standard_id'] : null;
        $division = !empty($filters['division']) ? trim($filters['division']) : null;
        $orderBy = !empty($filters['order_by']) ? trim($filters['order_by']) : '';
        $genderOrder = !empty($filters['gender_order']) && in_array($filters['gender_order'], ['male', 'female', 'both'], true) ? $filters['gender_order'] : '';
        $students = collect();
        $startFrom = 1;

        $assignedCount = 0;
        $existingMaxRoll = 0;
        $totalStudents = 0;
        $allAssigned = false;
        $pendingCounts = [];

        if ($currentYearModel) {
            $pendingCounts = Admission::where('academic_year_id', (int) $currentYearModel->id)
                ->where(function ($q) {
                    $q->whereNull('roll_no')
                      ->orWhere('roll_no', 0)
                      ->orWhereRaw("TRIM(COALESCE(roll_no, '')) = ''");
                })
                ->groupBy('current_std_id')
                ->selectRaw('current_std_id, count(*) as count')
                ->pluck('count', 'current_std_id')
                ->all();
        }

        if ($standardId && $currentYearModel) {
            $baseQuery = Admission::where('current_std_id', $standardId)
                ->where('academic_year_id', (int) $currentYearModel->id)
                ->when($division, fn($q, $d) => $q->where('division', $d));

            $totalStudents = (clone $baseQuery)->count();

            $assignedCount = (clone $baseQuery)
                ->whereNotNull('roll_no')
                ->where('roll_no', '>', 0)
                ->count();

            $existingMaxRoll = (int) ((clone $baseQuery)
                ->whereNotNull('roll_no')
                ->where('roll_no', '>', 0)
                ->max('roll_no'));

            // If all enrolled students already have roll numbers, allAssigned = true
            if ($totalStudents > 0 && $assignedCount === $totalStudents) {
                $allAssigned = true;
            } else {
                // Query ONLY pending students (students who do NOT have a roll number yet)
                $students = $this->queryPendingStudents($standardId, $division, (int) $currentYearModel->id, $orderBy, $genderOrder);
                $startFrom = ($existingMaxRoll > 0) ? ($existingMaxRoll + 1) : 1;
            }
        }

        $selectedStandard = $standardId ? $standards->firstWhere('id', $standardId) : null;

        return view($modules['folder_path'] . '.index', compact(
            'modules',
            'standards',
            'divisions',
            'students',
            'standardId',
            'division',
            'orderBy',
            'genderOrder',
            'startFrom',
            'selectedStandard',
            'currentYear',
            'currentYearModel',
            'allAssigned',
            'assignedCount',
            'existingMaxRoll',
            'totalStudents',
            'pendingCounts'
        ));
    }

    public function filter(Request $request)
    {
        $this->authorizeView();

        if ($request->boolean('clear')) {
            session()->forget(self::SESSION_KEY);
            return redirect()->route('roll-number-generate.index');
        }

        $request->validate([
            'standard_id' => 'required|integer|exists:master_standards,id',
            'division' => 'nullable|string|max:50',
            'order_by' => 'required|string',
            'gender_order' => 'nullable|string|in:male,female,both',
        ]);

        $allowedFields = ['surname', 'name', 'father_name', 'gr_no'];
        $rawFields = explode(',', (string) $request->order_by);
        $cleanFields = [];
        foreach ($rawFields as $field) {
            $f = trim(strtolower($field));
            if (in_array($f, $allowedFields, true) && !in_array($f, $cleanFields, true)) {
                $cleanFields[] = $f;
            }
        }
        $cleanOrderBy = !empty($cleanFields) ? implode(',', $cleanFields) : 'surname,name,father_name';

        session()->put(self::SESSION_KEY, [
            'standard_id'  => (int) $request->standard_id,
            'division'     => $request->filled('division') ? trim($request->division) : null,
            'order_by'     => $cleanOrderBy,
            'gender_order' => $request->filled('gender_order') ? trim($request->gender_order) : null,
        ]);

        return redirect()->route('roll-number-generate.index')
            ->with(self::FLASH_KEEP, true);
    }

    public function store(Request $request)
    {
        $this->authorizeEdit();

        $request->validate([
            'students' => 'required|array',
            'students.*.roll_no' => 'required|integer|min:1',
        ], [
            'students.required' => 'Please select at least one student to generate roll numbers.',
            'students.*.roll_no.required' => 'Roll number cannot be empty.',
            'students.*.roll_no.integer' => 'Roll number must be a valid number.',
        ]);

        $studentsData = $request->input('students', []);
        $selectedIds = [];
        $proposedRolls = [];

        foreach ($studentsData as $studentId => $row) {
            if (isset($row['selected'])) {
                $studentId = (int) $studentId;
                $rollNo = (int) $row['roll_no'];
                $selectedIds[] = $studentId;
                $proposedRolls[$studentId] = $rollNo;
            }
        }

        if (empty($selectedIds)) {
            return redirect()->back()->withInput()->withErrors('Please select at least one student to assign roll numbers.');
        }

        // Check duplicate roll numbers among selected students
        $duplicates = array_diff_assoc($proposedRolls, array_unique($proposedRolls));
        if (!empty($duplicates)) {
            $duplicateVal = reset($duplicates);
            return redirect()->back()->withInput()->withErrors("Duplicate roll number {$duplicateVal} found in your selection. Each student must have a unique roll number.");
        }

        $currentYearModel = MasterAcademicYear::current();

        // Check collision against other students in the same standard & division
        $firstAdmission = Admission::find(reset($selectedIds));
        if ($firstAdmission && $currentYearModel) {
            $otherConflict = Admission::where('current_std_id', $firstAdmission->current_std_id)
                ->where('academic_year_id', (int) $currentYearModel->id)
                ->when($firstAdmission->division, function ($q, $div) {
                    $q->where('division', $div);
                })
                ->whereNotIn('id', $selectedIds)
                ->whereIn('roll_no', $proposedRolls)
                ->first();

            if ($otherConflict) {
                return redirect()->back()->withInput()->withErrors("Roll number {$otherConflict->roll_no} is already assigned to '{$otherConflict->first_name} {$otherConflict->last_name}' in this class. Please choose a different roll number sequence.");
            }
        }
        $updatedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($proposedRolls as $studentId => $newRollNo) {
                $admission = Admission::find($studentId);
                if (!$admission) {
                    continue;
                }

                $admission->update([
                    'roll_no'    => $newRollNo,
                    'updated_by' => Auth::id(),
                ]);

                if ($currentYearModel) {
                    AdmissionClassHistory::record($admission, Auth::id(), $currentYearModel);
                }

                $updatedCount++;
            }

            DB::commit();

            session()->forget(self::SESSION_KEY);

            // Pre-filter Roll Number List so it immediately opens with the saved class
            if ($firstAdmission) {
                session()->put(RollNumberListController::SESSION_KEY, [
                    'standard_id' => (int) $firstAdmission->current_std_id,
                    'division' => $firstAdmission->division ?: null,
                ]);
                session()->put(RollNumberListController::FLASH_KEEP, true);
            }

            return redirect()->route('roll-number-list.index')
                ->with('success', "Roll numbers generated and saved successfully for {$updatedCount} student(s)!");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors('Error saving roll numbers: ' . $e->getMessage());
        }
    }

    protected function queryPendingStudents(int $standardId, ?string $division, int $academicYearId, string $orderBy, string $genderOrder): Collection
    {
        $query = Admission::with('currentStandard')
            ->where('current_std_id', $standardId)
            ->where('academic_year_id', $academicYearId)
            ->where(function ($q) {
                $q->whereNull('roll_no')
                  ->orWhere('roll_no', 0)
                  ->orWhereRaw("TRIM(COALESCE(roll_no, '')) = ''");
            });

        if ($division !== null && $division !== '') {
            $query->where('division', $division);
        }

        if ($genderOrder === 'male') {
            $query->whereRaw("LOWER(TRIM(gender)) = 'male'");
        } elseif ($genderOrder === 'female') {
            $query->whereRaw("LOWER(TRIM(gender)) = 'female'");
        }

        // Selected sort criteria (supports multiple fields in user-specified priority sequence)
        $fields = array_filter(array_map('trim', explode(',', (string) $orderBy)));
        if (empty($fields)) {
            $fields = ['surname', 'name', 'father_name'];
        }

        foreach ($fields as $field) {
            switch ($field) {
                case 'surname':
                    // In this system's database schema, first_name stores the Surname (e.g. Balas, Budhani, Dhaduk)
                    $query->orderByRaw("TRIM(COALESCE(first_name, '')) ASC");
                    break;
                case 'name':
                    // In this system's database schema, last_name stores the Student Given Name (e.g. Aarush, Anshi, Darsh)
                    $query->orderByRaw("TRIM(COALESCE(last_name, '')) ASC");
                    break;
                case 'father_name':
                    $query->orderByRaw("TRIM(COALESCE(father_name, '')) ASC");
                    break;
                case 'gr_no':
                    $query->orderByRaw("CAST(NULLIF(TRIM(gr_no), '') AS UNSIGNED) ASC")->orderBy('gr_no');
                    break;
            }
        }

        $query->orderBy('id');

        return $query->get();
    }

    protected function authorizeView(): void
    {
        if (!Helper::directCan($this->modules['permission_prefix'] . '-list')) {
            abort(403, 'User does not have the right permissions.');
        }
    }

    protected function authorizeEdit(): void
    {
        if (!Helper::directCanAny([
            $this->modules['permission_prefix'] . '-create',
            $this->modules['permission_prefix'] . '-edit',
        ])) {
            abort(403, 'User does not have the right permissions.');
        }
    }
}
