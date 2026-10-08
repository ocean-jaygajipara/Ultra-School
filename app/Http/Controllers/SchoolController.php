<?php

namespace App\Http\Controllers;

use App\Services\SchoolDatabaseManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class SchoolController extends Controller
{
    /**
     * Switch active school database.
     */
    public function switchSchool(Request $request, string $code)
    {
        $code = strtolower(trim($code));

        if (!SchoolDatabaseManager::exists($code)) {
            return Redirect::back()->with('error', 'Invalid school selected.');
        }

        SchoolDatabaseManager::setActiveSchool($code);
        $school = SchoolDatabaseManager::getActiveSchool();

        return Redirect::route('software.dashboard')->with('success', 'Switched database to ' . $school['name'] . '!');
    }
}
