<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Admission;
use App\Rules\ValidAadhaar;

class AdmissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation()
    {
        $mergeData = [
            'aadhar_card_no' => preg_replace('/\s+/', '', $this->aadhar_card_no),
        ];

        if ($this->filled('date_of_birth')) {
            $dob = trim($this->date_of_birth);
            if (preg_match('/^(\d{1,2})[-\/\.](\d{1,2})[-\/\.](\d{4})$/', $dob, $matches)) {
                $mergeData['date_of_birth'] = sprintf('%04d-%02d-%02d', $matches[3], $matches[2], $matches[1]);
            }
        }

        if ($this->filled('admission_date')) {
            $admDate = trim($this->admission_date);
            if (preg_match('/^(\d{1,2})[-\/\.](\d{1,2})[-\/\.](\d{4})$/', $admDate, $matches)) {
                $mergeData['admission_date'] = sprintf('%04d-%02d-%02d', $matches[3], $matches[2], $matches[1]);
            }
        }

        if ($this->filled('lc_date')) {
            $lcDate = trim($this->lc_date);
            if (preg_match('/^(\d{1,2})[-\/\.](\d{1,2})[-\/\.](\d{4})$/', $lcDate, $matches)) {
                $mergeData['lc_date'] = sprintf('%04d-%02d-%02d', $matches[3], $matches[2], $matches[1]);
            }
        }

        $mergeData['is_new_admission'] = ($this->has('is_new_admission') && ($this->is_new_admission == '1' || $this->is_new_admission === true || $this->is_new_admission == 'on')) ? 1 : 0;

        $this->merge($mergeData);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(Request $request): array
    {
        $id = $request->route('admission') ?? 0;

        $admission = $id ? Admission::find($id) : null;
        $profilePicRule = ($admission && $admission->profile_pic) ? 'nullable' : 'required';

        $rules = [
            'aadhar_card_no' => [
                'required',
                'regex:/^[2-9]{1}[0-9]{11}$/',
                Rule::unique((new Admission())->getTable(), 'aadhar_card_no')->ignore($id)
                // Rule::unique((new Admission())->getTable())->where(function ($query) use ($request) {
                //     return $query->where('aadhar_card_no', $request->aadhar_card_no);
                // })->ignore($id),
            ],

            'pen_no' => [
                'nullable',
                'string',
                'max:30',
            ],

            'gr_no' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique((new Admission())->getTable(), 'gr_no')->ignore($id),
            ],

            'bus_route_village' => [
                'nullable',
                'string',
                'max:255',
            ],

            'biometric_id' => [
                'nullable',
                'string',
                'max:255',
            ],

            'admission_date' => [
                'nullable',
                'date',
            ],

            'admission_std' => [
                'nullable',
                'string',
                'max:255',
            ],

            'current_std' => [
                'nullable',
                'string',
                'max:255',
            ],

            'division' => [
                'nullable',
                'string',
                'max:255',
            ],

            'stream' => [
                'nullable',
                'string',
                'max:255',
            ],

            'first_name' => [
                'required'
            ],

            'last_name' => [
                'required'
            ],

            'father_name' => [
                'required'
            ],

            'mother_name' => [
                'required'
            ],

            'temporary_address' => [
                'required'
            ],

            'permanent_address' => [
                'required'
            ],

            'mobile_no' => [
                'required',
                'regex:/^(?:\+91|91)?[6-9]\d{9}$/',
                Rule::unique((new Admission())->getTable(), 'mobile_no')->ignore($id),
            ],

            'parent_mobile_no' => [
                'nullable',
                'regex:/^(?:\+91|91)?[6-9]\d{9}$/',
                Rule::unique((new Admission())->getTable(), 'parent_mobile_no')->ignore($id),
            ],

            'other_mobile_no' => [
                'nullable',
                // 'regex:/^[6-9]\d{9}$/',
                // Rule::unique((new Admission())->getTable(), 'other_mobile_no')->ignore($id),
            ],

            'profile_pic' => [
                $profilePicRule,
                'image',
                'mimes:jpeg,png,jpg,gif',
                'max:2048',
            ],

            'whatsapp_no' => [
                'nullable',
                'regex:/^(?:\+91|91)?[6-9]\d{9}$/',
                Rule::unique((new Admission())->getTable(), 'whatsapp_no')->ignore($id),
            ],

            'cast' => [
                'nullable'
            ],

            'occupation' => [
                'nullable'
            ],

            'mother_occupation' => [
                'nullable',
                'string',
                'max:255',
            ],

            'religion' => [
                'nullable',
                'string',
                'max:255',
            ],

            'house' => [
                'nullable',
                'string',
                'max:255',
            ],

            'date_of_birth' => [
                'required'
            ],

            'birth_place' => [
                'nullable',
                'string',
                'max:255',
            ],

            'gender' => [
                'required'
            ],
            'category' => [
                'required'
            ],
            'spid' => [
                'nullable'
            ],
            'apaar_id_abc_id' => [
                'nullable'
            ],
            'udise' => [
                'nullable'
            ],
            'bank_name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'bank_account_no' => [
                'nullable',
                'string',
                'max:255',
            ],
            'is_new_admission' => [
                'nullable',
                'boolean',
            ],
            'last_school_name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'old_gr_no' => [
                'nullable',
                'string',
                'max:255',
            ],
            'passed_standard' => [
                'nullable',
                'string',
                'max:255',
            ],
            'lc_no' => [
                'nullable',
                'string',
                'max:255',
            ],
            'lc_date' => [
                'nullable',
                'date',
            ],
            'attendance' => [
                'nullable',
                'string',
                'max:255',
            ],
            'gdrivefolderurl' => [
                'nullable'
            ],
            'gdrivefolderid' => [
                'nullable'
            ],
            'gdrivefoldername' => [
                'nullable'
            ],

            'education' => 'nullable|array|min:1',
            'education.*' => 'nullable|string',

            'percentage_cgpa' => 'nullable|array|min:1',
            'percentage_cgpa.*' => 'nullable|string|max:255',

            'seat_no_nrollment_no' => 'nullable|array',
            'seat_no_nrollment_no.*' => 'nullable|string',

            'board_university' => 'nullable|array',
            'board_university.*' => 'nullable|string',

            'passing_year' => 'nullable|array',
            'passing_year.*' => 'nullable|string',

            'school_name_college_name' => 'nullable|array',
            'school_name_college_name.*' => 'nullable|string',
        ];
        // dd("L-155", $rules, $id, $request->all(), $request->route('master-city'), $request->route()->parameters());
        return $rules;
    }

    public function messages()
    {
        return [
            'aadhar_card_no.required' => 'The Aadhar Card No. is required.',
            'aadhar_card_no.regex' => 'Aadhaar number must be 12 digits and cannot start with 0 or 1.',
            'first_name.required' => 'The First Name is required.',
            'last_name.required' => 'The Last Name is required.',
            'father_name.required' => 'The Father Name is required.',
            'mother_name.required' => 'The Mother Name is required.',
            'temporary_address.required' => 'The Temporary Address is required.',
            'permanent_address.required' => 'The Permanent Address is required.',
            'mobile_no.required' => 'The Mobile No. is required.',
            'date_of_birth.required' => 'The Date of Birth is required.',
            'gender.required' => 'The Gender is required.',
            'category.required' => 'The Category is required.',
            'profile_pic.required' => 'The Profile Picture is required.',
        ];
    }
}
