<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Services\OrderServices;
/**
 * @OA\Tag(
 * name="Orders",
 * description="API Endpoints for managing user orders"
 * )
 */
class OrderController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/orders",
     * summary="Get all orders for authenticated user",
     * tags={"Orders"},
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(name="Accept", in="header", required=true, @OA\Schema(type="string", default="application/json")),
     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Orders retrieved successfully"),
     * @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Order"))
     * )
     * ),
     * @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorUnauthorized"))
     * )
     */
    public function getOrders() {
        $user = Auth::user();
        
        $orders = OrderServices::getOrders($user);

        return response()->json([
            'message' => 'Orders retrieved successfully',
            'data' => $orders
        ], 200);
    }

    /**
     * @OA\Post(
     * path="/api/orders",
     * summary="Create a new order from cart items",
     * tags={"Orders"},
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(name="Accept", in="header", required=true, @OA\Schema(type="string", default="application/json")),
     * @OA\RequestBody(
     * required=true,
     * description="Order details",
     * @OA\JsonContent(
     * required={"address_id"},
     * @OA\Property(property="address_id", type="integer", example=1)
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Order created successfully",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Order created successfully"),
     * @OA\Property(property="data", ref="#/components/schemas/Order")
     * )
     * ),
     * @OA\Response(
     * response=400,
     * description="Bad Request (Cart is empty)",
     * @OA\JsonContent(@OA\Property(property="message", type="string", example="Cart is empty. Cannot create order."))
     * ),
     * @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorUnauthorized")),
     * @OA\Response(
     * response=404,
     * description="Address not found",
     * @OA\JsonContent(@OA\Property(property="message", type="string", example="Address not found"))
     * ),
     * @OA\Response(
     * response=422,
     * description="Validation Error",
     * @OA\JsonContent(ref="#/components/schemas/ErrorValidationOrder")
     * ),
     * @OA\Response(response=500, description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorInternalServer"))
     * )
     */
    public function createOrder(Request $request) {
        $request->validate([
            'address_id' => 'required|exists:addresses,id',
        ]);

        $user = Auth::user();

        return OrderServices::createOrder($user, $request->address_id);
    }

    /**
     * @OA\Patch(
     * path="/api/orders/{order}/products",
     * summary="Update product quantity in an existing order",
     * tags={"Orders"},
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(name="Accept", in="header", required=true, @OA\Schema(type="string", default="application/json")),
     * @OA\Parameter(name="order", in="path", required=true, description="Order ID", @OA\Schema(type="integer")),
     * @OA\RequestBody(
     * required=true,
     * description="Product update details",
     * @OA\JsonContent(
     * required={"product_id", "quantity"},
     * @OA\Property(property="product_id", type="integer", example=1),
     * @OA\Property(property="quantity", type="integer", example=2, description="New quantity")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Product quantity updated",
     * @OA\JsonContent(@OA\Property(property="message", type="string", example="Product quantity updated successfully"))
     * ),
     * @OA\Response(
     * response=400,
     * description="Bad Request (Order not pending)",
     * @OA\JsonContent(@OA\Property(property="message", type="string", example="Order cannot be updated as it is no longer pending"))
     * ),
     * @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorUnauthorized")),
     * @OA\Response(response=403, description="Forbidden", @OA\JsonContent(ref="#/components/schemas/ErrorForbidden")),
     * @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ErrorNotFound")),
     * @OA\Response(
     * response=422,
     * description="Validation Error",
     * @OA\JsonContent(ref="#/components/schemas/ErrorValidationOrder")
     * ),
     * @OA\Response(response=500, description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorInternalServer"))
     * )
     */
    public function updateProductQuantity(Request $request, Order $order) {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $user = Auth::user();

        if ($order->user_id !== $user->id) {
            return response()->json([
                'message' => 'You are not authorized to update this order'
            ], 403);
        }

        return OrderServices::updateOrderProduct($order, $data);
    }

    /**
     * @OA\Delete(
     * path="/api/orders/{order}/products",
     * summary="Remove a product from an order",
     * tags={"Orders"},
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(name="Accept", in="header", required=true, @OA\Schema(type="string", default="application/json")),
     * @OA\Parameter(name="order", in="path", required=true, description="Order ID", @OA\Schema(type="integer")),
     * @OA\RequestBody(
     * required=true,
     * description="Product to remove",
     * @OA\JsonContent(
     * required={"product_id"},
     * @OA\Property(property="product_id", type="integer", example=1)
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Product removed successfully",
     * @OA\JsonContent(@OA\Property(property="message", type="string", example="Product removed successfully"))
     * ),
     * @OA\Response(
     * response=400,
     * description="Bad Request (Order not pending)",
     * @OA\JsonContent(@OA\Property(property="message", type="string", example="Order cannot be updated as it is no longer pending"))
     * ),
     * @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorUnauthorized")),
     * @OA\Response(response=403, description="Forbidden", @OA\JsonContent(ref="#/components/schemas/ErrorForbidden")),
     * @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ErrorNotFound")),
     * @OA\Response(
     * response=422,
     * description="Validation Error",
     * @OA\JsonContent(ref="#/components/schemas/ErrorValidationOrder")
     * ),
     * @OA\Response(response=500, description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorInternalServer"))
     * )
     */
    public function removeProduct(Order $order, Request $request) {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $productId = $data['product_id'];
        $data['quantity'] = 0;

        $user = Auth::user();

        if ($order->user_id !== $user->id) {
            return response()->json([
                'message' => 'You are not authorized to update this order'
            ], 403);
        }

        return OrderServices::updateOrderProduct($order, $data);
    }

    /**
     * @OA\Delete(
     * path="/api/orders/{order}",
     * summary="Cancel an order",
     * tags={"Orders"},
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(name="Accept", in="header", required=true, @OA\Schema(type="string", default="application/json")),
     * @OA\Parameter(name="order", in="path", required=true, description="Order ID", @OA\Schema(type="integer")),
     * @OA\Response(
     * response=200,
     * description="Order cancelled successfully",
     * @OA\JsonContent(@OA\Property(property="message", type="string", example="Order cancelled successfully"))
     * ),
     * @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorUnauthorized")),
     * @OA\Response(response=403, description="Forbidden", @OA\JsonContent(ref="#/components/schemas/ErrorForbidden")),
     * @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ErrorNotFound")),
     * @OA\Response(response=500, description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorInternalServer"))
     * )
     */
    public function cancelOrder(Order $order) {
        $user = Auth::user();

        if ($order->user_id !== $user->id) {
            return response()->json([
                'message' => 'You are not authorized to cancel this order'
            ], 403);
        }

        return OrderServices::cancelOrder($order);
    }
}
