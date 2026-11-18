<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Admission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AuthenticatedController extends Controller
{

    // public function __construct(Request $request) {}

    /**
     * Get logged-in student profile
     *
     * Returns the complete profile of the currently authenticated student, including personal details,
     * contact information and status.
     *
     * @group Student
     * @authenticated
     *
     * @response 200 {
     *   "status": "true",
     *   "message": "Login user profile get successfully.",
     *   "data": {
     *     "id": "123",
     *     "first_name": "Raj",
     *     "last_name": "Patel",
     *     "father_name": "Mahesh",
     *     "mobile_no": "9876543210",
     *     "email_address": "raj@example.com",
     *     "status": "active"
     *   }
     * }
     *
     * @response 401 scenario="Not authenticated" {
     *   "status": "false",
     *   "message": "Unauthorization.",
     *   "data": []
     * }
     */
    public function student_profile_detail(Request $request)
    {
        try {
            // return auth('student-api')->user();
            $student = Auth::guard('student-api')->user();

            if (!$student) {
                return $this->sendError("Unauthorization.", [], [], 401);
            }
            $responseData = [];
            $getProfileRequest = new Request();

            $getProfileRequest->admission_id = $student->id;
            $getProfile = (new AuthenticatedController())->student_profile_response($getProfileRequest);

            return $this->sendResponse($getProfile, 'Login user profile get successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], $e->getMessage());;
        }
        return $this->sendError('Something went wrong.', [], [], 500);
    }

    /**
     * Build student profile response
     *
     * Internal helper that assembles the student profile object from an admission id.
     *
     * @hideFromAPIDocumentation
     */
    public function student_profile_response(Request $request)
    {
        try {
            $responseData = [];

            if (!$request?->admission_id) {
                return (object)$responseData;
            }

            $studentDetail = Admission::where('id', $request?->admission_id)->first();
            if (!$studentDetail && $request?->directReturn) {
                return (object)$responseData;
            }

            $responseData['id'] = $studentDetail?->id . '';
            $responseData['aadhar_card_no'] = $studentDetail?->aadhar_card_no . '';
            $responseData['first_name'] = $studentDetail?->first_name . '';
            $responseData['last_name'] = $studentDetail?->last_name . '';
            $responseData['father_name'] = $studentDetail?->father_name . '';
            $responseData['mother_name'] = $studentDetail?->mother_name . '';
            $responseData['temporary_address'] = $studentDetail?->temporary_address . '';
            $responseData['permanent_address'] = $studentDetail?->permanent_address . '';
            $responseData['mobile_no'] = $studentDetail?->mobile_no . '';
            $responseData['parent_mobile_no'] = $studentDetail?->parent_mobile_no . '';
            $responseData['other_mobile_no'] = $studentDetail?->other_mobile_no . '';
            $responseData['whatsapp_no'] = $studentDetail?->whatsapp_no . '';
            $responseData['cast'] = $studentDetail?->cast . '';
            $responseData['occupation'] = $studentDetail?->occupation . '';
            $responseData['date_of_birth'] = $studentDetail?->date_of_birth . '';
            $responseData['email_address'] = $studentDetail?->email_address . '';
            $responseData['gender'] = $studentDetail?->gender . '';
            $responseData['category'] = $studentDetail?->category . '';
            $responseData['enrolment_no'] = $studentDetail?->enrolment_no . '';
            $responseData['spid'] = $studentDetail?->spid . '';
            $responseData['apaar_id_abc_id'] = $studentDetail?->apaar_id_abc_id . '';
            $responseData['gdrivefolderurl'] = $studentDetail?->gdrivefolderurl . '';
            $responseData['gdrivefolderid'] = $studentDetail?->gdrivefolderid . '';
            $responseData['gdrivefoldername'] = $studentDetail?->gdrivefoldername . '';
            $responseData['status'] = $studentDetail?->status . '';
            $responseData['profile_url'] = $studentDetail?->profile_pic_url . '';
            return (object)$responseData;
        } catch (\Exception $e) {
            return (object)[$e->getMessage()];
        }
        return (object)[];
    }
    /**
     * Update logged-in student profile
     *
     * Updates key contact and identification fields (mobile numbers, Aadhaar and addresses) for the
     * currently authenticated student. Returns the fresh profile after update.
     *
     * @group Student
     * @authenticated
     *
     * @bodyParam mobile_no string required Primary mobile number (must be unique). Example: 9876543210
     * @bodyParam other_mobile_no string required Alternate mobile number (must be unique). Example: 9123456780
     * @bodyParam whatsapp_no string WhatsApp number (must be unique if provided). Example: 9876543210
     * @bodyParam aadhar_card_no string required 12‑digit Aadhaar number. Example: 234567890123
     * @bodyParam temporary_address string required Current/temporary address. Example: 12, Shree Nagar Society, Surat
     * @bodyParam permanent_address string required Permanent address. Example: 45, Patel Street, Junagadh
     *
     * @response 200 {
     *   "status": "true",
     *   "message": "Update student profile successfully.",
     *   "data": {
     *     "id": "123",
     *     "mobile_no": "9876543210",
     *     "other_mobile_no": "9123456780",
     *     "whatsapp_no": "9876543210",
     *     "temporary_address": "12, Shree Nagar Society, Surat",
     *     "permanent_address": "45, Patel Street, Junagadh"
     *   }
     * }
     *
     * @response 401 scenario="Not authenticated" {
     *   "status": "false",
     *   "message": "Unauthorization",
     *   "data": []
     * }
     */
   public function student_update_profile(Request $request)
{
    try {
        $loginUser = Auth::user();
        if (!$loginUser) {
            return $this->sendError("Unauthorization", [], [], 401);
        }

        $loginUserId = $loginUser->id;

        $validator =  Validator::make($request->all(), [
            'mobile_no' => [
                'required',
                'regex:/^(?:\+91|91)?[6-9]\d{9}$/',
                Rule::unique((new Admission())->getTable(), 'mobile_no')->ignore($loginUserId),
            ],
            'other_mobile_no' => [
                'required',
                'regex:/^(?:\+91|91)?[6-9]\d{9}$/',
                Rule::unique((new Admission())->getTable(), 'other_mobile_no')->ignore($loginUserId),
            ],
            'whatsapp_no' => [ 
                'nullable',
                'regex:/^(?:\+91|91)?[6-9]\d{9}$/',
                Rule::unique((new Admission())->getTable(), 'whatsapp_no')->ignore($loginUserId),
            ],
            'aadhar_card_no' => [
                'required',
                'regex:/^[2-9]{1}[0-9]{11}$/',
                'string',
                Rule::unique((new Admission())->getTable(), 'aadhar_card_no')->ignore($loginUserId),
            ],
            'temporary_address' => [
                'required',
                'string',
            ],
            'permanent_address' => [
                'required',
                'string',
            ],
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
        }

        $updateParams = $request->only([
            'mobile_no',
            'other_mobile_no',
            'whatsapp_no',
            'aadhar_card_no',
            'temporary_address',
            'permanent_address',
        ]);

        $updateTeamPerson = Admission::where('id', $loginUser?->id)->first();
        $updateTeamPerson->update($updateParams);

        $newRequest = new Request();
        $newRequest['admission_id'] = $loginUser?->id;
        $login_profile = (array)self::student_profile_response($newRequest);

        return $this->sendResponse($login_profile, "Update student profile successfully.");
    } catch (\Exception $e) {
        return $this->sendError($e->getMessage());
    }
}

    /**
     * Person details (GET/POST) — personal info only.
     */
    public function person_details(Request $request)
    {
        try {
            $student = Auth::guard('student-api')->user();
            if (!$student) {
                return $this->sendError('Unauthorization.', [], [], 401);
            }

            return $this->sendResponse(
                $this->buildPersonDetailsPayload($student),
                'Person details fetched successfully.'
            );
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }

    /**
     * Update person details — only contact / address / personal editable fields.
     * Does not touch academic, status, GR, standard, names, DOB, etc.
     */
    public function update_person_details(Request $request)
    {
        try {
            /** @var Admission|null $student */
            $student = Auth::guard('student-api')->user();
            if (!$student) {
                return $this->sendError('Unauthorization.', [], [], 401);
            }

            $table = (new Admission())->getTable();
            $mobileRegex = '/^(?:\+91|91)?[6-9]\d{9}$/';

            // Required fields: keep empty as "" so required/regex fail.
            // Optional fields: keep empty as null so nullable passes.
            $input = $request->all();
            foreach (['mobile_no', 'parent_mobile_no', 'aadhar_card_no'] as $requiredField) {
                if (!array_key_exists($requiredField, $input) || $input[$requiredField] === null) {
                    $input[$requiredField] = '';
                }
            }
            foreach (['other_mobile_no', 'whatsapp_no'] as $optionalField) {
                if (array_key_exists($optionalField, $input) && trim((string) $input[$optionalField]) === '') {
                    $input[$optionalField] = null;
                }
            }

            $validator = Validator::make(
                $input,
                [
                    'mobile_no' => [
                        'required',
                        'string',
                        'regex:' . $mobileRegex,
                        Rule::unique($table, 'mobile_no')->ignore($student->id),
                    ],
                    'parent_mobile_no' => [
                        'required',
                        'string',
                        'regex:' . $mobileRegex,
                    ],
                    'other_mobile_no' => [
                        'nullable',
                        'regex:' . $mobileRegex,
                        Rule::unique($table, 'other_mobile_no')->ignore($student->id),
                    ],
                    'whatsapp_no' => [
                        'nullable',
                        'regex:' . $mobileRegex,
                        Rule::unique($table, 'whatsapp_no')->ignore($student->id),
                    ],
                    'aadhar_card_no' => [
                        'required',
                        'string',
                        'regex:/^[2-9][0-9]{11}$/',
                        Rule::unique($table, 'aadhar_card_no')->ignore($student->id),
                    ],
                    'temporary_address' => ['sometimes', 'nullable', 'string', 'max:255'],
                    'permanent_address' => ['sometimes', 'nullable', 'string', 'max:255'],
                    'occupation' => ['sometimes', 'nullable', 'string', 'max:255'],
                    'mother_occupation' => ['sometimes', 'nullable', 'string', 'max:255'],
                    'birth_place' => ['sometimes', 'nullable', 'string', 'max:255'],
                    'bank_name' => ['sometimes', 'nullable', 'string', 'max:255'],
                    'bank_account_no' => ['sometimes', 'nullable', 'string', 'max:255'],
                ],
                [
                    'mobile_no.required' => 'Mobile number is required.',
                    'mobile_no.regex' => 'Please enter a valid mobile number.',
                    'mobile_no.unique' => 'This mobile number is already registered.',
                    'parent_mobile_no.required' => 'Parent mobile number is required.',
                    'parent_mobile_no.regex' => 'Please enter a valid parent mobile number.',
                    'other_mobile_no.regex' => 'Please enter a valid other mobile number.',
                    'other_mobile_no.unique' => 'This other mobile number is already registered.',
                    'whatsapp_no.regex' => 'Please enter a valid WhatsApp number.',
                    'whatsapp_no.unique' => 'This WhatsApp number is already registered.',
                    'aadhar_card_no.required' => 'Aadhaar card number is required.',
                    'aadhar_card_no.regex' => 'Please enter a valid 12-digit Aadhaar card number.',
                    'aadhar_card_no.unique' => 'This Aadhaar card number is already registered.',
                ]
            );

            if ($validator->fails()) {
                return $this->sendError($validator->errors()->first(), $validator->errors(), [], 422);
            }

            // Strict whitelist — no academic / identity / status fields
            $allowed = [
                'mobile_no',
                'parent_mobile_no',
                'other_mobile_no',
                'whatsapp_no',
                'aadhar_card_no',
                'temporary_address',
                'permanent_address',
                'occupation',
                'mother_occupation',
                'birth_place',
                'bank_name',
                'bank_account_no',
            ];

            $updateParams = $request->only($allowed);
            if (empty($updateParams)) {
                return $this->sendError('No valid person fields to update.', [], [], 422);
            }

            foreach ($allowed as $field) {
                if (array_key_exists($field, $updateParams) && is_string($updateParams[$field])) {
                    $updateParams[$field] = trim($updateParams[$field]);
                    if ($updateParams[$field] === '') {
                        $updateParams[$field] = null;
                    }
                }
            }

            // Digits-only for phone / aadhar when provided
            foreach (['mobile_no', 'parent_mobile_no', 'other_mobile_no', 'whatsapp_no', 'aadhar_card_no', 'bank_account_no'] as $digitField) {
                if (array_key_exists($digitField, $updateParams) && $updateParams[$digitField] !== null) {
                    $updateParams[$digitField] = preg_replace('/\D+/', '', (string) $updateParams[$digitField]);
                }
            }

            $student->fill($updateParams);
            $student->save();

            $student->refresh();

            return $this->sendResponse(
                $this->buildPersonDetailsPayload($student),
                'Person details updated successfully.'
            );
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }

    /**
     * Person-details payload (read model). Academic keys are view-only.
     */
    protected function buildPersonDetailsPayload(Admission $student): array
    {
        $student->loadMissing(['currentStandard', 'academicYear']);

        $name = strtoupper(trim(preg_replace(
            '/\s+/',
            ' ',
            ($student->first_name ?? '') . ' ' . ($student->last_name ?? '') . ' ' . ($student->father_name ?? '')
        )));

        return [
            'id'                 => (string) $student->id,
            'admission_id'       => (string) ($student->gr_no ?? ''),
            'name'               => $name,
            'first_name'         => (string) ($student->first_name ?? ''),
            'last_name'          => (string) ($student->last_name ?? ''),
            'father_name'        => (string) ($student->father_name ?? ''),
            'mother_name'        => (string) ($student->mother_name ?? ''),
            'date_of_birth'      => (string) ($student->date_of_birth ?? ''),
            'birth_place'        => (string) ($student->birth_place ?? ''),
            'gender'             => (string) ($student->gender ?? ''),
            'religion'           => (string) ($student->religion ?? ''),
            'cast'               => (string) ($student->cast ?? ''),
            'category'           => (string) ($student->category ?? ''),
            'aadhar_card_no'     => (string) ($student->aadhar_card_no ?? ''),
            'pen_no'             => (string) ($student->pen_no ?? ''),
            'spid'               => (string) ($student->spid ?? ''),
            'apaar_id_abc_id'    => (string) ($student->apaar_id_abc_id ?? ''),
            'mobile_no'          => (string) ($student->mobile_no ?? ''),
            'parent_mobile_no'   => (string) ($student->parent_mobile_no ?? ''),
            'other_mobile_no'    => (string) ($student->other_mobile_no ?? ''),
            'whatsapp_no'        => (string) ($student->whatsapp_no ?? ''),
            'temporary_address'  => (string) ($student->temporary_address ?? ''),
            'permanent_address'  => (string) ($student->permanent_address ?? ''),
            'occupation'         => (string) ($student->occupation ?? ''),
            'mother_occupation'  => (string) ($student->mother_occupation ?? ''),
            'bank_name'          => (string) ($student->bank_name ?? ''),
            'bank_account_no'    => (string) ($student->bank_account_no ?? ''),
            'house'              => (string) ($student->house ?? ''),
            'profile_url'        => (string) ($student->profile_pic_url ?? ''),
            // view-only academic (not updatable via this API)
            'standard'           => optional($student->currentStandard)->name,
            'division'           => (string) ($student->division ?? ''),
            'roll_no'            => $student->roll_no,
            'stream'             => (string) ($student->stream ?? ''),
            'academic_year'      => optional($student->academicYear)->name,
            'status'             => (string) ($student->status ?? ''),
        ];
    }

}
