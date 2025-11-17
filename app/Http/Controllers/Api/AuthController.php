<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use App\Models\User;
use App\Mail\WelcomeMail;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(Request $request) {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole('user');

        // Send the Welcome Mail
        try {
            Mail::to($user)->send(new WelcomeMail($user));
        } catch (\Exception $e) {
            // Log the error, but don't stop the user from registering
            \Log::error('Mail sending failed for user: ' . $user->email . ' Error: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'User registered successfully. Please check your email to verify.',
        ], 201);
    }

    /**
     * Log in an existing user.
     */
    public function login(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Attempt to authenticate the user
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid login details'
            ], 401); // 401 : "Unauthorized"
        }

        $user = User::where('email', $request['email'])->firstOrFail();

        // Create a Sanctum API token
        $token = $user->createToken('api-token')->plainTextToken;

        // Return the user and the token
        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token
        ], 200);
    }

    /**
     * Log out an existing user.
     */
    public function logout(Request $request){
        $user = $request->user();
        
        $user->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ], 200);
    }
}