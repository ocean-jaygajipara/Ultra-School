<?php

namespace App\Http\Middleware;

use App\Services\SchoolDatabaseManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SwitchSchoolDatabaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Switch to the active school's database before executing request
        $activeCode = SchoolDatabaseManager::getActiveSchoolCode();
        SchoolDatabaseManager::switchDatabase($activeCode);

        return $next($request);
    }
}
