<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NotificationSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => 'required|string',
            'academic_year_id' => 'required|integer|exists:master_academic_years,id',
            'standard_id' => 'required|integer|exists:master_standards,id',
            'class_id' => 'required|array|min:1',
            'class_id.*' => 'required|string|max:50',
            'student_id' => 'required|array|min:1',
            'student_id.*' => 'string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'pdf' => 'nullable|mimes:pdf|max:15360',
        ];
    }

    public function messages(): array
    {
        return [
            'academic_year_id.required' => 'Please select academic year.',
            'standard_id.required' => 'Please select standard.',
            'class_id.required' => 'Please select at least one class.',
            'student_id.required' => 'Please select at least one student.',
        ];
    }
}
