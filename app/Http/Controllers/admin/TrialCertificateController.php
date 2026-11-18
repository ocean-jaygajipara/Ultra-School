<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\Master\MasterStandard;
use App\Models\TrialCertificate;
use App\Services\SchoolDatabaseManager;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class TrialCertificateController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title'       => 'Trial Certificate',
            'folder_path' => 'software.module.trial-certificate',
            'route'       => 'trial-certificate',
            'permission'  => 'trial-certificate-list',
        ];
    }

    public function index(Request $request)
    {
        $this->authorizeUsOnly();

        $modules = $this->modules;
        View::share('modules', $modules);
        View::share('sidebar_active', 'trial-certificate');

        $filters = [
            'q' => trim((string) $request->input('q', '')),
        ];

        $entries = TrialCertificate::query()
            ->when($filters['q'] !== '', function ($q) use ($filters) {
                $term = '%' . $filters['q'] . '%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('gr_no', 'like', $term)
                        ->orWhere('student_name', 'like', $term)
                        ->orWhere('batch_no', 'like', $term);
                });
            })
            ->orderByDesc('id')
            ->paginate(100);

        $historyCount = TrialCertificate::query()->count();

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

    public function store(Request $request)
    {
        $this->authorizeUsOnly();

        $validated = $request->validate([
            'standard_id'     => 'required|integer',
            'print_date'      => 'nullable|string|max:20',
            'trial'           => 'required|string|max:30',
            'exam_month'      => 'required|string|max:30',
            'exam_year'       => 'required|string|max:10',
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

        $standard = MasterStandard::find($standardId);
        $printDate = trim((string) ($validated['print_date'] ?? ''));
        if ($printDate === '') {
            $printDate = Carbon::now()->format('d-m-Y');
        }
        $examLabel = $validated['exam_month'] . '-' . $validated['exam_year'];
        $batchNo = 'TC-' . now()->format('Ymd-His') . '-' . Str::upper(Str::random(4));

        DB::beginTransaction();
        try {
            foreach ($students as $student) {
                TrialCertificate::create([
                    'batch_no'     => $batchNo,
                    'admission_id' => $student->id,
                    'standard_id'  => $standardId,
                    'std'          => (string) ($student->current_std ?? $standard?->name ?? ''),
                    'class'        => (string) ($student->division ?? ''),
                    'roll_no'      => (string) ($student->roll_no ?? ''),
                    'gr_no'        => (string) ($student->gr_no ?? ''),
                    'student_name' => $this->boardStudentName($student),
                    'uid_no'       => $this->resolveUid($student),
                    'ssc_index'    => (string) ($student->spid ?? ''),
                    'print_date'   => $printDate,
                    'trial'        => $validated['trial'],
                    'exam_month'   => $validated['exam_month'],
                    'exam_year'    => $validated['exam_year'],
                    'exam_label'   => $examLabel,
                    'place'        => 'KESHOD',
                    'created_by'   => Auth::id(),
                    'updated_by'   => Auth::id(),
                ]);
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()->withInput()
                ->with('error', $e->getMessage() ?: 'Unable to generate certificates.');
        }

        return redirect()
            ->route('trial-certificate.index')
            ->with('success', $students->count() . ' trial certificates generated.');
    }

    public function print(Request $request)
    {
        $this->authorizeUsOnly();

        $batchNo = trim((string) $request->input('batch_no', ''));
        $id = (int) $request->input('id', 0);

        $query = TrialCertificate::query();
        if ($id > 0) {
            $query->where('id', $id);
        } elseif ($batchNo !== '') {
            $query->where('batch_no', $batchNo);
        } else {
            return redirect()->route('trial-certificate.index')
                ->with('error', 'Please generate certificates first.');
        }

        $entries = $query->orderByRaw('CAST(gr_no AS UNSIGNED)')->orderBy('gr_no')->get();
        if ($entries->isEmpty()) {
            return redirect()->route('trial-certificate.index')
                ->with('error', 'No certificates found to print.');
        }

        $first = $entries->first();
        $stdName = (string) ($first->std ?? '');
        $isEnglish = stripos($stdName, 'english') !== false;
        $schoolTitle = $isEnglish
            ? 'ULTRA SECONDARY SCHOOL (ENG. MED.)'
            : 'ULTRA SECONDARY SCHOOL';

        $certificates = $entries->map(function (TrialCertificate $row) {
            return [
                'gr_no'        => (string) ($row->gr_no ?? ''),
                'uid_no'       => (string) ($row->uid_no ?? ''),
                'ssc_index'    => (string) ($row->ssc_index ?? ''),
                'student_name' => (string) ($row->student_name ?? ''),
                'trial'        => (string) ($row->trial ?: 'First'),
                'exam_label'   => (string) ($row->exam_label ?: trim(($row->exam_month ?? '') . '-' . ($row->exam_year ?? ''), '-')),
                'print_date'   => (string) ($row->print_date ?: Carbon::now()->format('d-m-Y')),
                'place'        => (string) ($row->place ?: 'KESHOD'),
            ];
        });

        return view($this->modules['folder_path'] . '.print', [
            'certificates' => $certificates,
            'schoolTitle'  => $schoolTitle,
        ]);
    }

    public function destroy($id)
    {
        $this->authorizeUsOnly();

        try {
            TrialCertificate::findOrFail($id)->delete();

            return response()->json(['success' => true, 'message' => 'Deleted successfully.']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage() ?: 'Delete failed.'], 422);
        }
    }

    protected function formView(Request $request)
    {
        $this->authorizeUsOnly();

        $modules = $this->modules;
        View::share('modules', $modules);
        View::share('sidebar_active', 'trial-certificate');

        $standards = MasterStandard::where('status', 'active')->orderBy('id')->get();
        $filters = [
            'standard_id' => (int) $request->input('standard_id', 0),
            'print_date'  => trim((string) $request->input('print_date', Carbon::now()->format('d-m-Y'))),
            'trial'       => trim((string) $request->input('trial', 'First')),
            'exam_month'  => trim((string) $request->input('exam_month', 'June')),
            'exam_year'   => trim((string) $request->input('exam_year', (string) date('Y'))),
        ];
        if ($filters['print_date'] === '') {
            $filters['print_date'] = Carbon::now()->format('d-m-Y');
        }

        $trials = ['First', 'Second', 'Third', 'Fourth', 'Fifth', 'Sixth'];
        $examMonths = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December',
        ];
        $examYears = range((int) date('Y') - 1, (int) date('Y') + 2);

        $students = collect();
        if ($filters['standard_id'] > 0) {
            $students = $this->queryStudents($filters['standard_id'])->get();
        }

        return view($modules['folder_path'] . '.form', compact(
            'modules',
            'standards',
            'filters',
            'trials',
            'examMonths',
            'examYears',
            'students'
        ));
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

        if (!Helper::directCan('trial-certificate-list')) {
            abort(403, 'User does not have the right permissions.');
        }
    }
}
