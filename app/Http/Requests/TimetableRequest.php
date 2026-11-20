<?php

namespace App\Http\Requests;

use App\Models\Master\MasterStandard;
use App\Models\Timetable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TimetableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('timetable') ?? 0;

        return [
            'standard_id' => [
                'required',
                'integer',
                Rule::exists((new MasterStandard())->getTable(), 'id'),
                Rule::unique((new Timetable())->getTable(), 'standard_id')
                    ->where(fn ($q) => $q->whereNull('deleted_at'))
                    ->ignore($id),
            ],
            'attachment' => [
                $this->isMethod('post') ? 'required' : 'nullable',
                'file',
                'mimes:pdf,doc,docx,txt,text,jpg,jpeg,png,webp',
                'max:10240',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'standard_id.unique' => 'Timetable already exists for this standard.',
            'attachment.required' => 'Please upload timetable attachment.',
        ];
    }
}
