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
     * @OA\Get(
     * path="/api/profile",
     * summary="Get authenticated user's profile and addresses",
     * tags={"User"},
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(
     * name="Accept",
     * in="header",
     * required=true,
     * @OA\Schema(type="string", default="application/json")
     * ),
     * @OA\Response(
     * response=200,
     * description="User profile data with loaded addresses",
     * @OA\JsonContent(
     * @OA\Property(property="user", type="object",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Amgad Mohamed"),
     * @OA\Property(property="email", type="string", format="email", example="amgad@example.com"),
     * @OA\Property(property="addresses", type="array", @OA\Items(ref="#/components/schemas/Address"))
     * )
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthorized",
     * @OA\JsonContent(ref="#/components/schemas/ErrorUnauthorized")
     * )
     * )
     */
    public function profile(Request $request) {
        $user = $request->user();

        $user->load('addresses'); 

        return response()->json([
            'user' => $user
        ], 200);
    }

    /**
     * @OA\Put(
     * path="/api/profile",
     * summary="Update authenticated user's profile (name and/or email).",
     * tags={"User"},
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(
     * name="Accept",
     * in="header",
     * required=true,
     * @OA\Schema(type="string", default="application/json")
     * ),
     * @OA\RequestBody(
     * description="User data to update (fields are optional)",
     * @OA\JsonContent(
     * @OA\Property(property="name", type="string", example="Amgad (Updated)", nullable=true),
     * @OA\Property(property="email", type="string", format="email", example="new-email@example.com", nullable=true)
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Profile updated successfully",
     * @OA\JsonContent(ref="#/components/schemas/SuccessUpdated")
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthorized",
     * @OA\JsonContent(ref="#/components/schemas/ErrorUnauthorized")
     * ),
     * @OA\Response(
     * response=422,
     * description="Validation Error",
     * @OA\JsonContent(ref="#/components/schemas/ErrorValidationUser")
     * )
     * )
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
     * @OA\Put(
     * path="/api/profile/password",
     * summary="Update the authenticated user's password",
     * tags={"User"},
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(
     * name="Accept",
     * in="header",
     * required=true,
     * @OA\Schema(type="string", default="application/json")
     * ),
     * @OA\RequestBody(
     * required=true,
     * description="Password change details",
     * @OA\JsonContent(
     * required={"current_password","password","password_confirmation"},
     * @OA\Property(property="current_password", type="string", format="password", example="OldPassword123"),
     * @OA\Property(property="password", type="string", format="password", example="NewPassword789"),
     * @OA\Property(property="password_confirmation", type="string", format="password", example="NewPassword789")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Password updated successfully",
     * @OA\JsonContent(ref="#/components/schemas/SuccessUpdated")
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthorized",
     * @OA\JsonContent(ref="#/components/schemas/ErrorUnauthorized")
     * ),
     * @OA\Response(
     * response=422,
     * description="Validation Error / Wrong current password",
     * @OA\JsonContent(
     * oneOf={
     * @OA\Schema(ref="#/components/schemas/ErrorValidationUser"),
     * @OA\Schema(
     * @OA\Property(property="message", type="string", example="The provided current password does not match your password.")
     * )
     * }
     * )
     * )
     * )
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
     * @OA\Put(
     * path="/api/reset-password",
     * summary="Reset password for authenticated user (without old password)",
     * tags={"User"},
     * security={{"bearerAuth": {}}},
     * @OA\RequestBody(
     * required=true,
     * description="New password details",
     * @OA\JsonContent(
     * required={"password","password_confirmation"},
     * @OA\Property(property="password", type="string", format="password", example="NewPassword789"),
     * @OA\Property(property="password_confirmation", type="string", format="password", example="NewPassword789")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Password reset successfully",
     * @OA\JsonContent(ref="#/components/schemas/SuccessMessage")
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthorized",
     * @OA\JsonContent(ref="#/components/schemas/ErrorUnauthorized")
     * ),
     * @OA\Response(
     * response=422,
     * description="Validation Error",
     * @OA\JsonContent(ref="#/components/schemas/ErrorValidationUser")
     * )
     * )
     */
    public function resetPassword(Request $request) {
        $user = $request->user();

        $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'message' => 'Password reset successfully'
        ], 200);
    }

    /**
     * @OA\Delete(
     * path="/api/profile",
     * summary="Delete the authenticated user's account and all associated tokens",
     * tags={"User"},
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(
     * name="Accept",
     * in="header",
     * required=true,
     * @OA\Schema(type="string", default="application/json")
     * ),
     * @OA\Response(
     * response=200,
     * description="Account deleted successfully",
     * @OA\JsonContent(ref="#/components/schemas/SuccessMessage")
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthorized",
     * @OA\JsonContent(ref="#/components/schemas/ErrorUnauthorized")
     * )
     * )
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