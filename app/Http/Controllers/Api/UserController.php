<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Get the authenticated user's profile.
     */
    public function profile(Request $request) {
        $user = $request->user();

        $user->load('addresses'); 

        return response()->json([
            'user' => $user
        ], 200);
    }

    /**
     * Update the authenticated user's profile (name & email).
     */
    public function update(Request $request){
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully',
        ], 200);
    }

    /**
     * Update the authenticated user's password.
     */
    public function updatePassword(Request $request) {
        $user = $request->user();

        // Validate the incoming data
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        // Check if the current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'The provided current password does not match your password.'
            ], 422);
        }

        // 3. Update the password
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'message' => 'Password updated successfully'
        ], 200);
    }

    /**
     * Delete the user account and its tokens.
     */
    public function delete(Request $request){
        $user = $request->user();
        
        $user->tokens()->delete();
        
        $user->delete();

        return response()->json([
            'message' => 'Account deleted successfully.'
        ], 200);
    }
}