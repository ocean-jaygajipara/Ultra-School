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
