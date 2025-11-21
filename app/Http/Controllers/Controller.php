<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 * title="E-Commerce API Documentation",
 * version="1.0.0",
 * description="API endpoints for the E-Commerce Application"
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
 * @OA\Schema(
 * schema="Category",
 * title="Category",
 * description="Category model representation",
 * @OA\Property(property="id", type="integer", example=1),
 * @OA\Property(property="title", type="string", example="Electronics"),
 * @OA\Property(property="slug", type="string", example="electronics"),
 * @OA\Property(property="created_at", type="string", format="date-time", example="2023-11-17T12:00:00.000000Z"),
 * @OA\Property(property="updated_at", type="string", format="date-time", example="2023-11-17T12:00:00.000000Z"),
 * @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, example=null)
 * )
 *
 * @OA\Schema(
 * schema="ProductImage",
 * title="Product Image",
 * description="Product Image model representation",
 * @OA\Property(property="id", type="integer", example=1),
 * @OA\Property(property="product_id", type="integer", example=10),
 * @OA\Property(property="image_url", type="string", example="https://example.com/images/product1.jpg"),
 * @OA\Property(property="created_at", type="string", format="date-time", example="2023-11-17T12:00:00.000000Z"),
 * @OA\Property(property="updated_at", type="string", format="date-time", example="2023-11-17T12:00:00.000000Z")
 * )
 *
 * @OA\Schema(
 * schema="Product",
 * title="Product",
 * description="Product model representation",
 * @OA\Property(property="id", type="integer", example=10),
 * @OA\Property(property="name", type="string", example="Wireless Headphones"),
 * @OA\Property(property="description", type="string", example="High quality wireless headphones with noise cancellation."),
 * @OA\Property(property="price", type="number", format="float", example=99.99),
 * @OA\Property(property="stock_quantity", type="integer", example=50),
 * @OA\Property(property="metric", type="string", example="unit"),
 * @OA\Property(property="created_at", type="string", format="date-time", example="2023-11-17T12:00:00.000000Z"),
 * @OA\Property(property="updated_at", type="string", format="date-time", example="2023-11-17T12:00:00.000000Z"),
 * @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, example=null),
 * @OA\Property(property="images", type="array", @OA\Items(ref="#/components/schemas/ProductImage"))
 * )
 *
 * @OA\Schema(
 * schema="CartItem",
 * title="Cart Item",
 * description="Cart Item model representation",
 * @OA\Property(property="id", type="integer", example=1),
 * @OA\Property(property="cart_id", type="integer", example=5),
 * @OA\Property(property="product_id", type="integer", example=10),
 * @OA\Property(property="quantity", type="integer", example=2),
 * @OA\Property(property="product", ref="#/components/schemas/Product")
 * )
 *
 * @OA\Schema(
 * schema="ErrorValidationCart",
 * title="Cart Validation Error",
 * description="Validation error response for cart endpoints",
 * @OA\Property(property="message", type="string", example="The given data was invalid."),
 * @OA\Property(property="errors", type="object",
 * @OA\Property(property="product_id", type="array", @OA\Items(type="string", example="The selected product_id is invalid.")),
 * @OA\Property(property="quantity", type="array", @OA\Items(type="string", example="The quantity must be at least 1."))
 * )
 * )
 *
 * @OA\Schema(
 * schema="ErrorInternalServer",
 * title="Internal Server Error",
 * description="Standard response for 500 Internal Server Error",
 * @OA\Property(property="message", type="string", example="Something went wrong.")
 * )
 *
 * @OA\Schema(
 * schema="SuccessMessage",
 * title="Success Message",
 * description="Standard success response with a message",
 * @OA\Property(property="message", type="string", example="Operation successful.")
 * )
 *
 * @OA\Schema(
 * schema="SuccessResource",
 * title="Success Resource",
 * description="Standard success response with a resource",
 * @OA\Property(property="data", type="object")
 * )
 *
 * @OA\Schema(
 * schema="SuccessCollection",
 * title="Success Collection",
 * description="Standard success response with a collection",
 * @OA\Property(property="data", type="array", @OA\Items(type="object"))
 * )
 *
 * @OA\Schema(
 * schema="SuccessPaginatedCollection",
 * title="Success Paginated Collection",
 * description="Standard success response with a paginated collection",
 * @OA\Property(property="data", type="array", @OA\Items(type="object")),
 * @OA\Property(property="links", type="object"),
 * @OA\Property(property="meta", type="object")
 * )
 *
 * @OA\Schema(
 * schema="SuccessCreated",
 * title="Success Created",
 * description="Standard success response for 201 Created",
 * @OA\Property(property="message", type="string", example="Resource created successfully."),
 * @OA\Property(property="data", type="object")
 * )
 *
 * @OA\Schema(
 * schema="SuccessUpdated",
 * title="Success Updated",
 * description="Standard success response for 200 OK (Update)",
 * @OA\Property(property="message", type="string", example="Resource updated successfully."),
 * @OA\Property(property="data", type="object")
 * )
 *
 * @OA\Schema(
 * schema="SuccessDeleted",
 * title="Success Deleted",
 * description="Standard success response for 200 OK (Delete)",
 * @OA\Property(property="message", type="string", example="Resource deleted successfully.")
 * )
 *
 * @OA\Schema(
 * schema="SuccessAccepted",
 * title="Success Accepted",
 * description="Standard success response for 202 Accepted",
 * @OA\Property(property="message", type="string", example="Request accepted.")
 * )
 *
 * @OA\Schema(
 * schema="SuccessNoContent",
 * title="Success No Content",
 * description="Standard success response for 204 No Content"
 * )
 *
 * @OA\Schema(
 * schema="ErrorBadRequest",
 * title="Bad Request",
 * description="Standard response for 400 Bad Request",
 * @OA\Property(property="message", type="string", example="Bad Request")
 * )
 *
 * @OA\Schema(
 * schema="ErrorMethodNotAllowed",
 * title="Method Not Allowed",
 * description="Standard response for 405 Method Not Allowed",
 * @OA\Property(property="message", type="string", example="Method Not Allowed")
 * )
 *
 * @OA\Schema(
 * schema="ErrorConflict",
 * title="Conflict",
 * description="Standard response for 409 Conflict",
 * @OA\Property(property="message", type="string", example="Conflict")
 * )
 *
 * @OA\Schema(
 * schema="ErrorTooManyRequests",
 * title="Too Many Requests",
 * description="Standard response for 429 Too Many Requests",
 * @OA\Property(property="message", type="string", example="Too Many Requests")
 * )
 *
 * @OA\Schema(
 * schema="ErrorServiceUnavailable",
 * title="Service Unavailable",
 * description="Standard response for 503 Service Unavailable",
 * @OA\Property(property="message", type="string", example="Service Unavailable")
 * )
 *
 * @OA\Schema(
 * schema="ErrorGatewayTimeout",
 * title="Gateway Timeout",
 * description="Standard response for 504 Gateway Timeout",
 * @OA\Property(property="message", type="string", example="Gateway Timeout")
 * )
 *
 * @OA\Schema(
 * schema="ErrorNotImplemented",
 * title="Not Implemented",
 * description="Standard response for 501 Not Implemented",
 * @OA\Property(property="message", type="string", example="Not Implemented")
 * )
 *
 * @OA\Schema(
 * schema="ErrorBadGateway",
 * title="Bad Gateway",
 * description="Standard response for 502 Bad Gateway",
 * @OA\Property(property="message", type="string", example="Bad Gateway")
 * )
 *
 * @OA\Schema(
 * schema="ErrorUnsupportedMediaType",
 * title="Unsupported Media Type",
 * description="Standard response for 415 Unsupported Media Type",
 * @OA\Property(property="message", type="string", example="Unsupported Media Type")
 * )
 *
 * @OA\Schema(
 * schema="ErrorUnprocessableEntity",
 * title="Unprocessable Entity",
 * description="Standard response for 422 Unprocessable Entity",
 * @OA\Property(property="message", type="string", example="Unprocessable Entity")
 * )
 *
 * @OA\Schema(
 * schema="ErrorPreconditionFailed",
 * title="Precondition Failed",
 * description="Standard response for 412 Precondition Failed",
 * @OA\Property(property="message", type="string", example="Precondition Failed")
 * )
 *
 * @OA\Schema(
 * schema="ErrorPayloadTooLarge",
 * title="Payload Too Large",
 * description="Standard response for 413 Payload Too Large",
 * @OA\Property(property="message", type="string", example="Payload Too Large")
 * )
 *
 * @OA\Schema(
 * schema="ErrorUriTooLong",
 * title="URI Too Long",
 * description="Standard response for 414 URI Too Long",
 * @OA\Property(property="message", type="string", example="URI Too Long")
 * )
 *
 * @OA\Schema(
 * schema="ErrorExpectationFailed",
 * title="Expectation Failed",
 * description="Standard response for 417 Expectation Failed",
 * @OA\Property(property="message", type="string", example="Expectation Failed")
 * )
 *
 * @OA\Schema(
 * schema="ErrorMisdirectedRequest",
 * title="Misdirected Request",
 * description="Standard response for 421 Misdirected Request",
 * @OA\Property(property="message", type="string", example="Misdirected Request")
 * )
 *
 * @OA\Schema(
 * schema="ErrorLocked",
 * title="Locked",
 * description="Standard response for 423 Locked",
 * @OA\Property(property="message", type="string", example="Locked")
 * )
 *
 * @OA\Schema(
 * schema="ErrorFailedDependency",
 * title="Failed Dependency",
 * description="Standard response for 424 Failed Dependency",
 * @OA\Property(property="message", type="string", example="Failed Dependency")
 * )
 *
 * @OA\Schema(
 * schema="ErrorUpgradeRequired",
 * title="Upgrade Required",
 * description="Standard response for 426 Upgrade Required",
 * @OA\Property(property="message", type="string", example="Upgrade Required")
 * )
 *
 * @OA\Schema(
 * schema="ErrorPreconditionRequired",
 * title="Precondition Required",
 * description="Standard response for 428 Precondition Required",
 * @OA\Property(property="message", type="string", example="Precondition Required")
 * )
 *
 * @OA\Schema(
 * schema="ErrorRequestHeaderFieldsTooLarge",
 * title="Request Header Fields Too Large",
 * description="Standard response for 431 Request Header Fields Too Large",
 * @OA\Property(property="message", type="string", example="Request Header Fields Too Large")
 * )
 *
 * @OA\Schema(
 * schema="ErrorUnavailableForLegalReasons",
 * title="Unavailable For Legal Reasons",
 * description="Standard response for 451 Unavailable For Legal Reasons",
 * @OA\Property(property="message", type="string", example="Unavailable For Legal Reasons")
 * )
 *
 * @OA\Schema(
 * schema="ErrorVariantAlsoNegotiates",
 * title="Variant Also Negotiates",
 * description="Standard response for 506 Variant Also Negotiates",
 * @OA\Property(property="message", type="string", example="Variant Also Negotiates")
 * )
 *
 * @OA\Schema(
 * schema="ErrorInsufficientStorage",
 * title="Insufficient Storage",
 * description="Standard response for 507 Insufficient Storage",
 * @OA\Property(property="message", type="string", example="Insufficient Storage")
 * )
 *
 * @OA\Schema(
 * schema="ErrorLoopDetected",
 * title="Loop Detected",
 * description="Standard response for 508 Loop Detected",
 * @OA\Property(property="message", type="string", example="Loop Detected")
 * )
 *
 * @OA\Schema(
 * schema="ErrorNotExtended",
 * title="Not Extended",
 * description="Standard response for 510 Not Extended",
 * @OA\Property(property="message", type="string", example="Not Extended")
 * )
 *
 * @OA\Schema(
 * schema="ErrorNetworkAuthenticationRequired",
 * title="Network Authentication Required",
 * description="Standard response for 511 Network Authentication Required",
 * @OA\Property(property="message", type="string", example="Network Authentication Required")
 * )
 *
 * @OA\Schema(
 * schema="ErrorUnknown",
 * title="Unknown Error",
 * description="Standard response for Unknown Error",
 * @OA\Property(property="message", type="string", example="Unknown Error")
 * )
 */
abstract class Controller
{
    //
}