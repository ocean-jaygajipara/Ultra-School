<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BusFeeCollectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'admission_id' => 'required|integer|exists:admission,id',
            'bus_route_village' => 'nullable|string|max:150',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'academic_year' => 'nullable|string|max:50',
            'period_label' => 'required|string|in:Monthly,Yearly',
            'mode' => 'required|string|in:Cash,Online,Cheque,UPI',
            'upi_id' => 'nullable|string|max:100',
            'cheque_no' => 'nullable|string|max:100',
            'remark' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'admission_id.required' => 'Please select a student.',
            'period_label.required' => 'Please select Monthly or Yearly.',
            'period_label.in' => 'Fee type must be Monthly or Yearly.',
            'amount.required' => 'Please enter bus fee amount.',
            'mode.required' => 'Please select payment mode.',
        ];
    }
}
