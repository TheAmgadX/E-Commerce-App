<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Address;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AddressController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/addresses",
     * summary="Get all addresses for the authenticated user",
     * tags={"Address"},
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(
     * name="Accept",
     * in="header",
     * required=true,
     * @OA\Schema(type="string", default="application/json")
     * ),
     * @OA\Response(
     * response=200,
     * description="List of user addresses",
     * @OA\JsonContent(
     * @OA\Property(property="addresses", type="array", @OA\Items(ref="#/components/schemas/Address"))
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthorized",
     * @OA\JsonContent(ref="#/components/schemas/ErrorUnauthorized")
     * )
     * )
     */
    public function addresses(Request $request) {
        $addresses = $request->user()->addresses;

        return response()->json([
            'addresses' => $addresses
        ], 200);
    }

    /**
     * @OA\Post(
     * path="/api/address",
     * summary="Create a new address for the authenticated user",
     * tags={"Address"},
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(
     * name="Accept",
     * in="header",
     * required=true,
     * @OA\Schema(type="string", default="application/json")
     * ),
     * @OA\RequestBody(
     * required=true,
     * description="Address details",
     * @OA\JsonContent(
     * required={"country", "city", "street", "phone_numbers"},
     * @OA\Property(property="country", type="string", example="Egypt"),
     * @OA\Property(property="city", type="string", example="Giza"),
     * @OA\Property(property="street", type="string", example="456 Oak Avenue, Building 10"),
     * @OA\Property(property="phone_numbers", type="array",
     * @OA\Items(type="string", example="+201012345678"),
     * description="Array of phone number strings"
     * )
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Address created successfully",
     * @OA\JsonContent(ref="#/components/schemas/SuccessCreated")
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthorized",
     * @OA\JsonContent(ref="#/components/schemas/ErrorUnauthorized")
     * ),
     * @OA\Response(
     * response=422,
     * description="Validation Error",
     * @OA\JsonContent(ref="#/components/schemas/ErrorValidationAddress")
     * )
     * )
     */
    public function create(Request $request) {
        $validated = $request->validate([
            'country' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'street' => ['required', 'string', 'max:255'],

            // phone_numbers must be an array of at least one string
            'phone_numbers' => ['required', 'array', 'min:1'],

            // each element in the array phone_numbers maximum 11 
            'phone_numbers.*' => ['required', 'string', 'max:13'],
        ]);

        $address = $request->user()->addresses()->create($validated);

        return response()->json([
            'message' => 'Address created successfully',
        ], 201);
    }

    /**
     * @OA\Put(
     * path="/api/address/{address}",
     * summary="Update an existing address for the authenticated user",
     * tags={"Address"},
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(
     * name="Accept",
     * in="header",
     * required=true,
     * @OA\Schema(type="string", default="application/json")
     * ),
     * @OA\Parameter(
     * name="address",
     * in="path",
     * required=true,
     * description="ID of the address to update",
     * @OA\Schema(type="integer", example=1)
     * ),
     * @OA\RequestBody(
     * required=true,
     * description="Address details (all fields are optional)",
     * @OA\JsonContent(
     * @OA\Property(property="country", type="string", example="Updated Country", nullable=true),
     * @OA\Property(property="city", type="string", example="New Cairo", nullable=true),
     * @OA\Property(property="street", type="string", example="789 Pine Road, Floor 5", nullable=true),
     * @OA\Property(property="phone_numbers", type="array",
     * @OA\Items(type="string", example="+201298765432"),
     * nullable=true,
     * description="Array of phone number strings"
     * )
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Address updated successfully",
     * @OA\JsonContent(ref="#/components/schemas/SuccessUpdated")
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthorized",
     * @OA\JsonContent(ref="#/components/schemas/ErrorUnauthorized")
     * ),
     * @OA\Response(
     * response=403,
     * description="Forbidden - You do not have permission to update this address",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="You do not have permission to update this address.")
     * )
     * ),
     * @OA\Response(
     * response=404,
     * description="Not Found",
     * @OA\JsonContent(ref="#/components/schemas/ErrorNotFound")
     * ),
     * @OA\Response(
     * response=422,
     * description="Validation Error",
     * @OA\JsonContent(ref="#/components/schemas/ErrorValidationAddress")
     * )
     * )
     */
    public function update(Request $request, Address $address) {
        // Ensure the address belongs to the authenticated user
        if ($address->user_id !== $request->user()->id) {
            abort(403, 'You do not have permission to update this address.');
        }

        $validated = $request->validate([
            'country' => ['sometimes', 'string', 'max:100'],
            'city' => ['sometimes', 'string', 'max:100'],
            'street' => ['sometimes', 'string', 'max:255'],
            'phone_numbers' => ['sometimes', 'array', 'min:1'],
            'phone_numbers.*' => ['sometimes', 'string', 'max:13'],
        ]);

        $address->update($validated);

        return response()->json([
            'message' => 'Address updated successfully',
        ], 200);
    }

    /**
     * @OA\Delete(
     * path="/api/address/{address}",
     * summary="Delete an address for the authenticated user",
     * tags={"Address"},
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(
     * name="Accept",
     * in="header",
     * required=true,
     * @OA\Schema(type="string", default="application/json")
     * ),
     * @OA\Parameter(
     * name="address",
     * in="path",
     * required=true,
     * description="ID of the address to delete",
     * @OA\Schema(type="integer", example=1)
     * ),
     * @OA\Response(
     * response=200,
     * description="Address deleted successfully",
     * @OA\JsonContent(ref="#/components/schemas/SuccessDeleted")
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthorized",
     * @OA\JsonContent(ref="#/components/schemas/ErrorUnauthorized")
     * ),
     * @OA\Response(
     * response=403,
     * description="Forbidden - You do not have permission to delete this address",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="You do not have permission to delete this address.")
     * )
     * ),
     * @OA\Response(
     * response=404,
     * description="Not Found",
     * @OA\JsonContent(ref="#/components/schemas/ErrorNotFound")
     * )
     * )
     */
    public function delete(Request $request, Address $address) {
        // Ensure the address belongs to the authenticated user
        if ($address->user_id !== $request->user()->id) {
            abort(403, 'You do not have permission to delete this address.');
        }
        
        $address->delete();

        return response()->json([
            'message' => 'Address deleted successfully'
        ], 200);
    }
}