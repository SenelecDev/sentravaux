<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'users_count' => \App\Models\User::count(),
            'active_users' => \App\Models\User::where('is_active', true)->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
