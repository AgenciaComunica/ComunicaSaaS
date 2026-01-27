<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\UploadBatch;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $latestBatch = UploadBatch::latest('year')->latest('month')->first();

        return view('dashboard', [
            'latestBatch' => $latestBatch,
            'batchCount' => UploadBatch::count(),
            'leadCount' => Lead::count(),
        ]);
    }
}
