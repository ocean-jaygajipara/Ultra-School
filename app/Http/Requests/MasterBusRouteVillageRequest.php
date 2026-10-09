<?php

namespace App\Http\Requests;

use App\Models\Master\MasterBusRouteVillage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class MasterBusRouteVillageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(Request $request)
    {
        $id = $request->route('bus_route_village') ?? ($request->route('bus-route-village') ?? 0);

        return [
            'name' => [
                'required',
                'max:255',
                Rule::unique((new MasterBusRouteVillage())->getTable(), 'name')
                    ->whereNull('deleted_at')
                    ->ignore($id),
            ],
            'charge' => [
                'nullable',
                'numeric',
                'min:0',
            ],
        ];
    }
}
