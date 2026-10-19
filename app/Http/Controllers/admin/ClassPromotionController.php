<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\AdmissionClassHistory;
use App\Models\Master\MasterAcademicYear;
use App\Models\Master\MasterDivision;
use App\Models\Master\MasterStandard;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class ClassPromotionController extends Controller
{
    public const SESSION_KEY = 'class_promotion_filters';

    public $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Class Promotion',
            'folder_path' => 'software.module.class-promotion',
            'route' => 'class-promotion',
            'permission_prefix' => 'class-promotion',
        ];
    }

    public function index()
    {
        $this->authorizeView();
        $modules = $this->modules;
        $modules['permission_create'] = Helper::directCanAny([
            $modules['permission_prefix'] . '-create',
            $modules['permission_prefix'] . '-edit',
        ]);
        View::share('modules', $modules);

        $standards = MasterStandard::where('status', 'active')->orderBy('id')->get(['id', 'name']);
        $divisions = MasterDivision::where('status', 'active')->orderBy('name')->pluck('name');
        $currentYear = MasterAcademicYear::current();

        $filters = session(self::SESSION_KEY, []);
        $fromStandardId = !empty($filters['from_standard_id']) ? (int) $filters['from_standard_id'] : null;
        $fromDivision = !empty($filters['from_division']) ? trim($filters['from_division']) : null;
        $toStandardId = !empty($filters['to_standard_id']) ? (int) $filters['to_standard_id'] : null;
        $toDivision = array_key_exists('to_division', $filters) ? ($filters['to_division'] ?: null) : null;

        $students = collect();
        $fromStandard = null;
        $toStandard = null;
        $suggestedToStandardId = null;

        if ($fromStandardId) {
            $students = $this->students($fromStandardId, $fromDivision, $currentYear?->id);
            $fromStandard = $standards->firstWhere('id', $fromStandardId);
            $suggestedToStandardId = $this->nextStandardId($standards, $fromStandardId);
            if (!$toStandardId && $suggestedToStandardId) {
                $toStandardId = $suggestedToStandardId;
            }
        }

        if ($toStandardId) {
            $toStandard = $standards->firstWhere('id', $toStandardId);
        }

        return view($modules['folder_path'] . '.index', compact(
            'modules',
            'standards',
            'divisions',
            'students',
            'fromStandardId',
            'fromDivision',
            'toStandardId',
            'toDivision',
            'fromStandard',
            'toStandard',
            'suggestedToStandardId',
            'currentYear'
        ));
    }

    public function filter(Request $request)
    {
        $this->authorizeView();

        if ($request->boolean('clear')) {
            session()->forget(self::SESSION_KEY);

            return redirect()->route('class-promotion.index');
        }

        $request->validate([
            'from_standard_id' => 'required|integer|exists:master_standards,id',
            'from_division' => 'nullable|string|max:50',
            'to_standard_id' => 'nullable|integer|exists:master_standards,id',
            'to_division' => 'nullable|string|max:50',
        ]);

        session()->put(self::SESSION_KEY, [
            'from_standard_id' => (int) $request->from_standard_id,
            'from_division' => $request->filled('from_division') ? trim($request->from_division) : null,
            'to_standard_id' => $request->filled('to_standard_id') ? (int) $request->to_standard_id : null,
            'to_division' => $request->filled('to_division') ? trim($request->to_division) : null,
        ]);

        return redirect()->route('class-promotion.index');
    }

    public function promote(Request $request)
    {
        $this->authorizePromote();

        $currentYear = MasterAcademicYear::current();
        if (!$currentYear) {
            return redirect()->route('class-promotion.index')
                ->withErrors('No current academic year is set. Please set it in Academic Year Master first.');
        }

        $validated = $request->validate([
            'from_standard_id' => 'required|integer|exists:master_standards,id',
            'from_division' => 'nullable|string|max:50',
            'to_standard_id' => 'required|integer|exists:master_standards,id',
            'to_division' => 'nullable|string|max:50',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'integer|exists:admission,id',
            'roll_nos' => 'required|array',
            'roll_nos.*' => 'nullable|integer|min:1',
        ], [
            'student_ids.required' => 'Please select at least one student to promote.',
            'to_standard_id.required' => 'Please select To Standard.',
            'roll_nos.required' => 'Please enter New Roll No for selected students.',
        ]);

        $fromStandardId = (int) $validated['from_standard_id'];
        $toStandardId = (int) $validated['to_standard_id'];
        $fromDivision = !empty($validated['from_division']) ? trim($validated['from_division']) : null;
        $toDivision = array_key_exists('to_division', $validated) && $validated['to_division'] !== null && $validated['to_division'] !== ''
            ? trim($validated['to_division'])
            : null;
        $rollNos = $validated['roll_nos'] ?? [];

        if ($fromStandardId === $toStandardId && $fromDivision === $toDivision) {
            return redirect()->route('class-promotion.index')
                ->withErrors('From and To class are the same. Please choose a different Standard or Division.');
        }

        $query = Admission::whereIn('id', $validated['student_ids'])
            ->where('current_std_id', $fromStandardId);

        if ($fromDivision) {
            $query->where('division', $fromDivision);
        }

        $students = $query->get();
        if ($students->isEmpty()) {
            return redirect()->route('class-promotion.index')
                ->withErrors('No matching students found to promote.');
        }

        $assignedRolls = [];
        foreach ($students as $student) {
            $roll = $rollNos[$student->id] ?? null;
            if ($roll === null || $roll === '' || (int) $roll < 1) {
                return redirect()->route('class-promotion.index')
                    ->withErrors('Please enter New Roll No for all selected students.');
            }
            $roll = (int) $roll;
            if (in_array($roll, $assignedRolls, true)) {
                return redirect()->route('class-promotion.index')
                    ->withErrors('Duplicate New Roll No: ' . $roll . '. Each roll must be unique.');
            }
            $assignedRolls[] = $roll;
        }

        // Check overlap with students already in target class (not in this promote batch)
        $targetDivisionSamples = $students->map(function ($student) use ($toDivision) {
            return $toDivision !== null ? $toDivision : $student->division;
        })->unique()->filter()->values();

        foreach ($targetDivisionSamples as $divCheck) {
            $existing = Admission::where('current_std_id', $toStandardId)
                ->where('division', $divCheck)
                ->where('academic_year_id', $currentYear->id)
                ->whereNotIn('id', $students->pluck('id'))
                ->whereIn('roll_no', $assignedRolls)
                ->pluck('roll_no')
                ->all();

            if (!empty($existing)) {
                return redirect()->route('class-promotion.index')
                    ->withErrors('Roll No already used in target class: ' . implode(', ', $existing));
            }
        }

        $userId = Auth::id();
        $promoted = 0;

        DB::transaction(function () use ($students, $toStandardId, $toDivision, $rollNos, $userId, $currentYear, &$promoted) {
            foreach ($students as $student) {
                $newDivision = $toDivision !== null ? $toDivision : $student->division;
                $newRoll = (int) $rollNos[$student->id];

                // Freeze from-year class (from Academic Year Master) before moving to current year
                AdmissionClassHistory::freezeBeforePromotion($student, $currentYear, $userId);

                $student->update([
                    'current_std_id' => $toStandardId,
                    'division' => $newDivision,
                    'roll_no' => $newRoll,
                    'academic_year_id' => $currentYear->id,
                    'updated_by' => $userId,
                ]);

                AdmissionClassHistory::record($student->fresh(), $userId, $currentYear);
                $promoted++;
            }
        });

        session()->forget(self::SESSION_KEY);
        session()->put(\App\Http\Controllers\admin\RollNumberListController::SESSION_KEY, [
            'standard_id' => $toStandardId,
            'division' => $toDivision,
        ]);

        $toName = MasterStandard::find($toStandardId)?->name ?? 'next class';

        return redirect()->route('roll-number-list.index')
            ->with(\App\Http\Controllers\admin\RollNumberListController::FLASH_KEEP, true)
            ->with('success', $promoted . ' student(s) promoted to ' . $toName . ' for ' . $currentYear->name . ' with new roll numbers.');
    }

    protected function students(int $standardId, ?string $division, ?int $currentYearId = null): Collection
    {
        // Load students still on previous year (not yet promoted into current year)
        $query = Admission::with('currentStandard')
            ->where('current_std_id', $standardId);

        if ($currentYearId) {
            $query->where(function ($q) use ($currentYearId) {
                $q->whereNull('academic_year_id')
                    ->orWhere('academic_year_id', '!=', $currentYearId);
            });
        }

        if ($division !== null && $division !== '') {
            $query->where('division', $division);
        }

        return $query
            ->orderByRaw('CASE WHEN roll_no IS NULL OR roll_no = 0 THEN 1 ELSE 0 END')
            ->orderBy('roll_no')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    protected function nextStandardId(Collection $standards, int $fromStandardId): ?int
    {
        $ids = $standards->pluck('id')->values();
        $index = $ids->search($fromStandardId);
        if ($index === false) {
            return null;
        }

        return $ids->get($index + 1);
    }

    protected function authorizeView(): void
    {
        if (!Helper::directCan($this->modules['permission_prefix'] . '-list')) {
            abort(403, 'User does not have the right permissions.');
        }
    }

    protected function authorizePromote(): void
    {
        if (!Helper::directCanAny([
            $this->modules['permission_prefix'] . '-create',
            $this->modules['permission_prefix'] . '-edit',
        ])) {
            abort(403, 'User does not have permission to promote students.');
        }
    }
}
