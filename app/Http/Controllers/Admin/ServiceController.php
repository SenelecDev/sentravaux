<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Service::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('libelle', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('centre_responsabilite', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $services = $query->orderBy('libelle')->paginate(20);

        // Utilisateurs liés par le champ texte users.service (legacy)
        foreach ($services as $service) {
            $service->users_count = User::where(function ($q) use ($service) {
                $q->where('service', $service->libelle);
                if ($service->code) {
                    $q->orWhere('service', $service->code);
                }
            })->count();
        }

        return view('admin.services.index', compact('services'));
    }

    public function show($service)
    {
        $serviceModel = Service::where('libelle', $service)
            ->orWhere('code', $service)
            ->orWhere('id', $service)
            ->firstOrFail();

        $users = User::where(function ($q) use ($serviceModel) {
            $q->where('service', $serviceModel->libelle);
            if ($serviceModel->code) {
                $q->orWhere('service', $serviceModel->code);
            }
        })
            ->with('roles')
            ->orderBy('nom')
            ->paginate(20);

        return view('admin.services.show', [
            'service' => $serviceModel->libelle,
            'serviceModel' => $serviceModel,
            'users' => $users,
        ]);
    }
}
