<?php

namespace App\Http\Controllers\api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\SendOTP;
use App\Models\StudentDeviceToken;
use App\Services\SchoolDatabaseManager;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminAuthController extends Controller
{
    /**
     * Step 1: School list for login screen.
     *
     * GET /api/schools
     */
    public function schools()
    {
        $schools = collect(SchoolDatabaseManager::all())
            ->map(function (array $school, string $code) {
                return [
                    'code'       => $code,
                    'short_name' => $school['short_name'] ?? strtoupper($code),
                    'name'       => $school['name'] ?? $code,
                ];
            })
            ->values();

        return $this->sendResponse($schools, 'Schools fetched successfully.');
    }

    /**
     * Step 2: Send OTP using GR No. + Mobile (send_otp table).
     *
     * POST /api/login/send-otp
     * Body: school_key, gr_no, mobile_no
     */
    public function sendOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'school_key'     => 'required|string',
                'gr_no'          => 'required|string|max:50',
                'mobile_no'      => 'required|digits_between:10,15',
                'device_token'   => 'nullable|string',
                'device_details' => 'nullable|string',
            ], [
                'school_key.required' => 'Please select a school.',
                'gr_no.required'      => 'GR No. is required.',
                'mobile_no.required'  => 'Mobile number is required.',
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->errors()->first(), $validator->errors(), [], 422);
            }

            $schoolKey = strtolower(trim((string) $request->input('school_key')));
            if (!SchoolDatabaseManager::exists($schoolKey)) {
                return $this->sendError('Invalid school selected.', [
                    'school_key' => ['Valid schools: ues, ups, uv, us'],
                ], [], 422);
            }

            SchoolDatabaseManager::setActiveSchool($schoolKey);

            $grNo = trim((string) $request->input('gr_no'));
            $mobile = trim((string) $request->input('mobile_no'));

            $student = Admission::query()
                ->where('gr_no', $grNo)
                ->where('mobile_no', $mobile)
                ->first();

            if (!$student) {
                return $this->sendError('Student not found for this GR No. and mobile number.', [], [], 422);
            }

            if (in_array(strtolower((string) $student->status), ['cancel', 'cancelled', 'rejected'], true)) {
                return $this->sendError('Your account has been closed. Please contact school.', [], [], 422);
            }

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

            resend_otp:
            $checkSendedOtp = SendOTP::where('model', Admission::class)
                ->where('model_id', $student->id)
                ->where('sms_type', 'otp')
                ->whereNull('verified_at')
                ->where('status', 'send')
                ->orderByDesc('id')
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
                    'status'           => 'send',
                    'created_from'     => 'AdminAuthController => sendOtp',
                ]);

                Helper::sendOtpSms($student->mobile_no, $otp);
            } else {
                Helper::sendOtpSms($student->mobile_no, $checkSendedOtp->verified_content);
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
                'admission_id' => $student->id,
                'gr_no'        => $student->gr_no,
                'mobile_no'    => $student->mobile_no,
                'otp_expire'   => optional($checkSendedOtp->expire_time)->toDateTimeString()
                    ?? Carbon::parse($checkSendedOtp->expire_time)->toDateTimeString(),
            ], 'OTP has been sent successfully.');
        } catch (\Throwable $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }

    /**
     * Step 3: Verify OTP and issue token.
     *
     * POST /api/login/verify-otp
     * Body: school_key, gr_no, mobile_no, otp
     */
    public function verifyOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'school_key'     => 'required|string',
                'gr_no'          => 'required|string|max:50',
                'mobile_no'      => 'required|digits_between:10,15',
                'otp'            => 'required|digits:6',
                'device_token'   => 'nullable|string',
                'device_details' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->errors()->first(), $validator->errors(), [], 422);
            }

            $schoolKey = strtolower(trim((string) $request->input('school_key')));
            if (!SchoolDatabaseManager::exists($schoolKey)) {
                return $this->sendError('Invalid school selected.', [], [], 422);
            }

            SchoolDatabaseManager::setActiveSchool($schoolKey);

            $grNo = trim((string) $request->input('gr_no'));
            $mobile = trim((string) $request->input('mobile_no'));
            $otp = trim((string) $request->input('otp'));

            $student = Admission::query()
                ->where('gr_no', $grNo)
                ->where('mobile_no', $mobile)
                ->first();

            if (!$student) {
                return $this->sendError('Student not found for this GR No. and mobile number.', [], [], 422);
            }

            if (in_array(strtolower((string) $student->status), ['cancel', 'cancelled', 'rejected'], true)) {
                return $this->sendError('Your account has been cancelled. Please contact school.', [], [], 422);
            }

            $checkOTP = SendOTP::where('model', Admission::class)
                ->where('model_id', $student->id)
                ->where('send_to', $mobile)
                ->where('verified_content', $otp)
                ->where('sms_type', 'otp')
                ->where('status', 'send')
                ->orderByDesc('id')
                ->first();

            if (!$checkOTP) {
                return $this->sendError('The OTP you entered is incorrect. Please try again.', [
                    'otp' => ['The OTP you entered is incorrect.'],
                ], [], 422);
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

            $tokenResult = $student->createToken('student-api');
            $token = $tokenResult->accessToken;
            $expiresAt = optional($tokenResult->token)->expires_at;

            $school = SchoolDatabaseManager::get($schoolKey);
            $name = strtoupper(trim(preg_replace(
                '/\s+/',
                ' ',
                ($student->first_name ?? '') . ' ' . ($student->last_name ?? '') . ' ' . ($student->father_name ?? '')
            )));

            return $this->sendResponse([
                'token'      => $token,
                'token_type' => 'Bearer',
                'expires_at' => $expiresAt ? $expiresAt->toDateTimeString() : null,
                'school'     => [
                    'code'       => $schoolKey,
                    'short_name' => $school['short_name'] ?? strtoupper($schoolKey),
                    'name'       => $school['name'] ?? $schoolKey,
                ],
                'student'    => [
                    'id'         => $student->id,
                    'gr_no'      => $student->gr_no,
                    'name'       => $name,
                    'mobile_no'  => $student->mobile_no,
                    'standard'   => $student->current_std ?? null,
                    'division'   => $student->division ?? null,
                ],
            ], 'OTP verified successfully.');
        } catch (\Throwable $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }

    /**
     * Authenticated student profile.
     *
     * GET/POST /api/me
     * Header: Authorization: Bearer {token}
     * Header: X-School-Code: ues|ups|uv|us
     */
    public function me(Request $request)
    {
        $student = $request->user('student-api') ?: Auth::guard('student-api')->user();
        if (!$student) {
            return $this->sendError('Unauthorized.', [], [], 401);
        }

        $schoolKey = SchoolDatabaseManager::getActiveSchoolCode();
        $school = SchoolDatabaseManager::get($schoolKey);
        $name = strtoupper(trim(preg_replace(
            '/\s+/',
            ' ',
            ($student->first_name ?? '') . ' ' . ($student->last_name ?? '') . ' ' . ($student->father_name ?? '')
        )));

        return $this->sendResponse([
            'school'  => [
                'code'       => $schoolKey,
                'short_name' => $school['short_name'] ?? strtoupper($schoolKey),
                'name'       => $school['name'] ?? $schoolKey,
            ],
            'student' => [
                'id'        => $student->id,
                'gr_no'     => $student->gr_no,
                'name'      => $name,
                'mobile_no' => $student->mobile_no,
                'standard'  => $student->current_std ?? null,
                'division'  => $student->division ?? null,
                'status'    => $student->status,
            ],
        ], 'Profile fetched successfully.');
    }

    /**
     * Logout current student token.
     *
     * POST /api/logout
     */
    public function logout(Request $request)
    {
        $student = $request->user('student-api') ?: Auth::guard('student-api')->user();
        if (!$student) {
            return $this->sendError('Unauthorized.', [], [], 401);
        }

        $token = $student->token();
        if ($token) {
            $token->revoke();
        }

        return $this->sendResponse([], 'Logged out successfully.');
    }
}
