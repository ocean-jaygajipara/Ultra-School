<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\LeavingCertificateEntry;
use App\Models\Master\MasterAcademicYear;
use App\Models\Master\MasterDivision;
use App\Models\Master\MasterStandard;
use App\Services\SchoolDatabaseManager;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class LeavingCertificateEntryController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title'       => 'Leaving Certi. Entry',
            'folder_path' => 'software.module.leaving-certificate-entry',
            'route'       => 'leaving-certificate-entry',
            'permission'  => 'leaving-certificate-entry-list',
        ];
    }

    public function index(Request $request)
    {
        $this->authorizeList();
        $modules = $this->modules;
        View::share('modules', $modules);
        View::share('sidebar_active', 'leaving-certificate-entry');

        $academicYears = MasterAcademicYear::where('status', 'active')->orderByDesc('id')->pluck('name');
        $filters = [
            'academic_year' => trim((string) $request->input('academic_year', '')),
            'q'             => trim((string) $request->input('q', '')),
        ];

        $entries = LeavingCertificateEntry::query()
            ->when($filters['academic_year'] !== '', fn ($q) => $q->where('academic_year', $filters['academic_year']))
            ->when($filters['q'] !== '', function ($q) use ($filters) {
                $term = '%' . $filters['q'] . '%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('gr_no', 'like', $term)
                        ->orWhere('student_name', 'like', $term)
                        ->orWhere('leaving_certi_no', 'like', $term)
                        ->orWhere('leave_reason', 'like', $term);
                });
            })
            ->orderByDesc('leave_date')
            ->orderByDesc('id')
            ->paginate(50);

        return view($modules['folder_path'] . '.index', compact('modules', 'academicYears', 'filters', 'entries'));
    }

    public function create()
    {
        return $this->formView();
    }

    public function edit($id)
    {
        $entry = LeavingCertificateEntry::findOrFail($id);

        return $this->formView($entry);
    }

    public function update(Request $request, $id)
    {
        $request->merge(['id' => (int) $id]);

        return $this->store($request);
    }

    public function destroy(Request $request, $id)
    {
        $this->authorizeList();

        try {
            $entry = LeavingCertificateEntry::findOrFail($id);
            $entry->delete();

            return response()->json([
                'success' => true,
                'message' => 'Leaving certificate deleted successfully.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Unable to delete leaving certificate.',
            ], 422);
        }
    }

    protected function formView(?LeavingCertificateEntry $entry = null)
    {
        $this->authorizeList();
        $modules = $this->modules;
        $isEdit = !empty($entry?->id);
        View::share('modules', $modules);
        View::share('sidebar_active', 'leaving-certificate-entry');

        $standards = MasterStandard::where('status', 'active')->orderBy('id')->get(['id', 'name']);
        $divisions = MasterDivision::where('status', 'active')->orderBy('name')->pluck('name');
        $academicYears = MasterAcademicYear::where('status', 'active')->orderByDesc('id')->pluck('name');
        $defaultAcademicYear = MasterAcademicYear::current()?->name
            ?? MasterAcademicYear::forDate(now()->format('Y-m-d'))?->name
            ?? $academicYears->first();
        $school = SchoolDatabaseManager::getActiveSchool();
        $students = $this->getStudentOptions();

        $leaveReasons = [
            'TO GO ELSEWHERE',
            'CHANGE OF RESIDENCE',
            'PARENTS REQUEST',
            'COMPLETED STUDIES',
            'OTHER',
        ];
        $behaviorOptions = ['Good', 'Satisfactory', 'Average', 'Poor'];
        $progressOptions = ['Good', 'Satisfactory', 'Average', 'Poor'];

        return view($modules['folder_path'] . '.form', compact(
            'modules',
            'standards',
            'divisions',
            'academicYears',
            'defaultAcademicYear',
            'school',
            'students',
            'leaveReasons',
            'behaviorOptions',
            'progressOptions',
            'entry',
            'isEdit'
        ));
    }

    public function findStudent(Request $request)
    {
        $this->authorizeList();
        $admissionId = (int) $request->input('admission_id', 0);
        $gr = trim((string) $request->input('gr_no', ''));

        $studentQuery = Admission::with(['currentStandard', 'admissionStandard', 'academicYear']);
        if ($admissionId > 0) {
            $student = $studentQuery->where('id', $admissionId)->first();
        } elseif ($gr !== '') {
            $student = $studentQuery->where('gr_no', $gr)->first();
        } else {
            return response()->json(['success' => false, 'message' => 'Select student or enter GR No.']);
        }

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student not found.']);
        }

        $excludeId = (int) $request->input('exclude_id', 0);
        $existing = LeavingCertificateEntry::query()
            ->when($excludeId > 0, fn ($q) => $q->where('id', '!=', $excludeId))
            ->where(function ($q) use ($student) {
                $q->where('admission_id', $student->id);
                if (!empty($student->gr_no)) {
                    $q->orWhere('gr_no', $student->gr_no);
                }
            })
            ->latest('id')
            ->first();

        if ($existing) {
            $name = strtoupper(trim(preg_replace(
                '/\s+/',
                ' ',
                ($student->first_name ?? '') . ' ' . ($student->last_name ?? '') . ' ' . ($student->father_name ?? '')
            )));

            return response()->json([
                'success' => false,
                'message' => 'Leaving Certificate Entry already exists for '
                    . ($name ?: ('GR: ' . $student->gr_no))
                    . '. Duplicate entry is not allowed.',
                'existing_id' => $existing->id,
            ]);
        }

        // Prefill master fields only; do not attach old entry id on create.
        $payload = $this->studentPayload($student, null);
        $payload['id'] = null;

        return response()->json([
            'success' => true,
            'data'    => $payload,
        ]);
    }

    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    protected function getStudentOptions()
    {
        return Admission::query()
            ->with('currentStandard')
            ->where(function ($q) {
                $q->whereNull('status')->orWhereNotIn('status', ['Cancel', 'cancel', 'rejected']);
            })
            ->orderByRaw('gr_no IS NULL')
            ->orderByRaw('CAST(gr_no AS UNSIGNED)')
            ->orderBy('gr_no')
            ->orderBy('first_name')
            ->get()
            ->map(function (Admission $admission) {
                $name = strtoupper(trim(preg_replace(
                    '/\s+/',
                    ' ',
                    ($admission->first_name ?? '') . ' ' . ($admission->last_name ?? '') . ' ' . ($admission->father_name ?? '')
                )));
                $gr = $admission->gr_no ?: '-';
                $stdName = $admission->currentStandard?->name ?: 'Other';

                return [
                    'id'            => $admission->id,
                    'gr_no'         => $admission->gr_no,
                    'standard_id'   => $admission->current_std_id,
                    'standard_name' => $stdName,
                    'label'         => 'GR: ' . $gr . ' — ' . $name,
                ];
            });
    }

    public function findEntry(Request $request)
    {
        $this->authorizeList();
        $certiNo = trim((string) $request->input('leaving_certi_no', ''));
        $gr = trim((string) $request->input('gr_no', ''));

        $query = LeavingCertificateEntry::query();
        if ($certiNo !== '') {
            $query->where('leaving_certi_no', $certiNo);
        } elseif ($gr !== '') {
            $query->where('gr_no', $gr);
        } else {
            return response()->json(['success' => false, 'message' => 'Enter Leaving Certi No. or GR No.']);
        }

        $entry = $query->latest('id')->first();
        if (!$entry) {
            return response()->json(['success' => false, 'message' => 'Leaving certificate entry not found.']);
        }

        return response()->json([
            'success' => true,
            'data'    => $this->entryPayload($entry),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeList();

        $validated = $request->validate([
            'id'                 => 'nullable|integer',
            'mode'               => 'nullable|in:single,all_class',
            'academic_year'      => 'nullable|string|max:50',
            'admission_id'        => 'nullable|integer',
            'uid_no'             => 'nullable|string|max:100',
            'gr_no'              => 'required|string|max:50',
            'student_name'       => 'nullable|string|max:255',
            'caste'              => 'nullable|string|max:100',
            'category'           => 'nullable|string|max:100',
            'birthdate'          => 'nullable|string|max:20',
            'birthplace'         => 'nullable|string|max:150',
            'last_school'        => 'nullable|string|max:255',
            'admission_date'      => 'nullable|string|max:20',
            'admission_std'       => 'nullable|string|max:50',
            'last_std'           => 'nullable|string|max:50',
            'last_division'      => 'nullable|string|max:20',
            'leave_date'         => 'nullable|string|max:20',
            'studied_std'        => 'nullable|string|max:50',
            'studied_from'       => 'nullable|string|max:100',
            'leave_reason'       => 'nullable|string|max:150',
            'behavior'           => 'nullable|string|max:50',
            'progress'           => 'nullable|string|max:50',
            'attendance'         => 'nullable|string|max:50',
            'attendance_from'    => 'nullable|string|max:100',
            'leaving_certi_no'   => 'nullable|string|max:50',
            'certi_date'         => 'nullable|string|max:20',
            'note'               => 'nullable|string|max:2000',
            'fee'                => 'nullable|numeric|min:0',
            'photo'              => 'nullable|string|max:500',
        ]);

        $id = (int) ($validated['id'] ?? 0);
        $admissionId = (int) ($validated['admission_id'] ?? 0);
        $grNo = trim((string) ($validated['gr_no'] ?? ''));

        $duplicateQuery = LeavingCertificateEntry::query()
            ->when($id > 0, fn ($q) => $q->where('id', '!=', $id))
            ->where(function ($q) use ($admissionId, $grNo) {
                if ($admissionId > 0) {
                    $q->where('admission_id', $admissionId);
                }
                if ($grNo !== '') {
                    $q->orWhere('gr_no', $grNo);
                }
            });

        if ($duplicateQuery->exists()) {
            $studentLabel = trim((string) ($validated['student_name'] ?? '')) ?: ('GR: ' . $grNo);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Leaving Certificate Entry already exists for ' . $studentLabel . '. Duplicate entry is not allowed.');
        }

        $payload = [
            'mode'             => $validated['mode'] ?? 'single',
            'academic_year'    => $validated['academic_year'] ?? null,
            'admission_id'      => $validated['admission_id'] ?? null,
            'uid_no'           => $validated['uid_no'] ?? null,
            'gr_no'            => $validated['gr_no'],
            'student_name'     => strtoupper(trim((string) ($validated['student_name'] ?? ''))),
            'caste'            => strtoupper(trim((string) ($validated['caste'] ?? ''))),
            'category'         => $validated['category'] ?? null,
            'birthdate'        => $this->parseDate($validated['birthdate'] ?? null),
            'birthplace'       => strtoupper(trim((string) ($validated['birthplace'] ?? ''))),
            'last_school'      => strtoupper(trim((string) ($validated['last_school'] ?? ''))),
            'admission_date'    => $this->parseDate($validated['admission_date'] ?? null),
            'admission_std'     => $validated['admission_std'] ?? null,
            'last_std'         => $validated['last_std'] ?? null,
            'last_division'    => $validated['last_division'] ?? null,
            'leave_date'       => $this->parseDate($validated['leave_date'] ?? null),
            'studied_std'      => $validated['studied_std'] ?? null,
            'studied_from'     => $validated['studied_from'] ?? null,
            'leave_reason'     => $validated['leave_reason'] ?? null,
            'behavior'         => $validated['behavior'] ?? null,
            'progress'         => $validated['progress'] ?? null,
            'attendance'       => $validated['attendance'] ?? null,
            'attendance_from'  => $validated['attendance_from'] ?? null,
            'leaving_certi_no' => $validated['leaving_certi_no'] ?? null,
            'certi_date'       => $this->parseDate($validated['certi_date'] ?? null),
            'note'             => $validated['note'] ?? null,
            'fee'              => $validated['fee'] ?? 0,
            'photo'            => $validated['photo'] ?? null,
            'updated_by'       => Auth::id(),
        ];

        DB::beginTransaction();
        try {
            if ($id > 0) {
                $entry = LeavingCertificateEntry::findOrFail($id);
                $entry->fill($payload);
                $entry->save();
            } else {
                $payload['created_by'] = Auth::id();
                $entry = LeavingCertificateEntry::create($payload);
            }

            // Keep admission LC fields in sync
            $admission = null;
            if (!empty($payload['admission_id'])) {
                $admission = Admission::find($payload['admission_id']);
            }
            if (!$admission && !empty($payload['gr_no'])) {
                $admission = Admission::where('gr_no', $payload['gr_no'])->first();
            }
            if ($admission) {
                $admission->lc_no = $payload['leaving_certi_no'];
                $admission->lc_date = $payload['certi_date'];
                $admission->attendance = $payload['attendance'];
                if (!empty($payload['last_school'])) {
                    $admission->last_school_name = $payload['last_school'];
                }
                $admission->updated_by = Auth::id();
                $admission->save();
                if (!$entry->admission_id) {
                    $entry->admission_id = $admission->id;
                    $entry->save();
                }
            }

            DB::commit();

            $isUpdate = $id > 0;

            return redirect()
                ->route('leaving-certificate-entry.index')
                ->with(
                    'success',
                    $isUpdate
                        ? 'Leaving certificate updated successfully.'
                        : 'Leaving certificate created successfully.'
                );
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage() ?: 'Unable to save leaving certificate.');
        }
    }

    public function printList(Request $request)
    {
        $this->authorizeList();
        $academicYear = trim((string) $request->input('academic_year', ''));
        $entries = LeavingCertificateEntry::query()
            ->when($academicYear !== '', fn ($q) => $q->where('academic_year', $academicYear))
            ->orderByRaw('CAST(gr_no AS UNSIGNED)')
            ->orderBy('gr_no')
            ->get();

        $school = SchoolDatabaseManager::getActiveSchool();
        $schoolName = strtoupper($school['name'] ?? 'Ultra School');
        if (SchoolDatabaseManager::getActiveSchoolCode() === 'ues') {
            $schoolName .= '-KESHOD';
        }

        return view($this->modules['folder_path'] . '.print-list', compact('entries', 'school', 'schoolName', 'academicYear'));
    }

    public function printView(Request $request, $id = null)
    {
        $this->authorizeList();
        $entryId = $id ?: $request->integer('id');
        abort_unless($entryId, 404);

        $entry = LeavingCertificateEntry::with('admission')->findOrFail($entryId);
        $school = SchoolDatabaseManager::getActiveSchool();
        $schoolName = strtoupper($school['name'] ?? 'Ultra School');
        if (SchoolDatabaseManager::getActiveSchoolCode() === 'ues') {
            $schoolName .= '-KESHOD';
        }

        return view($this->modules['folder_path'] . '.print', compact('entry', 'school', 'schoolName'));
    }

    protected function studentPayload(Admission $student, ?LeavingCertificateEntry $existing = null): array
    {
        $name = strtoupper(trim(preg_replace(
            '/\s+/',
            ' ',
            ($student->first_name ?? '') . ' ' . ($student->last_name ?? '') . ' ' . ($student->father_name ?? '')
        )));

        $defaultYear = MasterAcademicYear::current()?->name
            ?? MasterAcademicYear::forDate(now()->format('Y-m-d'))?->name
            ?? '';

        $base = [
            'id'               => $existing?->id,
            'admission_id'     => $student->id,
            'academic_year'    => $existing?->academic_year
                ?: ($student->academicYear?->name ?? $defaultYear),
            'uid_no'           => $existing?->uid_no ?: ($student->udise ?: $student->pen_no ?: $student->aadhar_card_no),
            'gr_no'            => $student->gr_no,
            'student_name'     => $existing?->student_name ?: $name,
            'caste'            => $existing?->caste ?: strtoupper((string) $student->cast),
            'category'         => $existing?->category ?: ($student->category ?: ''),
            'birthdate'        => $existing?->formatDate('birthdate') ?: $this->formatDateValue($student->date_of_birth),
            'birthplace'       => $existing?->birthplace ?: strtoupper((string) $student->birth_place),
            'last_school'      => $existing?->last_school ?: strtoupper((string) $student->last_school_name),
            'admission_date'    => $existing?->formatDate('admission_date') ?: $this->formatDateValue($student->admission_date),
            'admission_std'     => $existing?->admission_std ?: ($student->admissionStandard?->name ?? ''),
            'last_std'         => $existing?->last_std ?: ($student->currentStandard?->name ?? ''),
            'last_division'    => $existing?->last_division ?: ($student->division ?? ''),
            'leave_date'       => $existing?->formatDate('leave_date') ?: '',
            'studied_std'      => $existing?->studied_std ?: ($student->currentStandard?->name ?? ''),
            'studied_from'     => $existing?->studied_from ?: '',
            'leave_reason'     => $existing?->leave_reason ?: 'TO GO ELSEWHERE',
            'behavior'         => $existing?->behavior ?: 'Good',
            'progress'         => $existing?->progress ?: 'Good',
            'attendance'       => $existing?->attendance ?: ($student->attendance ?? ''),
            'attendance_from'  => $existing?->attendance_from ?: '',
            'leaving_certi_no' => $existing?->leaving_certi_no ?: ($student->lc_no ?? ''),
            'certi_date'       => $existing?->formatDate('certi_date') ?: $this->formatDateValue($student->lc_date),
            'note'             => $existing?->note ?: '',
            'fee'              => $existing?->fee !== null ? (string) $existing->fee : '0',
            'photo'            => $existing?->photo ?: ($student->profile_pic ?? ''),
            'photo_url'        => $this->photoUrl($existing?->photo ?: ($student->profile_pic ?? '')),
        ];

        return $base;
    }

    protected function entryPayload(LeavingCertificateEntry $entry): array
    {
        return [
            'id'               => $entry->id,
            'admission_id'     => $entry->admission_id,
            'mode'             => $entry->mode ?: 'single',
            'academic_year'    => $entry->academic_year,
            'uid_no'           => $entry->uid_no,
            'gr_no'            => $entry->gr_no,
            'student_name'     => $entry->student_name,
            'caste'            => $entry->caste,
            'category'         => $entry->category,
            'birthdate'        => $entry->formatDate('birthdate'),
            'birthplace'       => $entry->birthplace,
            'last_school'      => $entry->last_school,
            'admission_date'    => $entry->formatDate('admission_date'),
            'admission_std'     => $entry->admission_std,
            'last_std'         => $entry->last_std,
            'last_division'    => $entry->last_division,
            'leave_date'       => $entry->formatDate('leave_date'),
            'studied_std'      => $entry->studied_std,
            'studied_from'     => $entry->studied_from,
            'leave_reason'     => $entry->leave_reason,
            'behavior'         => $entry->behavior,
            'progress'         => $entry->progress,
            'attendance'       => $entry->attendance,
            'attendance_from'  => $entry->attendance_from,
            'leaving_certi_no' => $entry->leaving_certi_no,
            'certi_date'       => $entry->formatDate('certi_date'),
            'note'             => $entry->note,
            'fee'              => $entry->fee !== null ? (string) $entry->fee : '0',
            'photo'            => $entry->photo,
            'photo_url'        => $this->photoUrl($entry->photo),
        ];
    }

    protected function photoUrl(?string $path): string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return '';
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset($path);
    }

    protected function parseDate(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $value, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function formatDateValue($value): string
    {
        if (!$value) {
            return '';
        }
        try {
            return Carbon::parse($value)->format('d-m-Y');
        } catch (\Throwable $e) {
            return '';
        }
    }

    protected function authorizeList(): void
    {
        if (!Helper::directCan('leaving-certificate-entry-list')) {
            abort(403, 'User does not have the right permissions.');
        }
    }
}
