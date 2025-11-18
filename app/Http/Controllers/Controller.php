<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 * title="E-Commerce API Documentation",
 * version="1.0.0",
 * description="API endpoints for the Graduation Project"
 * )
 *
 * @OA\SecurityScheme(
 * securityScheme="bearerAuth",
 * type="http",
 * scheme="bearer",
 * bearerFormat="JWT",
 * description="Enter your Sanctum token in the format: Bearer {token}"
 * )
 *
 *
 *
 * // --- REUSABLE SCHEMAS ---
 *
 * @OA\Schema(
 * schema="User",
 * title="User",
 * description="User model representation",
 * @OA\Property(property="id", type="integer", example=1),
 * @OA\Property(property="name", type="string", example="Amgad Mohamed"),
 * @OA\Property(property="email", type="string", format="email", example="amgad@example.com"),
 * @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true, example="2023-11-17T12:00:00.000000Z"),
 * @OA\Property(property="created_at", type="string", format="date-time", example="2023-11-17T12:00:00.000000Z"),
 * @OA\Property(property="updated_at", type="string", format="date-time", example="2023-11-17T12:00:00.000000Z")
 * )
 *
 * @OA\Schema(
 * schema="Address",
 * title="Address",
 * description="Address model representation",
 * @OA\Property(property="id", type="integer", example=1),
 * @OA\Property(property="user_id", type="integer", example=5),
 * @OA\Property(property="street", type="string", example="123 Example Street"),
 * @OA\Property(property="city", type="string", example="Cairo"),
 * @OA\Property(property="country", type="string", example="Egypt"),
 * @OA\Property(property="phone_numbers", type="array",
 * @OA\Items(type="string", example="+201012345678")
 * ),
 * @OA\Property(property="created_at", type="string", format="date-time", example="2023-11-17T12:00:00.000000Z"),
 * @OA\Property(property="updated_at", type="string", format="date-time", example="2023-11-17T12:00:00.000000Z")
 * )
 *
 * @OA\Schema(
 * schema="ErrorValidation",
 * title="Validation Error",
 * description="Standard response for 422 Unprocessable Entity due to validation failures",
 * @OA\Property(property="message", type="string", example="The given data was invalid."),
 * @OA\Property(property="errors", type="object",
 * @OA\Property(property="email", type="array", @OA\Items(type="string", example="The email has already been taken.")),
 * @OA\Property(property="password", type="array", @OA\Items(type="string", example="The password field is required."))
 * )
 * )
 *
 * @OA\Schema(
 * schema="ErrorValidationAuth",
 * title="Authentication Validation Error",
 * description="Validation error response for authentication endpoints (register, login)",
 * @OA\Property(property="message", type="string", example="The given data was invalid."),
 * @OA\Property(property="errors", type="object",
 * @OA\Property(property="name", type="array", @OA\Items(type="string", example="The name field is required.")),
 * @OA\Property(property="email", type="array", @OA\Items(type="string", example="The email has already been taken.")),
 * @OA\Property(property="password", type="array", @OA\Items(type="string", example="The password must be at least 8 characters."))
 * )
 * )
 *
 * @OA\Schema(
 * schema="ErrorValidationUser",
 * title="User Profile Validation Error",
 * description="Validation error response for user profile endpoints (update profile, change password)",
 * @OA\Property(property="message", type="string", example="The given data was invalid."),
 * @OA\Property(property="errors", type="object",
 * @OA\Property(property="name", type="array", @OA\Items(type="string", example="The name field must not exceed 255 characters.")),
 * @OA\Property(property="email", type="array", @OA\Items(type="string", example="The email must be a valid email address.")),
 * @OA\Property(property="current_password", type="array", @OA\Items(type="string", example="The current password field is required.")),
 * @OA\Property(property="password", type="array", @OA\Items(type="string", example="The password must be at least 8 characters."))
 * )
 * )
 *
 * @OA\Schema(
 * schema="ErrorValidationAddress",
 * title="Address Validation Error",
 * description="Validation error response for address endpoints (create, update address)",
 * @OA\Property(property="message", type="string", example="The given data was invalid."),
 * @OA\Property(property="errors", type="object",
 * @OA\Property(property="country", type="array", @OA\Items(type="string", example="The country field is required.")),
 * @OA\Property(property="city", type="array", @OA\Items(type="string", example="The city field is required.")),
 * @OA\Property(property="street", type="array", @OA\Items(type="string", example="The street must not exceed 255 characters.")),
 * @OA\Property(property="phone_numbers", type="array", @OA\Items(type="string", example="The phone numbers field must be an array with at least 1 item.")),
 * @OA\Property(property="phone_numbers.*", type="array", @OA\Items(type="string", example="Each phone number must not exceed 13 characters."))
 * )
 * )
 *
 * @OA\Schema(
 * schema="ErrorUnauthorized",
 * title="Unauthorized",
 * description="Standard response for 401 Unauthorized",
 * @OA\Property(property="message", type="string", example="Unauthenticated.")
 * )
 *
 * @OA\Schema(
 * schema="ErrorForbidden",
 * title="Forbidden",
 * description="Standard response for 403 Forbidden",
 * @OA\Property(property="message", type="string", example="Forbidden")
 * )
 *
 * @OA\Schema(
 * schema="ErrorNotFound",
 * title="Not Found",
 * description="Standard response for 404 Not Found",
 * @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\ModelName] ID.")
 * )
 */
abstract class Controller
{
    //
}