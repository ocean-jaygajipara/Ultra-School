<?php

namespace App\Http\Controllers\admin\master;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterBusRouteVillageRequest;
use App\Models\Master\MasterBusRouteVillage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Builder;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MasterBusRouteVillageController extends Controller
{
    public $modules = [];
    protected array $exportableColumns = [
        'name' => 'Village Name',
        'charge' => 'Transport Charge (₹)',
        'status' => 'Status',
    ];

    protected array $defaultExportColumns = [
        'name',
        'charge',
        'status',
    ];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Bus Route Village',
            'folder_path' => 'software.module.master.bus-route-village',
            'route' => 'bus-route-village',
            'table_name' => (new MasterBusRouteVillage())->getTable(),
            'permission_prefix' => 'bus-route-village',
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

        if (!$modules['permission_list']) {
            if ($request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }

        View::share('modules', $modules);

        try {
            $columns = [
                (object)['data' => "name", 'name' => 'name', 'td_label' => 'Village Name'],
                (object)['data' => "charge", 'name' => 'charge', 'td_label' => 'Transport Charge (₹)', 'className' => 'text-end', 'width' => '15%'],
                (object)['data' => "status", 'name' => 'status', 'td_label' => 'Status', 'className' => 'w-5 text-center', 'width' => '10%'],
                (object)['data' => "action", 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' => 'w-10 text-center'],
            ];
            View::share("columns", $columns);

            if ($request->ajax()) {
                $data = MasterBusRouteVillage::withTrashed()->orderBy('id', 'desc');

                return Datatables::of($data)
                    ->filter(function ($query) use ($request) {
                        $search = $request->input('search.value');
                        if (!empty($search)) {
                            $query->where(function ($q) use ($search) {
                                $q->where('name', 'like', "%{$search}%")
                                  ->orWhere('charge', 'like', "%{$search}%");
                            });
                        }
                    })
                    ->editColumn('charge', function ($row) {
                        return '₹' . number_format((float)($row->charge ?? 0), 2);
                    })
                    ->editColumn('status', function ($row) use ($modules) {
                        if ($row->status === "active") {
                            return '<button class="btn btn-success btn-sm dropdown-toggle" data-bs-toggle="dropdown">' . ucfirst($row->status) . '</button>
                                <ul class="dropdown-menu">
                                    <li><a href="javascript:void(0)" class="dropdown-item update-status" data-url="' . route($modules["route"] . '.status-update') . '" data-id="' . $row->id . '" data-update_status="inactive">Inactive</a></li>
                                </ul>';
                        } elseif ($row->status === "inactive") {
                            return '<button class="btn btn-danger btn-sm dropdown-toggle" data-bs-toggle="dropdown">' . ucfirst($row->status) . '</button>
                                <ul class="dropdown-menu">
                                    <li><a href="javascript:void(0)" class="dropdown-item update-status" data-url="' . route($modules["route"] . '.status-update') . '" data-id="' . $row->id . '" data-update_status="active">Active</a></li>
                                </ul>';
                        }
                        return '';
                    })
                    ->addColumn('action', function ($row) use ($modules) {
                        $btn = '';

                        if (!$row->deleted_at) {
                            if ($modules['permission_edit']) {
                                $btn .= '<a href="' . route($modules["route"] . ".edit", [$row->id]) . '" class="btn btn-primary btn-icon"><i class="fa-solid fa-pen-to-square"></i></a>';
                            }
                            if ($modules['permission_delete']) {
                                $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row->id]) . '" class="btn btn-danger btn-icon deletebutton mx-1"><i class="fa-solid fa-trash"></i></a>';
                            }
                        } else {
                            $btn .= '<a href="javascript:void(0)" data-restore="' . route($modules["route"] . ".restore", ['id' => $row->id]) . '" class="btn btn-light mx-1 record-restore"><i class="ti ti-history"></i> Restore</a>';
                            $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".permanent-delete", [$row->id]) . '" class="btn btn-danger btn-icon permanent-delete mx-1"><i class="fa-solid fa-trash"></i></a>';
                        }

                        return $btn ?: '-';
                    })
                    ->rawColumns(['status', 'action'])
                    ->make(true);
            }

            $availableExportColumns = $this->exportableColumns;
            $defaultExportColumns = $this->defaultExportColumns;

            return view($modules['folder_path'] . '.index', compact('modules', 'availableExportColumns', 'defaultExportColumns'));
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
        }
    }

    public function create()
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');
        if (!$modules['permission_add']) {
            if (request()->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }
        View::share('modules', $modules);
        return view($modules['folder_path'] . '.form', compact('modules'));
    }

    public function store(MasterBusRouteVillageRequest $request)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');
        if (!$modules['permission_add']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }

        View::share('modules', $modules);
        $validated = $request->validated();

        try {
            $loginUserId = Auth::user()->id;
            $validated['created_by'] = $loginUserId;

            $village = MasterBusRouteVillage::create($validated);

            if ($request->ajax()) {
                return $this->sendResponse([], 'Record Created Successfully');
            }

            flash()->success('Record Created Successfully');
            return redirect()->route($modules['route'] . '.index');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return $this->sendError($e->getMessage(), [], [], 500);
            }
            return redirect()->back()->withInput()->withErrors($e->getMessage());
        }
    }

    public function edit(string $id)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_edit'] = Helper::directCan($modules['permission_prefix'] . '-edit');
        if (!$modules['permission_edit']) {
            if (request()->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }
        View::share('modules', $modules);

        try {
            $edit = MasterBusRouteVillage::findOrFail($id);
            return view($modules['folder_path'] . '.form', compact('modules', 'edit'));
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    public function update(MasterBusRouteVillageRequest $request, string $id)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_edit'] = Helper::directCan($modules['permission_prefix'] . '-edit');
        if (!$modules['permission_edit']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }

        View::share('modules', $modules);
        $validated = $request->validated();

        try {
            $updateData = MasterBusRouteVillage::findOrFail($id);
            $loginUserId = Auth::user()->id;
            $validated['updated_by'] = $loginUserId;

            $updateData->update($validated);

            if ($request->ajax()) {
                return $this->sendResponse([], 'Record Updated Successfully');
            }

            flash()->success('Record Updated Successfully');
            return redirect()->route($modules['route'] . '.index');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return $this->sendError($e->getMessage(), [], [], 500);
            }
            return redirect()->back()->withInput()->withErrors($e->getMessage());
        }
    }

    public function status_update(Request $request)
    {
        return $this->statusUpdate($request);
    }

    public function statusUpdate(Request $request)
    {
        $modules = $this->modules;
        $modules['permission_edit'] = Helper::directCan($modules['permission_prefix'] . '-edit');
        if (!$modules['permission_edit']) {
            return response()->json(['status' => false, 'message' => 'Unauthorized!'], 403);
        }

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ]);
        }

        try {
            $village = MasterBusRouteVillage::withTrashed()->findOrFail($request->id);
            $village->status = $request->status;
            $village->updated_by = Auth::user()->id;
            $village->save();

            return response()->json([
                'status' => true,
                'message' => 'Status updated successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, string $id)
    {
        $modules = $this->modules;
        $modules['permission_delete'] = Helper::directCan($modules['permission_prefix'] . '-delete');
        if (!$modules['permission_delete']) {
            if ($request->ajax()) {
                return response()->json(['status' => false, 'message' => 'User does not have the right permissions.'], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }

        try {
            $village = MasterBusRouteVillage::findOrFail($id);
            $village->deleted_by = Auth::user()->id;
            $village->save();
            $village->delete();

            if ($request->ajax()) {
                return response()->json([
                    'status' => true,
                    'message' => 'Record moved to trash successfully',
                ]);
            }

            flash()->success('Record moved to trash successfully');
            return redirect()->route($modules['route'] . '.index');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function restore(Request $request, $id)
    {
        $modules = $this->modules;
        $modules['permission_delete'] = Helper::directCan($modules['permission_prefix'] . '-delete');
        if (!$modules['permission_delete']) {
            if ($request->ajax()) {
                return response()->json(['status' => false, 'message' => 'User does not have the right permissions.'], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }

        try {
            $village = MasterBusRouteVillage::withTrashed()->findOrFail($id);
            $village->deleted_by = null;
            $village->restore();

            if ($request->ajax()) {
                return response()->json([
                    'status' => true,
                    'message' => 'Record Restored Successfully',
                ]);
            }

            flash()->success('Record Restored Successfully');
            return redirect()->route($modules['route'] . '.index');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function permanentDelete(Request $request, $id)
    {
        $modules = $this->modules;
        $modules['permission_delete'] = Helper::directCan($modules['permission_prefix'] . '-delete');
        if (!$modules['permission_delete']) {
            if ($request->ajax()) {
                return response()->json(['status' => false, 'message' => 'User does not have the right permissions.'], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }

        try {
            $village = MasterBusRouteVillage::withTrashed()->findOrFail($id);
            $village->forceDelete();

            if ($request->ajax()) {
                return response()->json([
                    'status' => true,
                    'message' => 'Record Permanently Deleted',
                ]);
            }

            flash()->success('Record Permanently Deleted');
            return redirect()->route($modules['route'] . '.index');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function deleteSelected(Request $request)
    {
        $modules = $this->modules;
        $modules['permission_delete'] = Helper::directCan($modules['permission_prefix'] . '-delete');
        if (!$modules['permission_delete']) {
            return response()->json(['status' => false, 'message' => 'Unauthorized!'], 403);
        }

        $ids = $request->ids;
        if (!empty($ids) && is_array($ids)) {
            MasterBusRouteVillage::whereIn('id', $ids)->update(['deleted_by' => Auth::user()->id]);
            MasterBusRouteVillage::whereIn('id', $ids)->delete();
            return response()->json(['status' => true, 'message' => 'Selected records moved to trash successfully!']);
        }

        return response()->json(['status' => false, 'message' => 'No records selected!']);
    }

    public function printView(Request $request)
    {
        return $this->print($request);
    }

    public function print(Request $request)
    {
        $modules = $this->modules;
        $modules['permission_list'] = Helper::directCan($modules['permission_prefix'] . '-list');
        if (!$modules['permission_list']) {
            abort(403, 'User does not have the right permissions.');
        }

        $columns = $request->input('columns', []);
        $filterStatus = $request->input('status', 'all');

        $query = MasterBusRouteVillage::query();

        if ($filterStatus === 'deleted') {
            $query->onlyTrashed();
        } elseif ($filterStatus === 'active') {
            $query->where('status', 'active')->whereNull('deleted_at');
        } elseif ($filterStatus === 'inactive') {
            $query->where('status', 'inactive')->whereNull('deleted_at');
        } else {
            $query->withTrashed();
        }

        $villages = $query->orderBy('id', 'desc')->get();
        $availableExportColumns = $this->exportableColumns;

        return view($modules['folder_path'] . '.print', compact('villages', 'columns', 'availableExportColumns', 'modules'));
    }

    public function exportExcel(Request $request)
    {
        $modules = $this->modules;
        $modules['permission_list'] = Helper::directCan($modules['permission_prefix'] . '-list');
        if (!$modules['permission_list']) {
            abort(403, 'User does not have the right permissions.');
        }

        $requestColumns = $request->input('columns');
        if (empty($requestColumns)) {
            $requestColumns = $this->defaultExportColumns;
        } elseif (is_string($requestColumns)) {
            $requestColumns = explode(',', $requestColumns);
        }

        $validColumns = array_intersect($requestColumns, array_keys($this->exportableColumns));
        if (empty($validColumns)) {
            $validColumns = $this->defaultExportColumns;
        }

        $query = MasterBusRouteVillage::query();
        $filterStatus = $request->input('status', 'all');
        if ($filterStatus === 'deleted') {
            $query->onlyTrashed();
        } elseif ($filterStatus === 'active') {
            $query->where('status', 'active')->whereNull('deleted_at');
        } elseif ($filterStatus === 'inactive') {
            $query->where('status', 'inactive')->whereNull('deleted_at');
        } else {
            $query->withTrashed();
        }

        $records = $query->orderBy('id', 'desc')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Bus Route Villages');

        $colIndex = 1;
        $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . '1', 'Sr No');
        foreach ($validColumns as $columnKey) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . '1', $this->exportableColumns[$columnKey]);
        }

        $rowIndex = 2;
        $srNo = 1;
        foreach ($records as $record) {
            $colIndex = 1;
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $rowIndex, $srNo++);
            foreach ($validColumns as $columnKey) {
                $value = $this->formatColumnValueForExcel($record, $columnKey);
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $rowIndex, $value);
            }
            $rowIndex++;
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Bus_Route_Village_Master_' . date('Y_m_d_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    protected function formatColumnValueForExcel(MasterBusRouteVillage $village, string $columnKey)
    {
        switch ($columnKey) {
            case 'name':
                return $village->name ?? '-';
            case 'charge':
                return isset($village->charge) ? (float)$village->charge : 0;
            case 'status':
                return ucfirst($village->status ?? '-');
            default:
                return data_get($village, $columnKey, '-');
        }
    }
}
