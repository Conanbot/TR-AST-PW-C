<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    private function validateAndGetPeriod(Request $request, ReportService $service): array
    {
        $validated = $request->validate([
            'preset' => ['nullable', 'string', 'in:today,yesterday,last_7_days,last_30_days,this_month,last_month,custom'],
            'start_date' => ['nullable', 'required_if:preset,custom', 'date'],
            'end_date' => ['nullable', 'required_if:preset,custom', 'date', 'after_or_equal:start_date'],
        ]);

        return $service->parsePeriod(
            $validated['preset'] ?? 'last_30_days',
            $validated['start_date'] ?? null,
            $validated['end_date'] ?? null
        );
    }

    public function index(): RedirectResponse
    {
        return redirect()->route('admin.reports.sales');
    }

    public function sales(Request $request, ReportService $service): View
    {
        $period = $this->validateAndGetPeriod($request, $service);
        $data = $service->getSalesReport($period, 15);

        return view('admin.reports.sales', compact('data', 'period'));
    }

    public function products(Request $request, ReportService $service): View
    {
        $period = $this->validateAndGetPeriod($request, $service);
        $data = $service->getProductSalesReport($period, 20);

        return view('admin.reports.products', compact('data', 'period'));
    }

    public function payments(Request $request, ReportService $service): View
    {
        $period = $this->validateAndGetPeriod($request, $service);
        $data = $service->getPaymentReport($period, 20);

        return view('admin.reports.payments', compact('data', 'period'));
    }

    public function promotions(Request $request, ReportService $service): View
    {
        $period = $this->validateAndGetPeriod($request, $service);
        $data = $service->getPromotionReport($period, 20);

        return view('admin.reports.promotions', compact('data', 'period'));
    }

    public function inventory(Request $request, ReportService $service): View
    {
        $period = $this->validateAndGetPeriod($request, $service);
        $data = $service->getInventoryReport($period, 20);

        return view('admin.reports.inventory', compact('data', 'period'));
    }

    public function purchasing(Request $request, ReportService $service): View
    {
        // Purchasing feature removed — redirect to inventory report
        return redirect()->route('admin.reports.inventory');
    }

    public function customers(Request $request, ReportService $service): View
    {
        $period = $this->validateAndGetPeriod($request, $service);
        $data = $service->getCustomerReport($period, 20);

        return view('admin.reports.customers', compact('data', 'period'));
    }
}
