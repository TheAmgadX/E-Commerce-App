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
     * @OA\Post(
     * path="/api/register",
     * summary="Register a new user account",
     * tags={"Auth"},
     * @OA\Parameter(
     * name="Accept",
     * in="header",
     * required=true,
     * @OA\Schema(type="string", default="application/json")
     * ),
     * @OA\RequestBody(
     * required=true,
     * description="User registration details",
     * @OA\JsonContent(
     * required={"name","email","password","password_confirmation"},
     * @OA\Property(property="name", type="string", example="Amgad Mohamed"),
     * @OA\Property(property="email", type="string", format="email", example="amgad@example.com"),
     * @OA\Property(property="password", type="string", format="password", example="Secret12345"),
     * @OA\Property(property="password_confirmation", type="string", format="password", example="Secret12345")
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="User registered successfully",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="User registered successfully. Please check your email to verify.")
     * )
     * ),
     * @OA\Response(
     * response=422,
     * description="Validation Error",
     * @OA\JsonContent(ref="#/components/schemas/ErrorValidation")
     * )
     * )
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
     * @OA\Post(
     * path="/api/login",
     * summary="Log in an existing user and receive a Sanctum token",
     * tags={"Auth"},
     * @OA\Parameter(
     * name="Accept",
     * in="header",
     * required=true,
     * @OA\Schema(type="string", default="application/json")
     * ),
     * @OA\RequestBody(
     * required=true,
     * description="User credentials",
     * @OA\JsonContent(
     * required={"email","password"},
     * @OA\Property(property="email", type="string", format="email", example="amgad@example.com"),
     * @OA\Property(property="password", type="string", format="password", example="Secret12345")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Login successful",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Login successful"),
     * @OA\Property(property="user", ref="#/components/schemas/User"),
     * @OA\Property(property="token", type="string", example="1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx")
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Invalid login details",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Invalid login details")
     * )
     * ),
     * @OA\Response(
     * response=422,
     * description="Validation Error",
     * @OA\JsonContent(ref="#/components/schemas/ErrorValidation")
     * )
     * )
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
     * @OA\Post(
     * path="/api/logout",
     * summary="Log out user and invalidate the current token",
     * tags={"Auth"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="Accept",
     * in="header",
     * required=true,
     * @OA\Schema(type="string", default="application/json")
     * ),
     * @OA\Response(
     * response=200, 
     * description="Successfully logged out",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Successfully logged out")
     * )
     * ),
     * @OA\Response(response=401, description="Unauthenticated / Missing Token")
     * )
     */
    public function logout(Request $request){
        $user = $request->user();
        
        $user->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ], 200);
    }
}