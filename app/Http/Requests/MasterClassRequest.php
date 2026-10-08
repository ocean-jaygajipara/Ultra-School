<?php

namespace App\Http\Requests;

use App\Models\Master\MasterClass;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MasterClassRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(Request $request): array
    {
        $id = $request->route('class') ?? 0;
        $rules = [
            'class' => [
                'required',
                Rule::unique((new MasterClass())->getTable(), 'class')->ignore($id),
            ],
        ];
        return $rules;
    }

    public function messages()
    {
        return [
            'class.required' => 'The Class is required.',
        ];
    }
}
