<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Models\RefreshToken;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

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

    public function toggleActive(string $id)
    {
        $user = User::findOrFail($id);
        
        // Prevent self-deactivation
        if ($user->id === auth()->id()) {
            return back()->with('error', __('admin.cannot_deactivate_self'));
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
