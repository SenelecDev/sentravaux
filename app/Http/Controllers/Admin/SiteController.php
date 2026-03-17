<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function index(Request $request)
    {
        $query = Site::withCount('demandes');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('libelle', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('ville', 'like', "%{$search}%")
                  ->orWhere('region', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $sites = $query->orderBy('libelle')->paginate(20);

        return view('admin.sites.index', compact('sites'));
    }

    public function show(Site $site)
    {
        $site->loadCount('demandes');
        $demandes = $site->demandes()->with('user')->latest()->paginate(20);

        return view('admin.sites.show', compact('site', 'demandes'));
    }
}
