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
        
        $orders = Order::where('user_id', $user->id)
            ->with(['address', 'products.images'])
            ->orderBy('created_at', 'desc')
            ->get();

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
        $cart = $user->cart;

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json([
                'message' => 'Cart is empty. Cannot create order.'
            ], 400);
        }

        // Verify the address belongs to the user
        $address = $user->addresses()->find($request->address_id);
        if (!$address) {
            return response()->json([
                'message' => 'Address not found'
            ], 404);
        }

        DB::beginTransaction();
        
        try {
            // Calculate total amount
            $totalAmount = 0;
            foreach ($cart->items as $item) {
                $totalAmount += $item->product->price * $item->quantity;
            }

            // Create the order
            $order = Order::create([
                'user_id' => $user->id,
                'address_id' => $request->address_id,
                'total_amount' => $totalAmount,
                'status' => OrderStatus::Pending,
            ]);

            // Create order items from cart items
            foreach ($cart->items as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                ]);
            }

            // Clear the cart
            $cart->items()->delete();

            DB::commit();
            
            // Load relationships for response
            $order->load(['address', 'products.images']);

            return response()->json([
                'message' => 'Order created successfully',
                'data' => $order
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Order creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
        
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

        if ($order->status !== OrderStatus::Pending) {
            return response()->json([
                'message' => 'Order cannot be updated as it is no longer pending'
            ], 400);
        }

        $productId   = (int) $data['product_id'];
        $newQuantity = (int) $data['quantity'];

        $orderProduct = $order->products()->where('products.id', $productId)->first();

        if (!$orderProduct) {
            return response()->json([
                'message' => 'Product not found in this order'
            ], 404);
        }

        DB::beginTransaction();

        try {
            // calculate the new total_amount for the order and update the values.
            $oldQuantity = $orderProduct->pivot->quantity;
            
            $oldPrice = $orderProduct->price * $oldQuantity;

            $order->total_amount -= $oldPrice;

            $newPrice = $orderProduct->price * $newQuantity;

            $order->total_amount += $newPrice;
            
            $order->products()->updateExistingPivot($productId, ['quantity' => $newQuantity]);
            
            $order->save();
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Product quantity update failed',
                'error' => $e->getMessage()
            ], 500);
        }

        return response()->json([
            'message' => 'Product quantity updated successfully'
        ], 200);
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

        $user = Auth::user();

        if ($order->user_id !== $user->id) {
            return response()->json([
                'message' => 'You are not authorized to update this order'
            ], 403);
        }

        if ($order->status !== OrderStatus::Pending) {
            return response()->json([
                'message' => 'Order cannot be updated as it is no longer pending'
            ], 400);
        }

        if (!$order->products()->where('product_id', $productId)->exists()) {
            return response()->json([
                'message' => 'Product not found in this order'
            ], 404);
        }

        $orderProduct = $order->products()->where('product_id', $productId)->first();

        DB::beginTransaction();
        
        try {
            $removedProductPrice = $orderProduct->price * $orderProduct->pivot->quantity;
            $order->total_amount -= $removedProductPrice;

            $order->products()->detach($productId);

            if ($order->products()->count() === 0) {
                $order->status = OrderStatus::Cancelled;
            }

            $order->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Product removal failed',
                'error' => $e->getMessage()
            ], 500);
        }


        return response()->json([
            'message' => 'Product removed successfully'
        ], 200);
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

        $order->status = OrderStatus::Cancelled;

        try{
            $order->save();
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Order cancellation failed',
                'error' => $e->getMessage()
            ], 500);
        }

        return response()->json([
            'message' => 'Order cancelled successfully'
        ], 200);
    }
}
