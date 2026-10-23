<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\BusFeeCollectionRequest;
use App\Models\Admission;
use App\Models\BusFeeCollection;
use App\Models\Master\MasterAcademicYear;
use App\Models\Master\MasterBusRouteVillage;
use App\Models\Master\MasterStandard;
use App\Services\SchoolDatabaseManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use NumberFormatter;
use Yajra\DataTables\Facades\DataTables;

class BusFeeCollectionController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Bus Transport Fee',
            'folder_path' => 'software.module.bus-fee-collection',
            'route' => 'bus-fee-collection',
            'table_name' => (new BusFeeCollection())->getTable(),
            'permission_prefix' => 'fees-collection',
        ];
    }

    public function index(Request $request)
    {
        $modules = $this->modules;
        $modules['permission_list'] = Helper::directCan($modules['permission_prefix'] . '-list');
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');

        if (!$modules['permission_list']) {
            if ($request->ajax()) {
                return response()->json(['error' => 'User does not have permission.'], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }

        View::share('modules', $modules);

        $columns = [
            (object) ['data' => 'receipt_no', 'name' => 'receipt_no', 'td_label' => 'Receipt No'],
            (object) ['data' => 'date', 'name' => 'date', 'td_label' => 'Date'],
            (object) ['data' => 'gr_no', 'name' => 'gr_no', 'td_label' => 'GR No'],
            (object) ['data' => 'student_name', 'name' => 'student_name', 'td_label' => 'Student Name'],
            (object) ['data' => 'bus_route_village', 'name' => 'bus_route_village', 'td_label' => 'Bus Route'],
            (object) ['data' => 'period_label', 'name' => 'period_label', 'td_label' => 'Fee Type'],
            (object) ['data' => 'amount', 'name' => 'amount', 'td_label' => 'Amount'],
            (object) ['data' => 'mode', 'name' => 'mode', 'td_label' => 'Mode'],
            (object) ['data' => 'action', 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' => 'text-center'],
        ];
        View::share('columns', $columns);

        if ($request->ajax()) {
            $query = BusFeeCollection::query()->orderByDesc('id');

            return DataTables::of($query)
                ->editColumn('date', function ($row) {
                    return $row->date ? $row->date->format('d-m-Y') : '-';
                })
                ->editColumn('amount', function ($row) {
                    return number_format((float) $row->amount, 2);
                })
                ->addColumn('action', function ($row) use ($modules) {
                    $viewUrl = route($modules['route'] . '.show', $row->id);
                    $printUrl = route($modules['route'] . '.print', $row->id);
                    return '<div class="d-inline-flex gap-1">' .
                        '<a href="' . $viewUrl . '" target="_blank" class="btn btn-sm btn-info" title="View Receipt"><i class="ti ti-eye"></i></a>' .
                        '<a href="' . $printUrl . '" target="_blank" class="btn btn-sm btn-primary" title="Print Receipt"><i class="ti ti-printer"></i></a>' .
                        '</div>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view($modules['folder_path'] . '.index', compact('modules'));
    }

    public function create()
    {
        $modules = $this->modules;
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');

        if (!$modules['permission_add']) {
            abort(403, 'User does not have the right permissions.');
        }

        View::share('modules', $modules);

        $academicYears = MasterAcademicYear::where('status', 'active')
            ->orderByDesc('start_date')
            ->pluck('name');
        $currentYear = MasterAcademicYear::current()?->name;
        $standards = MasterStandard::where('status', 'active')->orderBy('id')->get(['id', 'name']);
        $busRoutes = MasterBusRouteVillage::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view($modules['folder_path'] . '.form', compact('modules', 'academicYears', 'currentYear', 'standards', 'busRoutes'));
    }

    public function studentsByStandard(Request $request)
    {
        $standardId = (int) $request->input('standard_id');

        if (!$standardId) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $students = Admission::with('currentStandard')
            ->where('current_std_id', $standardId)
            ->orderBy('division')
            ->orderBy('roll_no')
            ->orderBy('first_name')
            ->get()
            ->map(function ($row) {
                // Same label format as admission select list
                $label = trim(($row->first_name ?? '') . ' ' . ($row->last_name ?? '') . ' ' . ($row->father_name ?? ''));

                return [
                    'id' => $row->id,
                    'name' => $label,
                    'has_bus_route' => !empty($row->bus_route_village),
                ];
            })
            ->values();

        return response()->json(['success' => true, 'data' => $students]);
    }

    public function store(BusFeeCollectionRequest $request)
    {
        $modules = $this->modules;
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');

        if (!$modules['permission_add']) {
            abort(403, 'User does not have the right permissions.');
        }

        $admission = Admission::with('currentStandard')->findOrFail($request->admission_id);

        $busRoute = $request->input('bus_route_village') ?: $admission->bus_route_village;

        $record = BusFeeCollection::create([
            'receipt_no' => BusFeeCollection::nextReceiptNo(),
            'admission_id' => $admission->id,
            'student_name' => $admission->full_name,
            'gr_no' => $admission->gr_no,
            'standard' => $admission->current_std,
            'division' => $admission->division,
            'bus_route_village' => $busRoute,
            'academic_year' => $request->academic_year,
            'period_label' => $request->period_label,
            'date' => $request->date,
            'amount' => $request->amount,
            'mode' => $request->mode,
            'upi_id' => $request->upi_id,
            'cheque_no' => $request->cheque_no,
            'remark' => $request->remark,
            'status' => 'active',
            'created_by' => Auth::id(),
        ]);

        return Redirect::route($modules['route'] . '.print', $record->id);
    }

    public function studentDetails($id)
    {
        $admission = Admission::with('currentStandard')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $admission->id,
                'student_name' => $admission->full_name,
                'gr_no' => $admission->gr_no,
                'standard' => $admission->current_std,
                'division' => $admission->division,
                'bus_route_village' => $admission->bus_route_village,
                'charge' => null,
                'has_bus_route' => !empty($admission->bus_route_village),
            ],
        ]);
    }

    public function show($id, Request $request)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        $feesData = BusFeeCollection::with('createdByUser')->findOrFail($id);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $feesData,
            ]);
        }

        $schoolName = SchoolDatabaseManager::getActiveSchool()['name'] ?? 'Ultra School';
        $amountInWords = $this->amountInWords((float) $feesData->amount);
        $autoPrint = false;

        return view($modules['folder_path'] . '.print', compact('feesData', 'schoolName', 'amountInWords', 'autoPrint'));
    }

    public function print($id)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        $feesData = BusFeeCollection::with('createdByUser')->findOrFail($id);
        $schoolName = SchoolDatabaseManager::getActiveSchool()['name'] ?? 'Ultra School';
        $amountInWords = $this->amountInWords((float) $feesData->amount);
        $autoPrint = true;

        return view($modules['folder_path'] . '.print', compact('feesData', 'schoolName', 'amountInWords', 'autoPrint'));
    }

    protected function amountInWords(float $amount): string
    {
        try {
            $formatter = new NumberFormatter('en', NumberFormatter::SPELLOUT);
            $words = $formatter->format((int) round($amount));

            return ucwords($words) . ' Rupees Only';
        } catch (\Throwable $e) {
            return number_format($amount, 2) . ' Rupees Only';
        }
    }
}
