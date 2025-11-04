<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Resources\Admin\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('email')) {
            $query->where('email', 'like', '%'.$request->email.'%');
        }

        if ($request->filled('from')) {
            $query->where('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->where('created_at', '<=', $request->to);
        }

        $perPage = min((int) $request->get('per_page', 20), 50);
        $users = $query->paginate($perPage);

        return response()->json([
            'data' => UserResource::collection($users->items()),
            'next_cursor' => $users->hasMorePages() ? $users->nextPageUrl() : null,
        ]);
    }

    public function update(string $id, UpdateUserRequest $request): JsonResponse
    {
        $user = User::findOrFail($id);

        // Prevent current admin from removing their own admin role
        if ($user->id === auth()->id() && $request->role !== 'admin') {
            return response()->json([
                'type' => 'about:blank',
                'title' => 'Conflict',
                'status' => 409,
                'detail' => 'You cannot remove your own admin role',
            ], 409);
        }

        $user->update(['role' => $request->role]);

        return response()->json(new UserResource($user));
    }

    public function destroy(string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return response()->json([
                'type' => 'about:blank',
                'title' => 'Conflict',
                'status' => 409,
                'detail' => 'You cannot delete your own account',
            ], 409);
        }

        $user->delete();

        return response()->json(null, 204);
    }
}
