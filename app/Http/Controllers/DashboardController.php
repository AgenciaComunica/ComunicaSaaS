<?php

namespace App\Http\Controllers;

use App\Models\UploadBatch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function stopImpersonate(): RedirectResponse
    {
        $impersonatorId = request()->session()->pull('impersonator_id');
        if ($impersonatorId) {
            $admin = \App\Models\User::find($impersonatorId);
            if ($admin) {
                auth()->login($admin);
                request()->session()->regenerate();
            }
        }

        return redirect()->route('admin.dashboard');
    }

    public function index(): View|RedirectResponse
    {
        $user = request()->user();
        if ($user && $user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $year = (int) request()->query('year', now()->year);
        $month = (int) request()->query('month', now()->month);

        $months = collect(range(1, 12))->map(fn ($m) => [
            'value' => $m,
            'label' => Carbon::create(null, $m, 1)->locale('pt_BR')->translatedFormat('F'),
        ]);
        $years = collect(range(now()->year - 1, now()->year + 1));

        return view('dashboard', [
            'year' => $year,
            'month' => $month,
            'months' => $months,
            'years' => $years,
            'kpis' => [
                'social_reach' => 48231,
                'social_impressions' => 128904,
                'social_profile_views' => 6123,
                'social_clicks' => 1840,
                'site_sessions' => 22315,
                'site_users' => 17642,
                'site_pageviews' => 58120,
                'site_bounce_rate' => 0.41,
                'ad_spend' => 9140.75,
                'paid_conversations' => 412,
                'leads_frio' => 142,
                'leads_quente' => 86,
                'leads_muito_quente' => 34,
                'leads_sem_temp' => 19,
                'sales' => 27,
                'revenue' => 143280.50,
                'conversion_rate' => 0.065,
                'ticket' => 5306.69,
                'roi' => 8.6,
                'roas' => 9.6,
            ],
        ]);
    }
}
