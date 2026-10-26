<?php

namespace App\Http\Controllers\admin\master;

use App\Http\Controllers\Controller;
use App\Models\Master\MasterFeeDetail;
use App\Models\Master\MasterStandard;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;

class MasterFeeDetailController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        $this->modules = [
            'title' => 'Fee',
            'folder_path' => 'software.module.master.fee',
            'route' => 'fees',
            'table_name' => (new MasterFeeDetail())->getTable(),
            'permission_prefix' => 'fee',
        ];
    }

    public function index()
    {
        $modules = $this->modules;
        $fees = $this->feeRows();
        $standards = MasterStandard::where('status', 'active')->orderBy('id')->get(['id', 'name']);
        View::share('modules', $modules);

        return view($this->modules['folder_path'] . '.index', compact('fees', 'standards'));
    }

    // Store new fee
    public function store(Request $request)
    {
        $request->validate([
            'standard_id' => 'nullable',
            'fee_name'   => 'required|string|max:255',
            'amount'     => 'required|numeric|min:0',
        ]);

        $standardId = $request->filled('standard_id') && $request->standard_id !== 'all' ? (int) $request->standard_id : null;

        try {
            MasterFeeDetail::create([
                'standard_id' => $standardId,
                'fee_name'   => $request->fee_name,
                'amount'     => $request->amount,
                'created_by' => Auth::id(),
                'status'     => 'active',
            ]);

            $fees = $this->feeRows();

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Fee created successfully', 'fees' => $fees]);
            }

            return Redirect::route('fees.index')->withSuccess('Fee created successfully');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['message' => $e->getMessage()], 500);
            }
            return Redirect::route('fees.index')->withErrors($e->getMessage());
        }
    }

    // Update existing fee
    public function update(Request $request, $id)
    {
        $request->validate([
            'standard_id' => 'nullable',
            'fee_name'   => 'required|string|max:255',
            'amount'     => 'required|numeric|min:0',
        ]);

        $standardId = $request->filled('standard_id') && $request->standard_id !== 'all' ? (int) $request->standard_id : null;

        try {
            $fee = MasterFeeDetail::findOrFail($id);
            $fee->update([
                'standard_id' => $standardId,
                'fee_name'   => $request->fee_name,
                'amount'     => $request->amount,
                'updated_by' => Auth::id(),
            ]);

            $fees = $this->feeRows();

            return response()->json(['success' => true, 'message' => 'Fee updated successfully', 'fees' => $fees]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // Delete fee
    public function destroy($id, Request $request)
    {
        try {
            $fee = MasterFeeDetail::findOrFail($id);
            $fee->deleted_by = Auth::id();
            $fee->save();
            $fee->delete();

            $fees = $this->feeRows();

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Fee deleted successfully', 'fees' => $fees]);
            }

            return redirect()->route('fees.index')->withSuccess('Fee deleted successfully');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['message' => $e->getMessage()], 500);
            }
            return redirect()->route('fees.index')->withErrors($e->getMessage());
        }
    }

    public function restore($id)
    {
        $fee = MasterFeeDetail::withTrashed()->findOrFail($id);
        $fee->restore();

        return response()->json([
            'success' => true,
            'message' => 'Fee restored successfully',
            'fees' => $this->feeRows(),
        ]);
    }

    protected function feeRows()
    {
        return MasterFeeDetail::query()
            ->with('standard')
            ->orderBy('standard_id')
            ->orderBy('id', 'desc')
            ->get();
    }
}
