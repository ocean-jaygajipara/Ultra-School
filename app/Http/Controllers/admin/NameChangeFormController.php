<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\Master\MasterAcademicYear;
use App\Models\NameChangeForm;
use App\Services\SchoolDatabaseManager;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class NameChangeFormController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title'       => 'Name Change Form',
            'folder_path' => 'software.module.name-change-form',
            'route'       => 'name-change-form',
            'permission'  => 'name-change-form-list',
        ];
    }

    public function index(Request $request)
    {
        $this->authorizeUsOnly();

        $modules = $this->modules;
        View::share('modules', $modules);
        View::share('sidebar_active', 'name-change-form');

        $filters = [
            'q' => trim((string) $request->input('q', '')),
        ];

        $entries = NameChangeForm::query()
            ->when($filters['q'] !== '', function ($q) use ($filters) {
                $term = '%' . $filters['q'] . '%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('gr_no', 'like', $term)
                        ->orWhere('student_name', 'like', $term)
                        ->orWhere('mobile', 'like', $term)
                        ->orWhere('std', 'like', $term);
                });
            })
            ->orderByDesc('id')
            ->paginate(50);

        return view($modules['folder_path'] . '.index', compact('modules', 'filters', 'entries'));
    }

    public function create()
    {
        return $this->formView();
    }

    public function edit($id)
    {
        $entry = NameChangeForm::findOrFail($id);

        return $this->formView($entry);
    }

    public function store(Request $request)
    {
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
            $entry = NameChangeForm::findOrFail($id);
            $entry->delete();

            return response()->json([
                'success' => true,
                'message' => 'Name Change Form deleted successfully.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Unable to delete.',
            ], 422);
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

        $excludeId = (int) $request->input('exclude_id', 0);
        $grNo = trim((string) ($student->gr_no ?? ''));
        $duplicate = NameChangeForm::query()
            ->when($excludeId > 0, fn ($q) => $q->where('id', '!=', $excludeId))
            ->where(function ($q) use ($student, $grNo) {
                $q->where('admission_id', $student->id);
                if ($grNo !== '') {
                    $q->orWhere('gr_no', $grNo);
                }
            })
            ->exists();

        if ($duplicate) {
            return response()->json([
                'success' => false,
                'message' => 'Name Change Form already exists for GR: ' . ($grNo !== '' ? $grNo : $student->id) . '.',
            ]);
        }

        return response()->json([
            'success' => true,
            'data'    => $this->mapStudentFormData($student),
        ]);
    }

    public function print(Request $request, $id)
    {
        $this->authorizeUsOnly();

        $entry = NameChangeForm::findOrFail($id);
        $academicYear = $entry->academic_year
            ?: MasterAcademicYear::where('status', 'active')->orderByDesc('id')->value('name')
            ?: '';

        $data = $this->mapEntryFormData($entry);

        return view($this->modules['folder_path'] . '.print', [
            'entry'         => $entry,
            'data'          => $data,
            'formYearLabel' => $this->resolveFormYearLabel($academicYear),
            'examYear'      => $this->resolveExamYear($academicYear),
            'formDate'      => $entry->formatDate('form_date') ?: Carbon::now()->format('d-m-Y'),
            'subjectMarks'  => $entry->subject_marks ?: $this->defaultSubjectMarks(),
        ]);
    }

    protected function formView(?NameChangeForm $entry = null)
    {
        $this->authorizeUsOnly();

        $modules = $this->modules;
        View::share('modules', $modules);
        View::share('sidebar_active', 'name-change-form');

        $students = Admission::query()
            ->with(['currentStandard'])
            ->where(function ($q) {
                $q->whereNull('status')
                    ->orWhereRaw('LOWER(status) NOT IN (?, ?, ?)', ['cancel', 'cancelled', 'rejected']);
            })
            ->orderByRaw('CAST(gr_no AS UNSIGNED)')
            ->orderBy('gr_no')
            ->get();

        $academicYears = MasterAcademicYear::where('status', 'active')->orderByDesc('id')->pluck('name');
        $subjectMarks = $entry?->subject_marks ?: $this->defaultSubjectMarks();
        $isEdit = !empty($entry?->id);
        $nextSrNo = $isEdit ? (string) ($entry->sr_no ?? '') : $this->nextSrNo();

        return view($modules['folder_path'] . '.form', compact(
            'modules',
            'entry',
            'students',
            'academicYears',
            'subjectMarks',
            'isEdit',
            'nextSrNo'
        ));
    }

    protected function saveEntry(Request $request)
    {
        $this->authorizeUsOnly();

        $validated = $request->validate([
            'id'                  => 'nullable|integer',
            'admission_id'        => 'nullable|integer',
            'academic_year'       => 'nullable|string|max:50',
            'sr_no'               => 'nullable|string|max:50',
            'form_student_id'     => 'nullable|string|max:50',
            'std'                 => 'nullable|string|max:100',
            'class'               => 'nullable|string|max:50',
            'roll_no'             => 'nullable|string|max:50',
            'form_date'           => 'nullable|string|max:20',
            'type_of_student'     => 'nullable|string|max:50',
            'app_no'              => 'nullable|string|max:100',
            'student_name'        => 'required|string|max:255',
            'date_of_birth'       => 'nullable|string|max:20',
            'school_joining_date' => 'nullable|string|max:20',
            'medium'              => 'nullable|string|max:50',
            'primary_school_type' => 'nullable|string|max:50',
            'father_profession'   => 'nullable|string|max:255',
            'father_income'       => 'nullable|string|max:100',
            'gr_no'               => 'required|string|max:50',
            'cast'                => 'nullable|string|max:50',
            'gender'              => 'nullable|string|max:30',
            'mother_name'         => 'nullable|string|max:255',
            'aadhar'              => 'nullable|string|max:20',
            'bpl_card_no'         => 'nullable|string|max:100',
            'email'               => 'nullable|string|max:150',
            'is_widow_child'      => 'nullable|string|max:20',
            'is_minority'         => 'nullable|string|max:20',
            'minority_type'       => 'nullable|string|max:100',
            'address'             => 'nullable|string|max:2000',
            'pincode'             => 'nullable|string|max:10',
            'mobile'              => 'nullable|string|max:20',
            'uid'                 => 'nullable|string|max:30',
            'marital_status'      => 'nullable|string|max:30',
            'subject_marks'       => 'nullable|array',
        ]);

        $id = (int) ($validated['id'] ?? 0);
        $admissionId = (int) ($validated['admission_id'] ?? 0);
        $grNo = trim((string) ($validated['gr_no'] ?? ''));

        $duplicate = NameChangeForm::query()
            ->when($id > 0, fn ($q) => $q->where('id', '!=', $id))
            ->where(function ($q) use ($admissionId, $grNo) {
                if ($admissionId > 0) {
                    $q->where('admission_id', $admissionId);
                }
                if ($grNo !== '') {
                    $q->orWhere('gr_no', $grNo);
                }
            })
            ->exists();

        if ($duplicate) {
            return redirect()->back()->withInput()
                ->with('error', 'Name Change Form already exists for GR: ' . $grNo . '.');
        }

        $marks = $validated['subject_marks'] ?? $this->defaultSubjectMarks();
        foreach ($this->defaultSubjectMarks() as $key => $default) {
            $marks[$key] = !empty($marks[$key]) ? 1 : 0;
        }

        $srNo = trim((string) ($validated['sr_no'] ?? ''));
        if ($id <= 0 && $srNo === '') {
            $srNo = $this->nextSrNo();
        }

        $payload = [
            'admission_id'        => $admissionId ?: null,
            'academic_year'       => $validated['academic_year'] ?? null,
            'sr_no'               => $srNo !== '' ? $srNo : null,
            'form_student_id'     => $validated['form_student_id'] ?? null,
            'std'                 => $validated['std'] ?? null,
            'class'               => $validated['class'] ?? null,
            'roll_no'             => $validated['roll_no'] ?? null,
            'form_date'           => $this->parseDate($validated['form_date'] ?? null),
            'type_of_student'     => strtoupper(trim((string) ($validated['type_of_student'] ?? 'REGULAR'))),
            'app_no'              => $validated['app_no'] ?? null,
            'student_name'        => strtoupper(trim((string) $validated['student_name'])),
            'date_of_birth'       => $this->parseDate($validated['date_of_birth'] ?? null),
            'school_joining_date' => $this->parseDate($validated['school_joining_date'] ?? null),
            'medium'              => strtoupper(trim((string) ($validated['medium'] ?? 'ENGLISH'))),
            'primary_school_type' => $validated['primary_school_type'] ?? null,
            'father_profession'   => strtoupper(trim((string) ($validated['father_profession'] ?? ''))),
            'father_income'       => $validated['father_income'] ?? null,
            'gr_no'               => $grNo,
            'cast'                => strtoupper(trim((string) ($validated['cast'] ?? ''))),
            'gender'              => strtoupper(trim((string) ($validated['gender'] ?? ''))),
            'mother_name'         => strtoupper(trim((string) ($validated['mother_name'] ?? ''))),
            'aadhar'              => preg_replace('/\D+/', '', (string) ($validated['aadhar'] ?? '')),
            'bpl_card_no'         => $validated['bpl_card_no'] ?? null,
            'email'               => $validated['email'] ?? 'ultra_school@yahoo.in',
            'is_widow_child'      => $validated['is_widow_child'] ?? null,
            'is_minority'         => $validated['is_minority'] ?? null,
            'minority_type'       => $validated['minority_type'] ?? null,
            'address'             => strtoupper(trim((string) ($validated['address'] ?? ''))),
            'pincode'             => preg_replace('/\D+/', '', (string) ($validated['pincode'] ?? '')),
            'mobile'              => preg_replace('/\D+/', '', (string) ($validated['mobile'] ?? '')),
            'uid'                 => preg_replace('/\D+/', '', (string) ($validated['uid'] ?? '')),
            'marital_status'      => strtoupper(trim((string) ($validated['marital_status'] ?? 'UNMARRIED'))),
            'subject_marks'       => $marks,
            'updated_by'          => Auth::id(),
        ];

        DB::beginTransaction();
        try {
            if ($id > 0) {
                $entry = NameChangeForm::findOrFail($id);
                $entry->fill($payload)->save();
                $message = 'Name Change Form updated successfully.';
            } else {
                $payload['created_by'] = Auth::id();
                $entry = NameChangeForm::create($payload);
                $message = 'Name Change Form created successfully.';
            }
            DB::commit();

            return redirect()->route('name-change-form.index')->with('success', $message);
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()->withInput()
                ->with('error', $e->getMessage() ?: 'Unable to save Name Change Form.');
        }
    }

    protected function nextSrNo(): string
    {
        $max = (int) NameChangeForm::query()
            ->selectRaw('MAX(CAST(sr_no AS UNSIGNED)) as max_sr')
            ->value('max_sr');

        return (string) ($max + 1);
    }

    protected function mapEntryFormData(NameChangeForm $entry): array
    {
        return [
            'student_id'          => $entry->form_student_id ?: $entry->admission_id,
            'std'                 => (string) ($entry->std ?? ''),
            'class'               => (string) ($entry->class ?? ''),
            'roll_no'             => (string) ($entry->roll_no ?? ''),
            'type_of_student'     => (string) ($entry->type_of_student ?: 'REGULAR'),
            'app_no'              => (string) ($entry->app_no ?? ''),
            'student_name'        => (string) ($entry->student_name ?? ''),
            'date_of_birth'       => $entry->formatDate('date_of_birth'),
            'school_joining_date' => $entry->formatDate('school_joining_date'),
            'medium'              => (string) ($entry->medium ?: 'ENGLISH'),
            'primary_school_type' => (string) ($entry->primary_school_type ?? ''),
            'father_profession'   => (string) ($entry->father_profession ?? ''),
            'father_income'       => (string) ($entry->father_income ?? ''),
            'gr_no'               => (string) ($entry->gr_no ?? ''),
            'cast'                => (string) ($entry->cast ?? ''),
            'gender'              => (string) ($entry->gender ?? ''),
            'mother_name'         => (string) ($entry->mother_name ?? ''),
            'aadhar'              => (string) ($entry->aadhar ?? ''),
            'bpl_card_no'         => (string) ($entry->bpl_card_no ?? ''),
            'email'               => (string) ($entry->email ?: 'ultra_school@yahoo.in'),
            'is_widow_child'      => (string) ($entry->is_widow_child ?? ''),
            'is_minority'         => (string) ($entry->is_minority ?? ''),
            'minority_type'       => (string) ($entry->minority_type ?? ''),
            'address'             => (string) ($entry->address ?? ''),
            'pincode'             => (string) ($entry->pincode ?? ''),
            'mobile'              => (string) ($entry->mobile ?? ''),
            'uid'                 => (string) ($entry->uid ?? ''),
            'marital_status'      => (string) ($entry->marital_status ?: 'UNMARRIED'),
            'sr_no'               => (string) ($entry->sr_no ?? ''),
        ];
    }

    protected function mapStudentFormData(Admission $student): array
    {
        $aadhar = preg_replace('/\D+/', '', (string) ($student->aadhar_card_no ?? ''));
        $mobile = preg_replace('/\D+/', '', (string) ($student->mobile_no ?? ''));
        $uid = preg_replace('/\D+/', '', (string) ($student->udise ?? $student->pen_no ?? $student->apaar_id_abc_id ?? ''));

        $category = strtoupper(trim((string) ($student->category ?? $student->cast ?? '')));
        $castLabel = match (true) {
            in_array($category, ['SC', 'ST', 'OBC', 'GEN', 'GENERAL', 'EWS'], true) => (
                $category === 'GENERAL' ? 'GEN' : ($category === 'EWS' ? 'GEN' : $category)
            ),
            default => $category,
        };

        $gender = strtoupper(trim((string) ($student->gender ?? '')));
        if (in_array($gender, ['M', 'MALE'], true)) {
            $gender = 'MALE';
        } elseif (in_array($gender, ['F', 'FEMALE'], true)) {
            $gender = 'FEMALE';
        }

        $stdName = (string) ($student->current_std ?? '');
        $isEnglish = stripos($stdName, 'english') !== false || stripos((string) ($student->stream ?? ''), 'english') !== false;
        $academicYear = optional($student->academicYear)->name
            ?: MasterAcademicYear::where('status', 'active')->orderByDesc('id')->value('name');

        return [
            'admission_id'        => $student->id,
            'academic_year'       => $academicYear,
            'form_student_id'     => (string) $student->id,
            'std'                 => $stdName,
            'class'               => (string) ($student->division ?? ''),
            'roll_no'             => (string) ($student->roll_no ?? ''),
            'form_date'           => Carbon::now()->format('d-m-Y'),
            'type_of_student'     => 'REGULAR',
            'app_no'              => '',
            'student_name'        => $this->boardStudentName($student),
            'date_of_birth'       => $this->formatDate($student->date_of_birth),
            'school_joining_date' => $this->formatDate($student->admission_date),
            'medium'              => 'ENGLISH',
            'primary_school_type' => '',
            'father_profession'   => strtoupper(trim((string) ($student->occupation ?? ''))),
            'father_income'       => '',
            'gr_no'               => (string) ($student->gr_no ?? ''),
            'cast'                => $castLabel,
            'gender'              => $gender,
            'mother_name'         => strtoupper(trim((string) ($student->mother_full_name ?? $student->mother_name ?? ''))),
            'aadhar'              => $aadhar,
            'bpl_card_no'         => '',
            'email'               => 'ultra_school@yahoo.in',
            'is_widow_child'      => '',
            'is_minority'         => '',
            'minority_type'       => '',
            'address'             => strtoupper(trim((string) ($student->permanent_address ?: $student->temporary_address ?: ''))),
            'pincode'             => '',
            'mobile'              => $mobile,
            'uid'                 => $uid,
            'marital_status'      => 'UNMARRIED',
            'subject_marks'       => $this->defaultSubjectMarks(),
        ];
    }

    protected function defaultSubjectMarks(): array
    {
        return [
            'gujarati_fl'         => 1,
            'english_sl'          => 1,
            'sanskrit_sl'         => 0,
            'hindi_sl'            => 0,
            'social_science'      => 1,
            'science_technology'  => 1,
            'maths_standard'      => 0,
            'maths_basic'         => 0,
            'computer_theory'     => 0,
            'computer_practical'  => 0,
            'yoga_theory'         => 0,
            'yoga_practical'      => 0,
        ];
    }

    protected function boardStudentName(Admission $student): string
    {
        $first = trim((string) ($student->first_name ?? ''));
        $last = trim((string) ($student->last_name ?? ''));
        $father = trim((string) ($student->father_name ?? ''));

        if ($first !== '' && $father !== '') {
            $father = trim(preg_replace('/^' . preg_quote($first, '/') . '\s+/i', '', $father));
        }

        // Format: LAST FIRST FATHER (e.g. JOSHI ANANYA HITESHBHAI)
        return strtoupper(trim(preg_replace('/\s+/', ' ', "{$last} {$first} {$father}")));
    }

    protected function formatDate($value): string
    {
        if (!$value) {
            return '';
        }

        try {
            return Carbon::parse($value)->format('d-m-Y');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }

    protected function parseDate(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        try {
            if (preg_match('/^(\d{2})[\/\-](\d{2})[\/\-](\d{4})$/', $value, $m)) {
                return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
            }

            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function resolveFormYearLabel(?string $academicYear): string
    {
        $academicYear = trim((string) $academicYear);
        if (preg_match('/(\d{4})\s*[-–\/]\s*(\d{2,4})/', $academicYear, $m)) {
            $start = $m[1];
            $end = strlen($m[2]) === 2 ? substr($m[1], 0, 2) . $m[2] : $m[2];

            return $start . '-' . $end;
        }

        $y = (int) date('Y');
        $m = (int) date('n');

        return $m >= 6 ? ($y . '-' . ($y + 1)) : (($y - 1) . '-' . $y);
    }

    protected function resolveExamYear(?string $academicYear): string
    {
        $label = $this->resolveFormYearLabel($academicYear);
        if (preg_match('/-(\d{4})$/', $label, $m)) {
            return $m[1];
        }

        return (string) ((int) date('Y') + (date('n') >= 6 ? 1 : 0));
    }

    protected function authorizeUsOnly(): void
    {
        if (SchoolDatabaseManager::getActiveSchoolCode() !== 'us') {
            abort(404);
        }

        if (!Helper::directCan('name-change-form-list')) {
            abort(403, 'User does not have the right permissions.');
        }
    }
}
