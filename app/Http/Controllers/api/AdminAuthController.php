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

/**
 * Admin / web-side helpers. Mobile app auth lives in AuthenticationController.
 */
class AdminAuthController extends Controller
{
    /**
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
}
