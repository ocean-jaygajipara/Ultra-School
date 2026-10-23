<?php

namespace App\Http\Requests;

use App\Models\Master\MasterRouteNo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class MasterRouteNoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(Request $request)
    {
        $id = $request->route('route_no') ?? 0;

        return [
            'name' => [
                'required',
                'max:50',
                Rule::unique((new MasterRouteNo())->getTable(), 'name')
                    ->whereNull('deleted_at')
                    ->ignore($id),
            ],
        ];
    }
}
