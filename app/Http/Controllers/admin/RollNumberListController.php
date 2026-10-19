<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\Master\MasterAcademicYear;
use App\Models\Master\MasterDivision;
use App\Models\Master\MasterStandard;
use App\Services\SchoolDatabaseManager;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;

class RollNumberListController extends Controller
{
    public const SESSION_KEY = 'roll_number_list_filters';
    public const FLASH_KEEP = 'roll_number_list_keep';

    public $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Roll Number List',
            'folder_path' => 'software.module.roll-number-list',
            'route' => 'roll-number-list',
            'permission_prefix' => 'roll-number-list',
        ];
    }

    public function index()
    {
        $this->authorizeView();
        $modules = $this->modules;
        $modules['permission_print'] = Helper::directCan($modules['permission_prefix'] . '-print') || Helper::directCan($modules['permission_prefix'] . '-list');
        View::share('modules', $modules);

        $standards = MasterStandard::where('status', 'active')->orderBy('id')->get(['id', 'name']);
        $divisions = MasterDivision::where('status', 'active')->orderBy('name')->pluck('name');
        $currentYearModel = MasterAcademicYear::current();
        $currentYear = $currentYearModel?->name;

        // Browser refresh / direct open → clear filters. Keep only after Show List / Promote redirect.
        if (!session()->pull(self::FLASH_KEEP)) {
            session()->forget(self::SESSION_KEY);
        }

        $filters = session(self::SESSION_KEY, []);
        $standardId = !empty($filters['standard_id']) ? (int) $filters['standard_id'] : null;
        $division = !empty($filters['division']) ? trim($filters['division']) : null;

        $students = collect();
        if ($standardId && $currentYearModel) {
            $students = $this->students($standardId, $division, (int) $currentYearModel->id);
        }

        $selectedStandard = $standardId
            ? $standards->firstWhere('id', $standardId)
            : null;

        return view($modules['folder_path'] . '.index', compact(
            'modules',
            'standards',
            'divisions',
            'students',
            'standardId',
            'division',
            'selectedStandard',
            'currentYear',
            'currentYearModel'
        ));
    }

    public function filter(Request $request)
    {
        $this->authorizeView();

        if ($request->boolean('clear')) {
            session()->forget(self::SESSION_KEY);

            return redirect()->route('roll-number-list.index');
        }

        $request->validate([
            'standard_id' => 'required|integer|exists:master_standards,id',
            'division' => 'nullable|string|max:50',
        ]);

        session()->put(self::SESSION_KEY, [
            'standard_id' => (int) $request->standard_id,
            'division' => $request->filled('division') ? trim($request->division) : null,
        ]);

        return redirect()->route('roll-number-list.index')
            ->with(self::FLASH_KEEP, true);
    }

    public function print()
    {
        $this->authorizePrint();

        $filters = session(self::SESSION_KEY, []);
        $standardId = !empty($filters['standard_id']) ? (int) $filters['standard_id'] : null;
        $division = !empty($filters['division']) ? trim($filters['division']) : null;

        if (!$standardId) {
            return redirect()->route('roll-number-list.index')
                ->withErrors('Please select Standard first.');
        }

        $currentYearModel = MasterAcademicYear::current();
        if (!$currentYearModel) {
            return redirect()->route('roll-number-list.index')
                ->withErrors('No current academic year is set.');
        }

        $standard = MasterStandard::findOrFail($standardId);
        $students = $this->students($standardId, $division, (int) $currentYearModel->id);
        $school = SchoolDatabaseManager::getActiveSchool();
        $currentYear = $currentYearModel->name;

        return view($this->modules['folder_path'] . '.print', compact(
            'students',
            'standard',
            'division',
            'school',
            'currentYear'
        ));
    }

    protected function students(int $standardId, ?string $division, int $academicYearId): Collection
    {
        // Only students belonging to the current academic year (after admission / promotion)
        $query = Admission::with('currentStandard')
            ->where('current_std_id', $standardId)
            ->where('academic_year_id', $academicYearId);

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

    protected function authorizeView(): void
    {
        if (!Helper::directCan($this->modules['permission_prefix'] . '-list')) {
            abort(403, 'User does not have the right permissions.');
        }
    }

    protected function authorizePrint(): void
    {
        if (!Helper::directCanAny([
            $this->modules['permission_prefix'] . '-print',
            $this->modules['permission_prefix'] . '-list',
        ])) {
            abort(403, 'User does not have the right permissions.');
        }
    }
}
