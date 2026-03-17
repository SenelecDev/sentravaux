<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = User::whereNotNull('service')
            ->where('service', '!=', '')
            ->select('service')
            ->selectRaw('COUNT(*) as users_count')
            ->groupBy('service')
            ->orderBy('service');

        if ($request->filled('search')) {
            $query->where('service', 'like', '%' . $request->search . '%');
        }

        $services = $query->paginate(20);

        return view('admin.services.index', compact('services'));
    }

    public function show($service)
    {
        $users = User::where('service', $service)
            ->with('roles')
            ->orderBy('nom')
            ->paginate(20);

        return view('admin.services.show', compact('service', 'users'));
    }
}
