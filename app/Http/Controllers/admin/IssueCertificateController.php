<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\IssueCertificateHistory;
use App\Models\IssueCertificateLetterRecommendation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class IssueCertificateController extends Controller
{
    public $modules = [];

    private const TYPE_BONAFIDE = 'bonafide';
    private const TYPE_TC = 'tc';
    private const TYPE_ENGLISH_MEDIUM = 'english-medium';
    private const TYPE_LETTER_RECOMMENDATION = 'letter-recommendation';

    public function __construct()
    {
        $this->modules = [
            'folder_path' => 'software.module.issue-certificate',
        ];
    }

    public function bonafideIndex(Request $request)
    {
        if (!Helper::directCan('issue-certificate-bonafide-list')) {
            abort(403, 'User does not have the right permissions.');
        }

        return $this->certificateIndex($request, 'bonafide');
    }

    public function tcIndex(Request $request)
    {
        if (!Helper::directCan('issue-certificate-tc-list')) {
            abort(403, 'User does not have the right permissions.');
        }

        return $this->certificateIndex($request, 'tc');
    }

    public function englishMediumIndex(Request $request)
    {
        if (!Helper::directCan('issue-certificate-english-medium-list')) {
            abort(403, 'User does not have the right permissions.');
        }

        return $this->certificateIndex($request, self::TYPE_ENGLISH_MEDIUM);
    }

    public function letterRecommendationIndex(Request $request)
    {
        if (!Helper::directCan('issue-certificate-letter-recommendation-list')) {
            abort(403, 'User does not have the right permissions.');
        }

        $modules = [
            'title' => 'Letter of Recommendation',
            'folder_path' => 'software.module.issue-certificate',
            'route' => 'issue-certificate.letter-recommendation',
            'certificate_type' => self::TYPE_LETTER_RECOMMENDATION,
            'certificate_route' => 'letter-of-recommendation',
        ];

        View::share('modules', $modules);

        $students = $this->getStudentOptions();
        $defaults = $this->getCertificateDefaults();
        $histories = $this->getHistoriesLetterRecommendation();
        
        $faculties = \App\Models\User::where('status', 'active')
            ->whereHas('roles', function ($q) {
                $q->where('name', 'Faculty');
            })
            ->orderBy('name', 'asc')
            ->get();

        return view('software.module.issue-certificate.letter-recommendation.index', compact('modules', 'students', 'defaults', 'histories', 'faculties'));
    }

    public function storeHistory(Request $request)
    {
        $validated = $request->validate([
            'certificate_type' => 'required|in:bonafide,tc,english-medium,letter-recommendation',
            'student_id' => 'required|exists:admission,id',
            'issue_date' => 'nullable|string',
            'academic_year' => 'nullable|string',
            'recommender_type' => 'nullable|string|in:principal,faculty',
            'faculty_id' => 'nullable|exists:users,id',
            'recommender_name' => 'nullable|string|max:255',
            'recommender_designation' => 'nullable|string|max:255',
        ]);

        $permission = match ($validated['certificate_type']) {
            self::TYPE_TC => 'issue-certificate-tc-list',
            self::TYPE_ENGLISH_MEDIUM => 'issue-certificate-english-medium-list',
            self::TYPE_LETTER_RECOMMENDATION => 'issue-certificate-letter-recommendation-list',
            default => 'issue-certificate-bonafide-list',
        };

        if (!Helper::directCan($permission)) {
            return response()->json([
                'status' => false,
                'message' => 'User does not have the right permissions.',
            ], 403);
        }

        $admission = Admission::findOrFail($validated['student_id']);
        $studentName = trim(
            ($admission->first_name ?? '') . ' ' .
            ($admission->last_name ?? '') . ' ' .
            ($admission->father_name ?? '')
        );

        if ($validated['certificate_type'] === self::TYPE_LETTER_RECOMMENDATION) {
            $history = IssueCertificateLetterRecommendation::create([
                'admission_id' => $admission->id,
                'student_name' => $studentName,
                'recommender_type' => $validated['recommender_type'] ?? 'principal',
                'faculty_id' => $validated['faculty_id'] ?? null,
                'recommender_name' => $validated['recommender_name'] ?? 'Hitesh Vadalia',
                'recommender_designation' => $validated['recommender_designation'] ?? 'I/C. Principal',
                'issue_date' => $validated['issue_date'] ?? null,
                'created_by' => optional(auth()->user())->id,
            ]);
        } else {
            if (!Schema::hasTable('issue_certificate_histories')) {
                return response()->json([
                    'status' => false,
                    'message' => 'History table not found.',
                ], 422);
            }

            $history = IssueCertificateHistory::create([
                'certificate_type' => $validated['certificate_type'],
                'register_id' => $admission->id,
                'student_name' => $studentName,
                'issue_date' => $validated['issue_date'] ?? null,
                'academic_year' => $validated['academic_year'] ?? null,
                'created_by' => optional(auth()->user())->id,
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Certificate history saved.',
            'data' => $this->formatHistoryRow($history, $validated['certificate_type']),
        ]);
    }

    protected function certificateIndex(Request $request, string $type)
    {
        $typeLabel = match ($type) {
            self::TYPE_ENGLISH_MEDIUM => 'Medium of Instruction',
            self::TYPE_LETTER_RECOMMENDATION => 'Letter of Recommendation',
            self::TYPE_TC => 'Transfer Certificate',
            default => 'Bonafide Certificate',
        };

        $modules = [
            'title' => $typeLabel,
            'folder_path' => 'software.module.issue-certificate',
            'route' => 'issue-certificate.' . $type,
            'certificate_type' => $type,
            'certificate_route' => match ($type) {
                self::TYPE_TC => 'transfer-certificate',
                self::TYPE_ENGLISH_MEDIUM => 'english-medium-certificate',
                self::TYPE_LETTER_RECOMMENDATION => 'letter-of-recommendation',
                default => 'bonafide-certificate',
            },
        ];

        View::share('modules', $modules);

        $students = $this->getStudentOptions();
        $defaults = $this->getCertificateDefaults();
        $histories = $this->getHistories($type);
        $academicYears = $this->getAcademicYears();
        
        $faculties = [];
        if ($type === self::TYPE_LETTER_RECOMMENDATION) {
            $faculties = \App\Models\User::where('status', 'active')
                ->whereHas('roles', function ($q) {
                    $q->where('name', 'Faculty');
                })
                ->orderBy('name', 'asc')
                ->get();
        }

        return view($modules['folder_path'] . '.index', compact('modules', 'students', 'defaults', 'histories', 'faculties', 'academicYears'));
    }

    private function getStudentOptions()
    {
        return Admission::query()
            ->when(Schema::hasTable('cource_registration') && \App\Models\CourceRegistration::exists(), function ($q) {
                $q->whereHas('courses', function ($sq) {
                    $sq->whereNotIn('status', ['Cancel', 'cancel']);
                });
            })
            ->where(function ($q) {
                $q->whereNull('status')->orWhereNotIn('status', ['Cancel', 'cancel', 'rejected']);
            })
            ->orderByDesc('id')
            ->get()
            ->map(function ($admission) {
                $name = trim(
                    ($admission->first_name ?? '') . ' ' .
                    ($admission->last_name ?? '') . ' ' .
                    ($admission->father_name ?? '')
                );

                $extra = [];
                if (!empty($admission->gr_no)) {
                    $extra[] = 'GR: ' . $admission->gr_no;
                }
                if (!empty($admission->current_std)) {
                    $extra[] = 'Std: ' . $admission->current_std;
                }
                $extraInfo = !empty($extra) ? ' (' . implode(', ', $extra) . ')' : '';

                return [
                    'id' => $admission->id,
                    'label' => trim($name . $extraInfo),
                ];
            })
            ->values();
    }

    private function getCertificateDefaults(): array
    {
        $today = now();
        $currYear = (int)$today->format('Y');
        $academicYear = ($today->month >= 6) ? $currYear . '-' . ($currYear + 1) : ($currYear - 1) . '-' . $currYear;

        return [
            'issue_date' => $today->format('Y-m-d'),
            'academic_year' => $academicYear,
        ];
    }

    private function getAcademicYears(): array
    {
        $today = now();
        $currYear = (int)$today->format('Y');
        $currentAcademicStartYear = ($today->month >= 6) ? $currYear : ($currYear - 1);

        $years = [];
        for ($y = $currentAcademicStartYear; $y >= $currentAcademicStartYear - 15; $y--) {
            $years[] = $y . '-' . ($y + 1);
        }
        return $years;
    }

    private function getHistories(string $type)
    {
        if ($type === self::TYPE_LETTER_RECOMMENDATION) {
            if (!Schema::hasTable('issue_certificate_letter_recommendations')) {
                return collect();
            }

            return IssueCertificateLetterRecommendation::latest('id')
                ->limit(100)
                ->get()
                ->map(function ($history) use ($type) {
                    return $this->formatHistoryRow($history, $type);
                });
        }

        if (!Schema::hasTable('issue_certificate_histories')) {
            return collect();
        }

        return IssueCertificateHistory::where('certificate_type', $type)
            ->latest('id')
            ->limit(100)
            ->get()
            ->map(function ($history) use ($type) {
                return $this->formatHistoryRow($history, $type);
            });
    }

    private function formatHistoryRow($history, string $type): array
    {
        $certificateRoute = match ($type) {
            self::TYPE_TC => 'transfer-certificate',
            self::TYPE_ENGLISH_MEDIUM => 'english-medium-certificate',
            self::TYPE_LETTER_RECOMMENDATION => 'letter-of-recommendation',
            default => 'bonafide-certificate',
        };

        $studentId = $history->admission_id ?? $history->register_id;

        $params = http_build_query(array_filter([
            'issue_date' => $history->issue_date,
            'academic_year' => $history->academic_year ?? null,
            'recommender_name' => $history->recommender_name ?? null,
            'recommender_designation' => $history->recommender_designation ?? null,
            'recommender_type' => $history->recommender_type ?? null,
            'faculty_id' => $history->faculty_id ?? null,
        ]));

        $certificateUrl = route($certificateRoute, $studentId) . ($params ? '?' . $params : '');

        return [
            'id' => $history->id,
            'register_id' => $studentId,
            'student_name' => $history->student_name ?: '-',
            'issue_date' => $this->formatDisplayDate($history->issue_date),
            'academic_year' => $history->academic_year ?? '-',
            'generated_at' => $history->created_at
                ? $history->created_at->format('d-m-Y h:i A')
                : '-',
            'certificate_url' => $certificateUrl,
        ];
    }

    private function formatDisplayDate(?string $value): string
    {
        if (empty($value)) {
            return '-';
        }

        try {
            return \Carbon\Carbon::parse($value)->format('j-m-Y');
        } catch (\Exception $e) {
            return $value;
        }
    }

    private function getHistoriesLetterRecommendation()
    {
        if (!Schema::hasTable('issue_certificate_letter_recommendations')) {
            return collect();
        }

        return IssueCertificateLetterRecommendation::latest('id')
            ->limit(100)
            ->get()
            ->map(function ($history) {
                $params = http_build_query(array_filter([
                    'issue_date' => $history->issue_date,
                    'recommender_name' => $history->recommender_name,
                    'recommender_designation' => $history->recommender_designation,
                    'recommender_type' => $history->recommender_type,
                    'faculty_id' => $history->faculty_id,
                ]));

                $certificateUrl = route('letter-of-recommendation', $history->admission_id) . ($params ? '?' . $params : '');

                return [
                    'id' => $history->id,
                    'student_name' => $history->student_name ?: '-',
                    'issue_date' => $this->formatDisplayDate($history->issue_date),
                    'recommender_name' => $history->recommender_name ?: '-',
                    'recommender_type' => ucfirst($history->recommender_type ?? 'principal'),
                    'generated_at' => $history->created_at
                        ? $history->created_at->format('d-m-Y h:i A')
                        : '-',
                    'certificate_url' => $certificateUrl,
                ];
            });
    }
}
