<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\CharacterCertificate;
use App\Models\Master\MasterAcademicYear;
use App\Models\Master\MasterStandard;
use App\Services\SchoolDatabaseManager;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class CharacterCertificateController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title'       => 'Character Certificate',
            'folder_path' => 'software.module.character-certificate',
            'route'       => 'character-certificate',
            'permission'  => 'character-certificate-list',
        ];
    }

    public function index(Request $request)
    {
        $this->authorizeUsOnly();

        $modules = $this->modules;
        View::share('modules', $modules);
        View::share('sidebar_active', 'character-certificate');

        $filters = [
            'q' => trim((string) $request->input('q', '')),
        ];

        $entries = CharacterCertificate::query()
            ->when($filters['q'] !== '', function ($q) use ($filters) {
                $term = '%' . $filters['q'] . '%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('gr_no', 'like', $term)
                        ->orWhere('student_name', 'like', $term)
                        ->orWhere('uid_no', 'like', $term);
                });
            })
            ->orderByDesc('id')
            ->paginate(50);

        $historyCount = CharacterCertificate::query()->count();

        return view($modules['folder_path'] . '.index', compact(
            'modules',
            'filters',
            'entries',
            'historyCount'
        ));
    }

    public function create(Request $request)
    {
        return $this->formView($request);
    }

    public function edit($id)
    {
        $entry = CharacterCertificate::findOrFail($id);

        return $this->formView(request(), $entry);
    }

    public function store(Request $request)
    {
        if ($request->filled('standard_id') && !$request->filled('student_name') && !$request->filled('gr_no')) {
            return $this->generateByStandard($request);
        }

        return $this->saveEntry($request);
    }

    public function update(Request $request, $id)
    {
        $request->merge(['id' => (int) $id]);

        return $this->saveEntry($request);
    }

    public function destroy($id)
    {
        $this->authorizeUsOnly();

        try {
            CharacterCertificate::findOrFail($id)->delete();

            return response()->json(['success' => true, 'message' => 'Deleted successfully.']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage() ?: 'Delete failed.'], 422);
        }
    }

    public function findStudent(Request $request)
    {
        $this->authorizeUsOnly();

        $gr = trim((string) $request->input('gr_no', ''));
        $admissionId = (int) $request->input('admission_id', 0);

        $query = Admission::query()->with(['currentStandard', 'academicYear']);
        if ($admissionId > 0) {
            $query->where('id', $admissionId);
        } elseif ($gr !== '') {
            $query->where('gr_no', $gr);
        } else {
            return response()->json(['success' => false, 'message' => 'GR No. is required.']);
        }

        $student = $query->first();
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student not found.']);
        }

        $stdName = (string) ($student->current_std ?? $student->currentStandard?->name ?? '');

        return response()->json([
            'success' => true,
            'data'    => [
                'admission_id'   => $student->id,
                'gr_no'          => (string) ($student->gr_no ?? ''),
                'uid_no'         => $this->resolveUid($student),
                'student_name'   => $this->boardStudentName($student),
                'gender'         => (string) ($student->gender ?? ''),
                'standard_id'    => (int) ($student->current_std_id ?? 0),
                'std'            => $stdName,
                'std_display'    => $this->formatStandardDisplay($stdName),
                'class'          => (string) ($student->division ?? ''),
                'academic_year'  => (string) ($student->academicYear?->name
                    ?? MasterAcademicYear::where('status', 'active')->orderByDesc('id')->value('name')
                    ?? ''),
            ],
        ]);
    }

    public function print($id)
    {
        $this->authorizeUsOnly();

        $entry = CharacterCertificate::findOrFail($id);
        $stdName = (string) ($entry->std ?? '');
        $isEnglish = stripos($stdName, 'english') !== false;
        $schoolTitle = $isEnglish
            ? 'ULTRA SECONDARY SCHOOL (ENG. MED.)'
            : 'ULTRA SECONDARY SCHOOL';

        $gender = strtolower(trim((string) ($entry->gender ?? '')));
        $isFemale = in_array($gender, ['female', 'f', 'girl'], true);
        $sheHe = $isFemale ? 'she' : 'he';

        return view($this->modules['folder_path'] . '.print', [
            'entry'       => $entry,
            'schoolTitle' => $schoolTitle,
            'sheHe'       => $sheHe,
            'stdDisplay'  => (string) ($entry->std_display ?: $this->formatStandardDisplay($stdName)),
            'printDate'   => (string) ($entry->print_date ?: Carbon::now()->format('d-m-Y')),
        ]);
    }

    protected function formView(Request $request, ?CharacterCertificate $entry = null)
    {
        $this->authorizeUsOnly();

        $modules = $this->modules;
        View::share('modules', $modules);
        View::share('sidebar_active', 'character-certificate');

        $isEdit = !empty($entry?->id);
        $academicYears = MasterAcademicYear::where('status', 'active')->orderByDesc('id')->pluck('name');

        if ($isEdit) {
            $students = Admission::query()
                ->with(['currentStandard'])
                ->where(function ($q) {
                    $q->whereNull('status')
                        ->orWhereRaw('LOWER(status) NOT IN (?, ?, ?)', ['cancel', 'cancelled', 'rejected']);
                })
                ->orderByRaw('CAST(gr_no AS UNSIGNED)')
                ->orderBy('gr_no')
                ->get();

            return view($modules['folder_path'] . '.form', compact(
                'modules',
                'entry',
                'students',
                'academicYears',
                'isEdit'
            ));
        }

        $standards = MasterStandard::where('status', 'active')->orderBy('id')->get();
        $filters = [
            'standard_id'   => (int) $request->input('standard_id', 0),
            'print_date'    => trim((string) $request->input('print_date', Carbon::now()->format('d-m-Y'))),
            'academic_year' => trim((string) $request->input('academic_year', '')),
        ];
        if ($filters['print_date'] === '') {
            $filters['print_date'] = Carbon::now()->format('d-m-Y');
        }
        if ($filters['academic_year'] === '') {
            $filters['academic_year'] = (string) (
                MasterAcademicYear::where('status', 'active')->orderByDesc('id')->value('name') ?? ''
            );
        }

        $students = collect();
        if ($filters['standard_id'] > 0) {
            $students = $this->queryStudents($filters['standard_id'])->get();
        }

        return view($modules['folder_path'] . '.form', compact(
            'modules',
            'entry',
            'standards',
            'filters',
            'students',
            'academicYears',
            'isEdit'
        ));
    }

    protected function generateByStandard(Request $request)
    {
        $this->authorizeUsOnly();

        $validated = $request->validate([
            'standard_id'     => 'required|integer',
            'print_date'      => 'nullable|string|max:20',
            'academic_year'   => 'nullable|string|max:50',
            'admission_ids'   => 'required|array|min:1',
            'admission_ids.*' => 'integer',
        ]);

        $standardId = (int) $validated['standard_id'];
        $selectedIds = array_values(array_unique(array_map('intval', $validated['admission_ids'])));
        $students = $this->queryStudents($standardId)
            ->whereIn('id', $selectedIds)
            ->get();
        if ($students->isEmpty()) {
            return redirect()->back()->withInput()
                ->with('error', 'Please select at least one student.');
        }

        $alreadyIds = CharacterCertificate::query()
            ->whereIn('admission_id', $students->pluck('id')->all())
            ->pluck('admission_id')
            ->unique()
            ->all();

        if (!empty($alreadyIds)) {
            $names = $students
                ->whereIn('id', $alreadyIds)
                ->map(fn ($s) => $this->boardStudentName($s))
                ->filter()
                ->values()
                ->all();

            $label = $names ? implode(', ', $names) : 'selected student(s)';

            return redirect()->back()->withInput()
                ->with('error', 'Character certificate already exists for ' . $label . '.');
        }

        $standard = MasterStandard::find($standardId);
        $printDate = trim((string) ($validated['print_date'] ?? ''));
        if ($printDate === '') {
            $printDate = Carbon::now()->format('d-m-Y');
        }
        $academicYear = trim((string) ($validated['academic_year'] ?? ''));
        if ($academicYear === '') {
            $academicYear = (string) (
                MasterAcademicYear::where('status', 'active')->orderByDesc('id')->value('name') ?? ''
            );
        }

        $created = 0;
        DB::beginTransaction();
        try {
            foreach ($students as $student) {
                $stdName = (string) ($student->current_std ?? $standard?->name ?? '');
                CharacterCertificate::create([
                    'admission_id'  => $student->id,
                    'gr_no'         => (string) ($student->gr_no ?? ''),
                    'uid_no'        => $this->resolveUid($student),
                    'student_name'  => $this->boardStudentName($student),
                    'gender'        => (string) ($student->gender ?? ''),
                    'standard_id'   => $standardId,
                    'std'           => $stdName,
                    'std_display'   => $this->formatStandardDisplay($stdName),
                    'class'         => (string) ($student->division ?? ''),
                    'academic_year' => $academicYear,
                    'print_date'    => $printDate,
                    'created_by'    => Auth::id(),
                    'updated_by'    => Auth::id(),
                ]);
                $created++;
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()->withInput()
                ->with('error', $e->getMessage() ?: 'Unable to generate certificates.');
        }

        return redirect()
            ->route('character-certificate.index')
            ->with('success', $created . ' character certificates generated.');
    }

    protected function queryStudents(int $standardId)
    {
        return Admission::query()
            ->with(['currentStandard'])
            ->where('current_std_id', $standardId)
            ->where(function ($q) {
                $q->whereNull('status')
                    ->orWhereRaw('LOWER(status) NOT IN (?, ?, ?)', ['cancel', 'cancelled', 'rejected']);
            })
            ->orderByRaw('CAST(gr_no AS UNSIGNED)')
            ->orderBy('gr_no');
    }

    protected function saveEntry(Request $request)
    {
        $this->authorizeUsOnly();

        $validated = $request->validate([
            'id'            => 'nullable|integer',
            'admission_id'  => 'nullable|integer',
            'gr_no'         => 'required|string|max:50',
            'uid_no'        => 'nullable|string|max:50',
            'student_name'  => 'required|string|max:255',
            'gender'        => 'nullable|string|max:30',
            'standard_id'   => 'nullable|integer',
            'std'           => 'nullable|string|max:100',
            'std_display'   => 'nullable|string|max:100',
            'class'         => 'nullable|string|max:50',
            'academic_year' => 'required|string|max:50',
            'print_date'    => 'nullable|string|max:20',
        ]);

        $id = (int) ($validated['id'] ?? 0);
        $printDate = trim((string) ($validated['print_date'] ?? ''));
        if ($printDate === '') {
            $printDate = Carbon::now()->format('d-m-Y');
        }

        $std = (string) ($validated['std'] ?? '');
        $stdDisplay = trim((string) ($validated['std_display'] ?? ''));
        if ($stdDisplay === '') {
            $stdDisplay = $this->formatStandardDisplay($std);
        }

        $payload = [
            'admission_id'  => !empty($validated['admission_id']) ? (int) $validated['admission_id'] : null,
            'gr_no'         => trim((string) $validated['gr_no']),
            'uid_no'        => trim((string) ($validated['uid_no'] ?? '')),
            'student_name'  => strtoupper(trim((string) $validated['student_name'])),
            'gender'        => (string) ($validated['gender'] ?? ''),
            'standard_id'   => !empty($validated['standard_id']) ? (int) $validated['standard_id'] : null,
            'std'           => $std,
            'std_display'   => $stdDisplay,
            'class'         => (string) ($validated['class'] ?? ''),
            'academic_year' => trim((string) $validated['academic_year']),
            'print_date'    => $printDate,
            'updated_by'    => Auth::id(),
        ];

        if ($id > 0) {
            $entry = CharacterCertificate::findOrFail($id);
            $entry->update($payload);
            $message = 'Character certificate updated.';
        } else {
            $admissionId = (int) ($payload['admission_id'] ?? 0);
            $grNo = (string) ($payload['gr_no'] ?? '');

            $dupQuery = CharacterCertificate::query();
            if ($admissionId > 0) {
                $dupQuery->where('admission_id', $admissionId);
            } elseif ($grNo !== '') {
                $dupQuery->where('gr_no', $grNo);
            } else {
                $dupQuery = null;
            }

            if ($dupQuery && $dupQuery->exists()) {
                return redirect()->back()->withInput()
                    ->with('error', 'Character certificate already exists for '
                        . ($payload['student_name'] ?: 'this student') . '.');
            }

            $payload['created_by'] = Auth::id();
            $entry = CharacterCertificate::create($payload);
            $message = 'Character certificate generated.';
        }

        return redirect()
            ->route('character-certificate.index')
            ->with('success', $message);
    }

    protected function formatStandardDisplay(?string $std): string
    {
        $s = trim((string) $std);
        if ($s === '') {
            return '________';
        }

        $map = [
            '1' => '1 {One}', '1st' => '1 {One}',
            '2' => '2 {Two}', '2nd' => '2 {Two}',
            '3' => '3 {Three}', '3rd' => '3 {Three}',
            '4' => '4 {Four}', '4th' => '4 {Four}',
            '5' => '5 {Five}', '5th' => '5 {Five}',
            '6' => '6 {Six}', '6th' => '6 {Six}',
            '7' => '7 {Seven}', '7th' => '7 {Seven}',
            '8' => '8 {Eight}', '8th' => '8 {Eight}',
            '9' => '9 {Nine}', '9th' => '9 {Nine}',
            '10' => '10 {Ten}', '10th' => '10 {Ten}',
            '11' => '11 {Eleven}', '11th' => '11 {Eleven}',
            '12' => '12 {Twelve}', '12th' => '12 {Twelve}',
        ];

        if (preg_match('/(\d+)/', $s, $m) && isset($map[$m[1]])) {
            return $map[$m[1]];
        }

        $lower = strtolower($s);
        if (isset($map[$lower])) {
            return $map[$lower];
        }

        return $s;
    }

    protected function resolveUid(Admission $student): string
    {
        return (string) (
            $student->udise
            ?: $student->pen_no
            ?: $student->aadhar_card_no
            ?: ''
        );
    }

    protected function boardStudentName(Admission $student): string
    {
        $first = trim((string) ($student->first_name ?? ''));
        $father = trim((string) ($student->father_name ?? ''));
        $last = trim((string) ($student->last_name ?? ''));

        if ($first !== '' && $father !== '') {
            $father = trim(preg_replace('/^' . preg_quote($first, '/') . '\s+/i', '', $father));
        }

        return strtoupper(trim(preg_replace('/\s+/', ' ', "{$first} {$father} {$last}")));
    }

    protected function authorizeUsOnly(): void
    {
        if (SchoolDatabaseManager::getActiveSchoolCode() !== 'us') {
            abort(404);
        }

        if (!Helper::directCan('character-certificate-list')) {
            abort(403, 'User does not have the right permissions.');
        }
    }
}
