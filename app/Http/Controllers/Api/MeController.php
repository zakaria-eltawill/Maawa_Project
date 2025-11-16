<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Resources\Auth\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class MeController extends Controller
{
    /**
     * Update the authenticated user's profile.
     */
    public function update(ProfileUpdateRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        // Handle password change separately
        if (!empty($data['password'])) {
            // Verify current password
            if (!Hash::check($data['current_password'], $user->password)) {
                return response()->json([
                    'type' => 'about:blank',
                    'title' => 'Validation Error',
                    'status' => 422,
                    'detail' => 'The current password is incorrect.',
                    'errors' => [
                        'current_password' => ['The current password is incorrect.']
                    ]
                ], 422);
            }

            $user->update([
                'password' => Hash::make($data['password']),
            ]);

            // Remove password fields from data
            unset($data['password'], $data['current_password'], $data['password_confirmation']);
        }

        // Update other fields
        if (!empty($data)) {
            $user->update($data);
        }

        return response()->json(
            new UserResource($user->refresh())
        );
    }
}

