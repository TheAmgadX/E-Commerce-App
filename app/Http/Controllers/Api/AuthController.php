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
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Cache;

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
     * @OA\JsonContent(ref="#/components/schemas/ErrorValidationAuth")
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
     * @OA\JsonContent(ref="#/components/schemas/ErrorValidationAuth")
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

    /**
     * @OA\Post(
     * path="/api/forget-password",
     * summary="Send OTP for password reset",
     * tags={"Auth"},
     * @OA\RequestBody(
     * required=true,
     * description="User email",
     * @OA\JsonContent(
     * required={"email"},
     * @OA\Property(property="email", type="string", format="email", example="amgad@example.com")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="OTP sent successfully",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="OTP sent to your email.")
     * )
     * ),
     * @OA\Response(response=404, description="User not found")
     * )
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgetPassword(Request $request) {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $email = $request->email;
        $otp = rand(100000, 999999);

        // Store OTP in Cache for 5 minutes
        Cache::put('otp_' . $email, $otp, now()->addMinutes(5));

        // Send OTP via Email
        try {
            Mail::to($email)->send(new OtpMail($otp));
        } catch (\Exception $e) {
            \Log::error('OTP Mail sending failed for user: ' . $email . ' Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to send OTP. Please try again later.'
            ], 500);
        }

        return response()->json([
            'message' => 'OTP sent to your email.'
        ], 200);
    }

    /**
     * @OA\Post(
     * path="/api/check-OTP",
     * summary="Verify OTP and return auth token",
     * tags={"Auth"},
     * @OA\RequestBody(
     * required=true,
     * description="User email and OTP",
     * @OA\JsonContent(
     * required={"email", "otp"},
     * @OA\Property(property="email", type="string", format="email", example="amgad@example.com"),
     * @OA\Property(property="otp", type="string", example="123456")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="OTP verified successfully",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="OTP verified successfully"),
     * @OA\Property(property="token", type="string", example="1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx")
     * )
     * ),
     * @OA\Response(response=400, description="Invalid or Expired OTP")
     * )
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkOTP(Request $request) {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|string',
        ]);

        $email = $request->email;
        $otp = $request->otp;

        $cachedOtp = Cache::get('otp_' . $email);

        // if doesn't exist in the cache or doesn't match
        if (!$cachedOtp || $cachedOtp != $otp) {
            return response()->json([
                'message' => 'Invalid or Expired OTP'
            ], 400);
        }

        // Remove OTP from cache
        Cache::forget('otp_' . $email);

        $user = User::where('email', $email)->first();
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'OTP verified successfully',
            'token' => $token
        ], 200);
    }
}