<?php

namespace App\Http\Controllers\api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\CourceRegistration;
use App\Models\Master\MasterCity;
use App\Models\Master\MasterCountry;
use App\Models\Master\MasterCourse;
use App\Models\Master\MasterPincode;
use App\Models\Master\MasterState;
use App\Models\SendOTP;
use App\Models\StudentDeviceToken;
use App\Models\User;
use App\Models\UserDetail;
use App\Services\SchoolDatabaseManager;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Passport\Token;
use Spatie\Permission\Models\Role;

class AuthenticationController extends Controller
{
    public function __construct(Request $request) {}

    /**
     * Register a new user
     *
     * Creates a new application user with phone number, password and optional referral code.
     * If `add_pincode` is true and the pincode does not yet exist, it will also create a pending pincode record.
     *
     * @group Authentication
     *
     * @bodyParam name string required The full name of the user. Example: Ramesh Parmar
     * @bodyParam phone string required The 10‑digit mobile number (must be unique). Example: 9876543210
     * @bodyParam password string required The login password (minimum 4 characters). Example: secret123
     * @bodyParam pincode string required The 6‑digit pincode. Example: 395006
     * @bodyParam add_pincode boolean Whether to also create a new pincode record (when it doesn’t exist). Example: true
     * @bodyParam country_id integer required when add_pincode=true The country ID for the pincode. Example: 101
     * @bodyParam state_id integer required when add_pincode=true The state ID for the pincode. Example: 4030
     * @bodyParam city_id integer required when add_pincode=true The city ID for the pincode. Example: 51234
     * @bodyParam referral_code string The referral code of an existing user. Example: ABCD1234
     *
     * @response 200 {
     *   "status": "true",
     *   "message": "User register successfully.",
     *   "data": []
     * }
     *
     * @response 403 scenario="Validation failed" {
     *   "status": "false",
     *   "message": "Validation Error.",
     *   "messages": {
     *     "phone": [
     *       "The phone has already been taken."
     *     ]
     *   }
     * }
     */
    public function register(Request $request)
    {
        DB::beginTransaction();
        try {
            // return implode(',', Role::whereNotIn('name', ['developer'])->get()->pluck('id')->toArray());
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255'], // Ensures name is a string and max 255 characters
                'phone' => ['required', 'digits:10', 'unique:' . ((new User())->getTable()) . ',phone'], // Must be exactly 10 digits and unique in users table
                'pincode' => ['required', 'digits:6'], // Ensures pincode is exactly 6 digits
                'add_pincode' => ['nullable', 'in:true,false'],

                // Make these fields required only if add_pincode is true
                'country_id' => [
                    'required:add_pincode,true',
                    'exists:' . ((new MasterCountry())->getTable()) . ',id'
                ], // Checks if country_id exists in states table
                'state_id' => [
                    'required:add_pincode,true',
                    'exists:' . ((new MasterState())->getTable()) . ',id'
                ], // Checks if state_id exists in states table
                'city_id' => [
                    'required:add_pincode,true',
                    'exists:' . ((new MasterCity())->getTable()) . ',id'
                ], // Checks if city_id exists in cities table
                'password' => ['required', 'string', 'min:4'], // Min 4 characters and requires password_confirmation field
                // , 'confirmed'
                'referral_code' => ['nullable', 'string', 'exists:' . (new User())->getTable() . ',referral_code'], // Optional, but if given, must be a string max 10 chars

            ], [
                'city_id.required' => 'The city field is required.',
                'state_id.required' => 'The state field is required.',
                'country_id.required' => 'The state field is required.'
            ]);


            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors()->first(), $validator->errors(), 403);
            }
            // return $request->all();

            $input = [];
            $input['name'] = $request->name;
            $input['email'] = ($request?->email) ? $request?->email : $request->phone . '@ocean.com';
            $input['phone'] = $request->phone;
            $input['password'] = Hash::make($request->password);
            $input['sp'] = Helper::generateString(4) . $request->password;
            $input['status'] = "active";
            // return $input;
            // $input['pincode'] = $request->pincode;
            $adminUser = User::create($input);

            $checkPincodeExist = MasterPincode::where('pincode', $request->pincode)->first();

            /** Add user Extra Detail */
            if ($adminUser) {
                $adminUserDetail = new UserDetail();
                $adminUserDetail->user_id = $adminUser?->id;
                $adminUserDetail->applied_referral_code = $request?->referral_code ?? null;
                $adminUserDetail->gender = $request?->gender ?? null;
                $adminUserDetail->date_of_birth = $request?->date_of_birth ?? null;
                $adminUserDetail->country_id = $request?->country_id;
                $adminUserDetail->state_id = $request?->state_id;
                $adminUserDetail->city_id = $request?->city_id;
                $adminUserDetail->pincode = $request?->pincode;
                $adminUserDetail->save();
            }


            if ($request?->add_pincode && !$checkPincodeExist) {
                $newPincode = [];
                $newPincode['pincode'] = $request->pincode ?? null;
                $newPincode['city_id'] = $request->city_id ?? null;
                $newPincode['state_id'] = $request->state_id ?? null;
                $newPincode['country_id'] = $request->country_id ?? null;
                $newPincode['status'] = 'inactive';
                $newPincode['created_by'] = $adminUser?->id;
                MasterPincode::create($newPincode);
            }
            DB::commit();
            return $this->sendResponse([], 'User register successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage(), [], [], 201);
        }
    }

    // public function studentSendOtp(Request $request)
    // {
    //     try {
    //         $validator = Validator::make($request->all(), [
    //             'admission_id' => [
    //                 'required',
    //                 'integer',
    //                 function ($attribute, $value, $fail) {
    //                     if (!Admission::where('id', $value)->exists()) {
    //                         $fail('This admission_id is not registered.');
    //                     }
    //                 }
    //             ],
    //             'mobile_no' => [
    //                 'required',
    //                 'digits_between:10,15',
    //                 function ($attribute, $value, $fail) {
    //                     if (!Admission::where('mobile_no', $value)->exists()) {
    //                         $fail('This phone number is not registered.');
    //                     }
    //                 }
    //             ],
    //             'course_id' => [
    //                 'required',
    //                 'integer',
    //                 function ($attribute, $value, $fail) use ($request) {
    //                     $adminUser = Admission::where('mobile_no', $request->mobile_no)->first();
    //                     if (!$adminUser) {
    //                         return;
    //                     }

    //                     $exists = CourceRegistration::where('register_id', $adminUser->id)
    //                         ->where('course_id', $value)
    //                         ->exists();

    //                     if (!$exists) {
    //                         $fail("You are not registered for the selected course.");
    //                     }
    //                 }
    //             ],
    //         ]);
    //         if ($validator->fails()) {
    //             return $this->sendError($validator->errors()->first(), $validator->errors(), [], 422);
    //         }
    //         // dd('hello');

    //         $adminUser = Admission::where('id', $request->admission_id)
    //             ->where('mobile_no', $request->mobile_no)
    //             ->first();
    //         if (!$adminUser) {
    //             return $this->sendError('This student not found. pleasce check the admission id and mobile number.', [], [], 422);
    //         }

    //         $studenExitsInCourse = CourceRegistration::where('register_id', $adminUser?->id)->where('course_id', $request->course_id)->first();
    //         if (!$studenExitsInCourse) {
    //             return $this->sendError('Student not register in this course', [], [], 0);
    //         }

    //         $message = "OTP has been sent successfully.";

    //         resend_otp:
    //         $checkSendedOtp = SendOTP::where("model", Admission::class)->where("model_id", $adminUser?->id)->where('sms_type', 'otp')->whereNull('verified_at')->where('status', 'send')->orderBy('id', 'DESC')->first();

    //         $otp = Helper::generateNumericOTP();

    //         if (!$checkSendedOtp) {
    //             $insertArray = [];
    //             $insertArray['model'] = Admission::class;
    //             $insertArray['model_id'] = $adminUser?->id;
    //             $insertArray['send_to'] = $adminUser?->mobile_no;
    //             $insertArray['verified_content'] = $otp;
    //             $insertArray['expire_time'] = Carbon::now()->addMinutes(10);
    //             $insertArray['sms_type'] = 'otp';
    //             $insertArray['sms'] = 'Your One time OTP is ' . $otp;
    //             $insertArray['created_from'] = 'AuthenticationController => login';

    //             $checkSendedOtp = SendOTP::create($insertArray);
    //             // dd("L-185", $checkSendedOtp, $insertArray);
    //         }

    //         if ($checkSendedOtp?->expire_time) {
    //             // Assume $otpRecord is your OTP record (from DB or elsewhere)
    //             $expireTime = Carbon::parse($checkSendedOtp->expire_time);

    //             // Compare with current time
    //             if (Carbon::now()->greaterThan($expireTime)) {
    //                 $message = "OTP has expired. send new OTP pleasce check.";
    //                 $checkSendedOtp->status = 'expire';
    //                 $checkSendedOtp->save();
    //                 goto resend_otp;
    //                 // return response()->json(['message' => 'OTP has expired'], 400);
    //             }
    //         }
    //         $otp = $checkSendedOtp?->verified_content;

    //         // dd("L-203", $checkSendedOtp->toArray(), $otp);

    //         $responseData = [
    //             'admission_id'     => $adminUser->id,
    //             'mobile_no'   => $adminUser->mobile_no,
    //             'course_id'   => $request->course_id,
    //             'course_name' => $studenExitsInCourse?->course?->course_name,
    //             'otp'         => $otp,
    //         ];

    //         $message = $message . ' OTP: ' . $otp;
    //         return $this->sendResponse($responseData, $message);
    //     } catch (\Exception $e) {
    //         return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
    //     }
    // }

    /**
     * Student login via birth year or OTP
     *
     * If the submitted birth year matches the student’s date of birth, this endpoint logs the student in directly and
     * returns an access token and profile. If it doesn’t match, it sends a 4‑digit OTP to the registered mobile number
     * so the student can complete login using the `/student/verify-otp` endpoint.
     *
     * @group Authentication
     *
     * @bodyParam admission_id integer required The student admission ID. Example: 123
     * @bodyParam mobile_no string required The registered mobile number (10–15 digits). Example: 9876543210
     * @bodyParam birth_year integer required The student birth year (YYYY). Example: 2005
     * @bodyParam device_token string The FCM device token for push notifications. Example: fcm_token_123
     * @bodyParam device_details string Additional information about the device (model, OS, etc). Example: Android 14; OnePlus 11
     *
     * @response 200 scenario="Birth year matched (direct login)" {
     *   "status": "true",
     *   "message": "Login successful.",
     *   "data": {
     *     "id": "123",
     *     "first_name": "Raj",
     *     "last_name": "Patel",
     *     "mobile_no": "9876543210",
     *     "token": "eyJ0eXAiOiJKV1QiLCJh...",
     *     "expiresAt": "2026-03-13T10:00:00Z",
     *     "login_type": "birth_year"
     *   }
     * }
     *
     * @response 200 scenario="Birth year did not match (OTP sent)" {
     *   "status": "true",
     *   "message": "Please try again. Birth year does not match.",
     *   "data": {
     *     "admission_id": 123,
     *     "mobile_no": "9876543210"
     *   }
     * }
     *
     * @response 422 scenario="Validation failed" {
     *   "status": "false",
     *   "message": "The admission id field is required.",
     *   "messages": {
     *     "admission_id": [
     *       "The admission id field is required."
     *     ]
     *   }
     * }
     */
    public function studentSendOtp(Request $request)
    {
        try {
            // 🔹 VALIDATION
            $validator = Validator::make($request->all(), [
                'admission_id' => 'required|integer',
                'mobile_no'    => 'required|digits_between:10,15',
                'device_token'   => 'nullable|string',
                'device_details' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->sendError(
                    $validator->errors()->first(),
                    $validator->errors(),
                    [],
                    422
                );
            }

            // 🔹 FIND STUDENT
            $student = Admission::where('id', $request->admission_id)
                ->where('mobile_no', $request->mobile_no)
                ->first();

            if (!$student) {
                return $this->sendError(
                    'Student not found.',
                    [],
                    [],
                    422
                );
            }

            if (in_array(strtolower($student->status), ['cancel', 'cancelled'])) {
                return $this->sendError(
                    'Your account has been closed.',
                    [],
                    [],
                    422
                );
            }

            //🔹 STORE DEVICE TOKEN & DETAILS
            if ($request->filled('device_token') || $request->filled('device_details')) {
                if ($request->filled('device_token')) {
                    StudentDeviceToken::where('device_token', $request->device_token)
                        ->where('student_id', '!=', $student->id)
                        ->delete();
                }

                StudentDeviceToken::updateOrCreate(
                    [
                        'student_id'   => $student->id,
                        'device_token' => $request->device_token,
                    ],
                    [
                        'device_details' => $request->device_details,
                        'created_by'     => $student->id,
                    ]
                );
            }

            $message = "OTP has been sent successfully.";

            resend_otp:
            $checkSendedOtp = SendOTP::where("model", Admission::class)
                ->where("model_id", $student->id)
                ->where('sms_type', 'otp')
                ->whereNull('verified_at')
                ->where('status', 'send')
                ->orderBy('id', 'DESC')
                ->first();

            $otp = Helper::generateNumericOTP(6);

            if (!$checkSendedOtp) {
                $checkSendedOtp = SendOTP::create([
                    'model'            => Admission::class,
                    'model_id'         => $student->id,
                    'send_to'          => $student->mobile_no,
                    'verified_content' => $otp,
                    'expire_time'      => Carbon::now()->addMinutes(10),
                    'sms_type'         => 'otp',
                    'sms'              => 'Your One time OTP is ' . $otp,
                    'created_from'     => 'AuthenticationController => login',
                ]);
            }

            if (Carbon::now()->greaterThan(Carbon::parse($checkSendedOtp->expire_time))) {
                // dd("Hello");
                $checkSendedOtp->status = 'expire';
                $checkSendedOtp->save();
                goto resend_otp;
            }

            return $this->sendResponse([
                'admission_id' => $student->id,
                'mobile_no'    => $student->mobile_no,
                'otp'          => (string) $checkSendedOtp->verified_content,
            ], $message);
        } catch (\Exception $e) {
            return $this->sendError(
                'Something went wrong.',
                $e->getMessage(),
                [],
                500
            );
        }
    }

    /**
     * Verify OTP and log student in
     *
     * Verifies the 4‑digit OTP previously sent to the student’s mobile number. On success, it revokes any previous
     * tokens, issues a new Passport access token, stores device information (if provided) and returns the student
     * profile along with the token.
     *
     * @group Authentication
     *
     * @bodyParam mobile_no string required The registered mobile number that received the OTP. Example: 9876543210
     * @bodyParam otp string required The 4‑digit OTP sent to the mobile number. Example: 1234
     * @bodyParam device_token string The FCM device token for push notifications. Example: fcm_token_123
     * @bodyParam device_details string Additional information about the device (model, OS, etc). Example: Android 14; OnePlus 11
     *
     * @response 200 {
     *   "status": "true",
     *   "message": "OTP verified successfully.",
     *   "data": {
     *     "id": "123",
     *     "first_name": "Raj",
     *     "last_name": "Patel",
     *     "mobile_no": "9876543210",
     *     "token": "eyJ0eXAiOiJKV1QiLCJh...",
     *     "expiresAt": "2026-03-13T10:00:00Z"
     *   }
     * }
     *
     * @response 422 scenario="Invalid or expired OTP" {
     *   "status": "false",
     *   "message": "OTP not matched.",
     *   "messages": {
     *     "otp": [
     *       "OTP not matched."
     *     ]
     *   }
     * }
     */
    public function studentVerifyOtp(Request $request)
    {
        try {

            // Validate request
            $validator = Validator::make($request->all(), [
                'mobile_no' => [
                    'required',
                    'digits_between:10,15',
                    function ($attribute, $value, $fail) {
                        if (!Admission::where('mobile_no', $value)->exists()) {
                            $fail('This phone number is not registered.');
                        }
                    }
                ],
                'otp' => [
                    'required',
                    'digits:6',
                    function ($attribute, $value, $fail) use ($request) {
                        $checkOTP = SendOTP::where('send_to', $request->mobile_no)
                            ->where('verified_content', $value)
                            ->where('sms_type', 'otp')
                            ->where('status', 'send')
                            ->first();

                        if (!$checkOTP) {
                            $fail("The OTP you entered is incorrect.");
                        } elseif ($checkOTP->verified_at) {
                            $fail("OTP used before. Resend and try again.");
                        } elseif (Carbon::now()->greaterThan(Carbon::parse($checkOTP->expire_time))) {
                            $fail("Your OTP has expired. Resend and login.");
                        }
                    }
                ],
                'device_token' => 'nullable|string',
                'device_details' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                $firstError = $validator->errors()->first();
                if ($validator->errors()->has('otp') && $firstError === "The OTP you entered is incorrect.") {
                    $firstError = "The OTP you entered is incorrect. Please try again..";
                }
                return $this->sendError($firstError, $validator->errors(), [], 422);
            }

            // Get student
            $student = Admission::where('mobile_no', $request->mobile_no)->first();

            if (!$student) {
                return $this->sendError('User not found.', ['mobile_no' => ['User not found.']], [], 404);
            }

            if (in_array(strtolower($student->status), ['cancel', 'cancelled'])) {
                return $this->sendError(
                    'Your account has been cancelled. Please contact admin.',
                    [],
                    [],
                    422
                );
            }

            // Mark OTP as verified
            $checkOTP = SendOTP::where('send_to', $request->mobile_no)
                ->where('sms_type', 'otp')
                ->whereNull('verified_at')
                ->orderBy('id', 'DESC')
                ->first();

            if ($checkOTP) {
                $checkOTP->verified_at = Carbon::now();
                $checkOTP->status = "expire";
                $checkOTP->save();
            }

            // Revoke all previous tokens (Commented out to allow multiple device login)
            // Token::where('user_id', $student->id)
            //     ->update(['revoked' => true]);

            // Create new Passport token
            $tokenResult = $student->createToken('student-api');
            $token = $tokenResult->accessToken;
            $expiresAt = $tokenResult->token->expires_at;


            if ($request->filled('device_token') || $request->filled('device_details')) {
                if ($request->filled('device_token')) {
                    StudentDeviceToken::where('device_token', $request->device_token)
                        ->where('student_id', '!=', $student->id)
                        ->delete();
                }

                StudentDeviceToken::updateOrCreate(
                    [
                        'student_id'   => $student->id,
                        'device_token' => $request->device_token,
                    ],
                    [
                        'device_details' => $request->device_details,
                        'created_by'     => $student->id,
                    ]
                );
            }

            // Get student profile
            $getProfileRequest = new Request();
            $getProfileRequest->admission_id = $student->id;
            $getProfile = (new AuthenticatedController())->student_profile_response($getProfileRequest);
            $getProfile->token = $token;
            $getProfile->expiresAt = $expiresAt;
            $getProfile->device_token = $request->device_token ?? null;
            $getProfile->device_details = $request->device_details ?? null;

            return $this->sendResponse($getProfile, 'OTP verified successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }

    /**
     * Logout current student
     *
     * Revokes the currently authenticated student’s access token and deletes all stored device tokens for that student.
     *
     * @group Authentication
     * @authenticated
     *
     * @response 200 {
     *   "status": "true",
     *   "message": "Logged out successfully.",
     *   "data": []
     * }
     *
     * @response 401 scenario="Not authenticated" {
     *   "status": "false",
     *   "message": "User not authenticated.",
     *   "data": []
     * }
     */
    public function studentLogout(Request $request)
    {
        try {
            // Get logged-in student (from token)
            $student = auth()->user();

            if (!$student) {
                return $this->sendError('User not authenticated.', [], [], 401);
            }

            // Revoke current token only
            $request->user()->token()->revoke();

            // Delete current device token if provided, otherwise delete all for safety
            if ($request->filled('device_token')) {
                StudentDeviceToken::where('student_id', $student->id)
                    ->where('device_token', $request->device_token)
                    ->delete();
            } else {
                StudentDeviceToken::where('student_id', $student->id)->delete();
            }

            return $this->sendResponse([], 'Logged out successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }

    /**
     * Store student device token for push notifications
     */
    public function storeDeviceToken(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'student_id' => [
                    'required',
                    'integer',
                    Rule::exists(Admission::class, 'id'),
                ],
                'device_token' => ['required', 'string', 'max:255'],
                'device_details' => ['nullable', 'string'],
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors()->first(), $validator->errors(), 422);
            }

            $studentId = $request->student_id;

            // Delete existing token if registered to another student to avoid Duplicate key crash
            StudentDeviceToken::where('device_token', $request->device_token)
                ->where('student_id', '!=', $studentId)
                ->delete();

            // Check if token already exists
            $existingToken = StudentDeviceToken::where('student_id', $studentId)
                ->where('device_token', $request->device_token)
                ->first();

            if (!$existingToken) {
                $deviceToken = new StudentDeviceToken();
                $deviceToken->student_id = $studentId;
                $deviceToken->device_token = $request->device_token;
                $deviceToken->device_details = $request->device_details ?? null;
                $deviceToken->created_by = $studentId;
                $deviceToken->save();
            }

            return $this->sendResponse([], 'Device token stored successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }

    /**
     * App: POST /api/send-otp
     * Body: school_id, mobile, admission_id (id or GR), fcm_token
     */
    public function appSendOtp(Request $request)
    {
        try {
            $this->normalizeAppAuthRequest($request);

            $validator = Validator::make($request->all(), [
                'school_id'    => 'required|string',
                'mobile'       => 'required|digits_between:10,15',
                'admission_id' => 'required',
                'fcm_token'    => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->errors()->first(), $validator->errors(), [], 422);
            }

            $schoolKey = strtolower(trim((string) $request->input('school_id')));
            if (!SchoolDatabaseManager::exists($schoolKey)) {
                return $this->sendError('Invalid school selected.', [], [], 422);
            }

            SchoolDatabaseManager::setActiveSchool($schoolKey);

            $mobile = preg_replace('/\D+/', '', (string) $request->input('mobile'));
            $admissionKey = trim((string) $request->input('admission_id'));
            $student = $this->findAppStudent($admissionKey, $mobile);

            if (($student['error'] ?? null) === 'mobile_mismatch') {
                return $this->sendError('Mobile number does not match this student record.', [], [], 422);
            }
            if (($student['error'] ?? null) === 'inactive') {
                return $this->sendError('Your account is inactive. Please contact the school administration.', [], [], 422);
            }
            if (empty($student['student'])) {
                return $this->sendError('Student not found.', [], [], 422);
            }

            /** @var Admission $admission */
            $admission = $student['student'];

            if ($this->isAppStudentInactive($admission)) {
                return $this->sendError('Your account is inactive. Please contact the school administration.', [], [], 422);
            }

            $deviceToken = trim((string) $request->input('fcm_token', ''));
            if ($deviceToken !== '') {
                StudentDeviceToken::where('device_token', $deviceToken)
                    ->where('student_id', '!=', $admission->id)
                    ->delete();

                StudentDeviceToken::updateOrCreate(
                    ['student_id' => $admission->id, 'device_token' => $deviceToken],
                    ['created_by' => $admission->id]
                );
            }

            resend_otp:
            $checkSendedOtp = SendOTP::where('model', Admission::class)
                ->where('model_id', $admission->id)
                ->where('sms_type', 'otp')
                ->whereNull('verified_at')
                ->where('status', 'send')
                ->orderByDesc('id')
                ->first();

            $otp = Helper::generateNumericOTP(6);

            if (!$checkSendedOtp) {
                $checkSendedOtp = SendOTP::create([
                    'model'            => Admission::class,
                    'model_id'         => $admission->id,
                    'send_to'          => $admission->mobile_no,
                    'verified_content' => $otp,
                    'expire_time'      => Carbon::now()->addMinutes(10),
                    'sms_type'         => 'otp',
                    'sms'              => 'Your One time OTP is ' . $otp,
                    'status'           => 'send',
                    'created_from'     => 'AuthenticationController => appSendOtp',
                ]);
            }

            if (Carbon::now()->greaterThan(Carbon::parse($checkSendedOtp->expire_time))) {
                $checkSendedOtp->status = 'expire';
                $checkSendedOtp->save();
                goto resend_otp;
            }

            $school = SchoolDatabaseManager::get($schoolKey);

            return $this->sendResponse([
                'school'       => [
                    'code'       => $schoolKey,
                    'short_name' => $school['short_name'] ?? strtoupper($schoolKey),
                    'name'       => $school['name'] ?? $schoolKey,
                ],
                // App uses admission_id in place of gr_no
                'admission_id' => $admission->gr_no,
                'mobile_no'    => $admission->mobile_no,
                'otp'          => (string) $checkSendedOtp->verified_content,
                'otp_expire'   => optional($checkSendedOtp->expire_time)->toDateTimeString()
                    ?? Carbon::parse($checkSendedOtp->expire_time)->toDateTimeString(),
            ], 'OTP has been sent successfully.');
        } catch (\Throwable $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }

    /**
     * App: POST /api/verify-otp
     * Body: school_id, mobile, admission_id (app uses this instead of gr_no), otp, fcm_token
     */
    public function appVerifyOtp(Request $request)
    {
        try {
            $this->normalizeAppAuthRequest($request);

            $validator = Validator::make($request->all(), [
                'school_id'    => 'required|string',
                'mobile'       => 'required|digits_between:10,15',
                'admission_id' => 'required',
                'otp'          => 'required|digits_between:4,6',
                'fcm_token'    => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->errors()->first(), $validator->errors(), [], 422);
            }

            $schoolKey = strtolower(trim((string) $request->input('school_id')));
            if (!SchoolDatabaseManager::exists($schoolKey)) {
                return $this->sendError('Invalid school selected.', [], [], 422);
            }

            SchoolDatabaseManager::setActiveSchool($schoolKey);

            $mobile = preg_replace('/\D+/', '', (string) $request->input('mobile'));
            $admissionKey = trim((string) $request->input('admission_id'));
            $otp = trim((string) $request->input('otp'));

            $lookup = $this->findAppStudent($admissionKey, $mobile);
            if (($lookup['error'] ?? null) === 'mobile_mismatch') {
                return $this->sendError('Mobile number does not match this student record.', [], [], 422);
            }
            if (($lookup['error'] ?? null) === 'inactive') {
                return $this->sendError('Your account is inactive. Please contact the school administration.', [], [], 422);
            }
            if (empty($lookup['student'])) {
                return $this->sendError('Student not found for this admission_id.', [], [], 422);
            }

            /** @var Admission $admission */
            $admission = $lookup['student'];
            $sendTo = (string) ($admission->mobile_no ?: $mobile);

            if ($this->isAppStudentInactive($admission)) {
                return $this->sendError('Your account is inactive. Please contact the school administration.', [], [], 422);
            }

            $checkOTP = SendOTP::where('model', Admission::class)
                ->where('model_id', $admission->id)
                ->where('send_to', $sendTo)
                ->where('verified_content', $otp)
                ->where('sms_type', 'otp')
                ->where('status', 'send')
                ->orderByDesc('id')
                ->first();

            if (!$checkOTP) {
                return $this->sendError('The OTP you entered is incorrect. Please try again.', [], [], 422);
            }
            if ($checkOTP->verified_at) {
                return $this->sendError('OTP already used. Please resend OTP and try again.', [], [], 422);
            }
            if (Carbon::now()->greaterThan(Carbon::parse($checkOTP->expire_time))) {
                $checkOTP->status = 'expire';
                $checkOTP->save();

                return $this->sendError('Your OTP has expired. Please resend OTP and login.', [], [], 422);
            }

            $checkOTP->verified_at = Carbon::now();
            $checkOTP->status = 'expire';
            $checkOTP->save();

            $deviceToken = trim((string) $request->input('fcm_token', ''));
            if ($deviceToken !== '') {
                StudentDeviceToken::where('device_token', $deviceToken)
                    ->where('student_id', '!=', $admission->id)
                    ->delete();

                StudentDeviceToken::updateOrCreate(
                    ['student_id' => $admission->id, 'device_token' => $deviceToken],
                    ['created_by' => $admission->id]
                );
            }

            $tokenResult = $admission->createToken('student-api');
            $token = $tokenResult->accessToken;

            return response()->json([
                'status'  => 'true',
                'message' => 'OTP verified successfully.',
                'token'   => $token,
                'data'    => $this->buildAppStudentAuthData($admission, $schoolKey),
            ], 200);
        } catch (\Throwable $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }

    /**
     * App: POST /api/student/auth-detail  (auth required)
     * Already logged-in student — same response shape as verify-otp.
     */
    public function appStudentAuthDetail(Request $request)
    {
        try {
            /** @var Admission|null $admission */
            $admission = Auth::guard('student-api')->user();
            if (!$admission) {
                return $this->sendError('Unauthorization.', [], [], 401);
            }

            if ($this->isAppStudentInactive($admission)) {
                return $this->sendError('Your account is inactive. Please contact the school administration.', [], [], 422);
            }

            $schoolKey = SchoolDatabaseManager::getActiveSchoolCode()
                ?: strtolower(trim((string) (
                    $request->header('school_code')
                    ?: $request->input('school_code')
                    ?: $request->header('X-School-Code')
                    ?: $request->input('school_id')
                    ?: $request->input('school_key')
                    ?: ''
                )));

            $token = $request->bearerToken() ?: '';

            return response()->json([
                'status'  => 'true',
                'message' => 'Student details fetched successfully.',
                'token'   => $token,
                'data'    => $this->buildAppStudentAuthData($admission, $schoolKey),
            ], 200);
        } catch (\Throwable $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }

    /**
     * Shared student payload for verify-otp + student/auth-detail (Android same model).
     */
    protected function buildAppStudentAuthData(Admission $admission, string $schoolKey): array
    {
        $school = SchoolDatabaseManager::get($schoolKey) ?: [];
        $admission->loadMissing(['currentStandard', 'academicYear']);

        $profileRequest = new Request();
        $profileRequest->admission_id = $admission->id;
        $studentDetails = (array) (new AuthenticatedController())->student_profile_response($profileRequest);

        $name = strtoupper(trim(preg_replace(
            '/\s+/',
            ' ',
            ($admission->first_name ?? '') . ' ' . ($admission->last_name ?? '') . ' ' . ($admission->father_name ?? '')
        )));

        $profileLink = (string) ($admission->profile_pic_url ?? '');

        $studentDetails = array_merge($studentDetails, [
            'admission_id'      => $admission->gr_no,
            'name'              => $name,
            'roll_no'           => $admission->roll_no,
            'division'          => $admission->division,
            'stream'            => $admission->stream,
            'standard'          => optional($admission->currentStandard)->name,
            'standard_id'       => $admission->current_std_id,
            'academic_year'     => optional($admission->academicYear)->name,
            'academic_year_id'  => $admission->academic_year_id,
            'admission_date'    => $admission->admission_date,
            'pen_no'            => $admission->pen_no,
            'religion'          => $admission->religion,
            'house'             => $admission->house,
            'birth_place'       => $admission->birth_place,
            'profile_url'       => $profileLink,
            'status'            => (string) ($admission->status ?? ''),
        ]);

        $studentDetails['school'] = [
            'code'       => $schoolKey,
            'short_name' => $school['short_name'] ?? strtoupper($schoolKey),
            'name'       => $school['name'] ?? $schoolKey,
        ];

        return $studentDetails;
    }

    protected function normalizeAppAuthRequest(Request $request): void
    {
        // Flutter legacy keys → API keys (admission_id in place of gr_no)
        if ($request->filled('gr_no') && !$request->filled('admission_id')) {
            $request->merge(['admission_id' => $request->input('gr_no')]);
        }
        if ($request->filled('mobile_no') && !$request->filled('mobile')) {
            $request->merge(['mobile' => preg_replace('/\D+/', '', (string) $request->input('mobile_no'))]);
        }
        if ($request->filled('mobile')) {
            $request->merge(['mobile' => preg_replace('/\D+/', '', (string) $request->input('mobile'))]);
        }
        if ($request->filled('school_key') && !$request->filled('school_id')) {
            $request->merge(['school_id' => strtolower(trim((string) $request->input('school_key')))]);
        }
        if ($request->filled('device_token') && !$request->filled('fcm_token')) {
            $request->merge(['fcm_token' => (string) $request->input('device_token')]);
        }
    }

    /**
     * App sends admission_id in place of gr_no.
     * Resolve by gr_no first (app value), then by DB id.
     * Includes soft-deleted so inactive students get a clear message.
     *
     * @return array{student:?Admission,error:?string}
     */
    protected function findAppStudent(string $admissionKey, string $mobile): array
    {
        $student = null;

        // App admission_id is usually GR No.
        if ($admissionKey !== '') {
            $student = Admission::withTrashed()->where('gr_no', $admissionKey)->first();
        }

        // Fallback: real DB admission id
        if (!$student && $admissionKey !== '' && ctype_digit($admissionKey)) {
            $student = Admission::withTrashed()->where('id', (int) $admissionKey)->first();
        }

        if (!$student) {
            return ['student' => null, 'error' => null];
        }

        $registered = array_values(array_filter([
            preg_replace('/\D+/', '', (string) ($student->mobile_no ?? '')),
            preg_replace('/\D+/', '', (string) ($student->parent_mobile_no ?? '')),
            preg_replace('/\D+/', '', (string) ($student->whatsapp_no ?? '')),
        ]));

        $input = preg_replace('/\D+/', '', $mobile);
        if ($input === '' || !in_array($input, $registered, true)) {
            return ['student' => null, 'error' => 'mobile_mismatch'];
        }

        if ($this->isAppStudentInactive($student)) {
            return ['student' => $student, 'error' => 'inactive'];
        }

        return ['student' => $student, 'error' => null];
    }

    protected function isAppStudentInactive(Admission $admission): bool
    {
        if (method_exists($admission, 'trashed') && $admission->trashed()) {
            return true;
        }

        $status = strtolower(trim((string) ($admission->status ?? '')));

        return in_array($status, [
            'inactive',
            'in_active',
            'deactive',
            'deactivated',
            'cancel',
            'cancelled',
            'rejected',
            'left',
            'closed',
        ], true);
    }
}

