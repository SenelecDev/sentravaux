<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $roles = Role::withCount('users', 'permissions')->orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get();
        $totalUsers = User::count();

        return view('admin.roles.index', compact('roles', 'permissions', 'totalUsers'));
    }

    public function show(Role $role)
    {
        $role->load('permissions');
        $users = User::role($role->name)->orderBy('nom')->paginate(20);
        $allPermissions = Permission::orderBy('name')->get();

        return view('admin.roles.show', compact('role', 'users', 'allPermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->syncPermissions($request->input('permissions', []));

        return back()->with('success', 'Permissions du rôle "' . $role->name . '" mises à jour.');
    }
}
