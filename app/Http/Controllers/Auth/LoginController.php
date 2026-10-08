<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Services\SchoolDatabaseManager;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::DASHBOARD;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    public function showLoginForm(Request $request)
    {
        $schools = SchoolDatabaseManager::all();
        $selectedSchool = $request->query('school', null);

        return view('software.auth.login', compact('schools', 'selectedSchool'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'school_key' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|min:4',
        ], [
            'school_key.required' => 'Please select a school to login.',
        ]);

        $schoolKey = strtolower(trim($request->input('school_key')));
        if (!SchoolDatabaseManager::exists($schoolKey)) {
            return Redirect::back()->withInput()->withErrors(['school_key' => 'Invalid school selected.']);
        }

        // Switch to the chosen school database before attempting authentication
        SchoolDatabaseManager::switchDatabase($schoolKey);

        if (Auth::attempt($request->only('email', 'password'), $request->filled('remember'))) {
            $user = Auth::user();

            if (in_array(Helper::getLoginUserRole(), Helper::getApplicationUserRoles()->pluck('name')->toArray())) {
                Auth::logout();
                return Redirect::back()->withInput()->withErrors(['email' => 'You are not authorized to login as admin.']);
            }

            if ($user->status == "inactive") {
                Auth::logout();
                return Redirect::back()->withInput()->withErrors(["email" => "Contact admin. Your account is inactive."]);
            }

            // Store active school in session
            SchoolDatabaseManager::setActiveSchool($schoolKey);

            $schoolInfo = SchoolDatabaseManager::getActiveSchool();
            return Redirect::route('software.dashboard')->with('success', 'Logged in successfully to ' . $schoolInfo['name'] . '!');
        }

        return Redirect::back()->withInput()->withErrors(["email" => "Invalid credentials for the selected school."]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route("login");
    }
}
