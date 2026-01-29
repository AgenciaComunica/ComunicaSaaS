<?php

namespace App\Http\Controllers;

use App\Models\UploadBatch;
use App\Models\User;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'userCount' => User::where('role', 'user')->count(),
            'batchCount' => UploadBatch::count(),
                    ]);
    }
}
