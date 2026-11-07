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
        // Prefer explicit school from API header/body, then session/default.
        $requestedCode = $request->header('X-School-Code')
            ?? $request->header('School-Code')
            ?? $request->input('school_key')
            ?? $request->input('school_code');

        if (is_string($requestedCode) && SchoolDatabaseManager::exists($requestedCode)) {
            SchoolDatabaseManager::setActiveSchool($requestedCode);
        }

        $activeCode = SchoolDatabaseManager::getActiveSchoolCode();
        SchoolDatabaseManager::switchDatabase($activeCode);

        $route = (string) ($request->route()?->getName() ?? '');
        $onFeeStatusReport = str_starts_with($route, 'fee-receipt-entry.paid-report')
            || str_starts_with($route, 'fee-receipt-entry.unpaid-report');
        if (!$onFeeStatusReport) {
            session()->forget([
                'fee_receipt_paid_report.' . $activeCode,
                'fee_receipt_unpaid_report.' . $activeCode,
            ]);
        }

        return $next($request);
    }
}
