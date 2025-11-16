<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Models\RefreshToken;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // Filters
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Pagination
        $perPage = min($request->get('per_page', 20), 50);
        $users = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return view('admin.users.index', compact('users'));
    }

    public function show(string $id)
    {
        $user = User::with(['properties', 'bookings', 'proposals', 'reviews'])->findOrFail($id);
        return view('admin.users.show', compact('user'));
    }

    public function create()
    {
        $canCreateAdmin = auth()->user()->isSuperAdmin();
        return view('admin.users.create', compact('canCreateAdmin'));
    }

    public function store(Request $request)
    {
        // Only super admin can create admin users
        $allowedRoles = auth()->user()->isSuperAdmin() 
            ? ['tenant', 'owner', 'admin'] 
            : ['tenant', 'owner'];

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in($allowedRoles)],
            'phone_number' => [
                'required',
                'string',
                'regex:/^09[0-9]{8}$/',
                'unique:users,phone_number'
            ],
            'region' => ['required', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        // Double check: Regular admins cannot create admin users
        if (!auth()->user()->isSuperAdmin() && $validated['role'] === 'admin') {
            return back()->with('error', 'Only super admin can create admin users');
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'phone_number' => $validated['phone_number'],
            'region' => $validated['region'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLogger::record(
            'user.created',
            [
                'entity_type' => 'user',
                'entity_id' => $user->id,
                'entity_name' => $user->name,
            ],
            null,
            $validated,
            ['email' => $user->email, 'role' => $user->role]
        );

        return redirect()->route('admin.users.index')
            ->with('status', __('admin.user_created'));
    }

    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        
        // Check if current user can manage this user
        if (!auth()->user()->canManage($user)) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You do not have permission to edit this user');
        }

        // Super admin cannot be edited
        if ($user->isSuperAdmin()) {
            return redirect()->route('admin.users.show', $user->id)
                ->with('error', 'Super admin cannot be edited');
        }

        $canEditRole = auth()->user()->isSuperAdmin();
        return view('admin.users.edit', compact('user', 'canEditRole'));
    }

    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        // Check if current user can manage this user
        if (!auth()->user()->canManage($user)) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You do not have permission to edit this user');
        }

        // Super admin cannot be edited
        if ($user->isSuperAdmin()) {
            return redirect()->route('admin.users.show', $user->id)
                ->with('error', 'Super admin cannot be edited');
        }

        // Only super admin can change roles to/from admin
        $allowedRoles = auth()->user()->isSuperAdmin() 
            ? ['tenant', 'owner', 'admin'] 
            : ['tenant', 'owner'];

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in($allowedRoles)],
            'phone_number' => [
                'required',
                'string',
                'regex:/^09[0-9]{8}$/',
                Rule::unique('users')->ignore($user->id)
            ],
            'region' => ['required', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        // Double check: Regular admins cannot change role to admin
        if (!auth()->user()->isSuperAdmin() && $validated['role'] === 'admin') {
            return back()->with('error', 'Only super admin can assign admin role');
        }

        $before = $user->only(['name', 'email', 'role', 'phone_number', 'region', 'is_active']);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        
        // Only super admin can change roles
        if (auth()->user()->isSuperAdmin()) {
            $user->role = $validated['role'];
        }
        
        $user->phone_number = $validated['phone_number'];
        $user->region = $validated['region'];
        $user->is_active = $request->boolean('is_active', $user->is_active);

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        $after = $user->only(['name', 'email', 'role', 'phone_number', 'region', 'is_active']);

        AuditLogger::record(
            'user.updated',
            [
                'entity_type' => 'user',
                'entity_id' => $user->id,
                'entity_name' => $user->name,
            ],
            $before,
            $after,
            ['email' => $user->email, 'role' => $user->role]
        );

        return redirect()->route('admin.users.show', $user->id)
            ->with('status', __('admin.user_updated'));
    }

    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        
        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return back()->with('error', __('admin.cannot_delete_self'));
        }

        // Check if current user can manage this user
        if (!auth()->user()->canManage($user)) {
            return back()->with('error', 'You do not have permission to delete this user');
        }

        // Super admin cannot be deleted
        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Super admin cannot be deleted');
        }

        $before = $user->toArray();

        AuditLogger::record(
            'user.deleted',
            [
                'entity_type' => 'user',
                'entity_id' => $user->id,
                'entity_name' => $user->name,
            ],
            $before,
            null,
            ['email' => $user->email, 'role' => $user->role]
        );

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('status', __('admin.user_deleted'));
    }

    public function toggleActive(string $id)
    {
        $user = User::findOrFail($id);
        
        // Prevent self-deactivation
        if ($user->id === auth()->id()) {
            return back()->with('error', __('admin.cannot_deactivate_self'));
        }

        // Check if current user can manage this user
        if (!auth()->user()->canManage($user)) {
            return back()->with('error', 'You do not have permission to modify this user');
        }

        // Super admin cannot be deactivated
        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Super admin cannot be deactivated');
        }

        $before = ['is_active' => $user->is_active];

        $user->is_active = !$user->is_active;
        $user->save();

        if (!$user->is_active) {
            RefreshToken::where('user_id', $user->id)
                ->whereNull('revoked_at')
                ->update(['revoked_at' => now()]);
        }

        $after = ['is_active' => $user->is_active];

        AuditLogger::record(
            'user.toggle_active',
            [
                'entity_type' => 'user',
                'entity_id' => $user->id,
                'entity_name' => $user->name,
            ],
            $before,
            $after,
            [
                'email' => $user->email,
                'role' => $user->role,
            ]
        );

        $messageKey = $user->is_active ? 'user_activated' : 'user_deactivated';

        return back()->with('status', __('admin.' . $messageKey));
    }
}
