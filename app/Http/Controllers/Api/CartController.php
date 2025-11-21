<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/cart",
     * summary="Get authenticated user's cart products",
     * tags={"Cart"},
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(
     * name="Accept",
     * in="header",
     * required=true,
     * @OA\Schema(type="string", default="application/json")
     * ),
     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(
     * oneOf={
     * @OA\Schema(
     * title="Populated Cart Response",
     * @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/CartItem")),
     * @OA\Property(property="message", type="string", example="Cart products retrieved successfully")
     * ),
     * @OA\Schema(
     * title="Empty Cart Response",
     * @OA\Property(property="message", type="string", example="Cart is empty"),
     * @OA\Property(property="data", type="array", @OA\Items(), example={})
     * )
     * }
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthorized (Invalid Token)",
     * @OA\JsonContent(ref="#/components/schemas/ErrorUnauthorized")
     * )
     * )
     */
    public function cartProducts() {
        $user = Auth::user();

        $cart = $user->cart;

        if (!$cart) {
             return response()->json([
                'message' => 'Cart is empty',
                'data' => [],
            ], 200);
        }

        $items = $cart->items()->with('product')->get();

        if($items->isEmpty()){
            return response()->json([
                'message' => 'Cart is empty',
                'data' => [],
            ], 200);
        }

        return response()->json([
            'data' => $items,
            'message' => 'Cart products retrieved successfully',
        ], 200);
    }

    /**
     * @OA\Post(
     * path="/api/cart",
     * summary="Add a product to the cart",
     * tags={"Cart"},
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(
     * name="Accept",
     * in="header",
     * required=true,
     * @OA\Schema(type="string", default="application/json")
     * ),
     * @OA\RequestBody(
     * required=true,
     * description="Product to add to cart",
     * @OA\JsonContent(
     * required={"product_id", "quantity"},
     * @OA\Property(property="product_id", type="integer", example=1),
     * @OA\Property(property="quantity", type="integer", example=1)
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Product added to cart successfully",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Product added to cart successfully")
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthorized",
     * @OA\JsonContent(ref="#/components/schemas/ErrorUnauthorized")
     * ),
     * @OA\Response(
     * response=422,
     * description="Validation Error",
     * @OA\JsonContent(ref="#/components/schemas/ErrorValidationCart")
     * )
     * )
     */
    public function addProduct(Request $request) {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $user = Auth::user();
        $cart = $user->cart;

        if (!$cart) {
            $cart = Cart::create(['user_id' => $user->id]);
        }

        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($cartItem) {
            $cartItem->quantity += $request->quantity;
            $cartItem->save();
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
            ]);
        }

        return response()->json([
            'message' => 'Product added to cart successfully'
        ], 200);
    }

    /**
     * @OA\Patch(
     * path="/api/cart",
     * summary="Update product quantity in the cart",
     * tags={"Cart"},
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(
     * name="Accept",
     * in="header",
     * required=true,
     * @OA\Schema(type="string", default="application/json")
     * ),
     * @OA\RequestBody(
     * required=true,
     * description="Product quantity update",
     * @OA\JsonContent(
     * required={"product_id", "quantity"},
     * @OA\Property(property="product_id", type="integer", example=1),
     * @OA\Property(property="quantity", type="integer", example=3)
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Cart updated successfully",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Cart updated successfully")
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthorized",
     * @OA\JsonContent(ref="#/components/schemas/ErrorUnauthorized")
     * ),
     * @OA\Response(
     * response=404,
     * description="Cart or Product not found",
     * @OA\JsonContent(ref="#/components/schemas/ErrorNotFound")
     * ),
     * @OA\Response(
     * response=422,
     * description="Validation Error",
     * @OA\JsonContent(ref="#/components/schemas/ErrorValidationCart")
     * ),
     * @OA\Response(
     * response=500,
     * description="Internal Server Error (Update Failed)",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Cart update failed")
     * )
     * )
     * )
     */
    public function updateProductCount(Request $request) {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $user = Auth::user();
        $cart = $user->cart;

        if (!$cart) {
            return response()->json([
                'message' => 'Cart not found'
            ], 404);
        }

        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $request->product_id)
            ->first();

        if (!$cartItem) {
            return response()->json([
                'message' => 'Product not found in cart'
            ], 404);
        }
        
        $updated = CartItem::where('cart_id', $cartItem->cart_id)
            ->where('product_id', $cartItem->product_id)
            ->update(['quantity' => $request->quantity]);

        if($updated === 0){
            return response()->json([
                'message' => 'Cart update failed'
            ], 500);
        }

        return response()->json([
            'message' => 'Cart updated successfully'
        ], 200);
    }

    /**
     * @OA\Delete(
     * path="/api/cart",
     * summary="Remove a product from the cart",
     * tags={"Cart"},
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(
     * name="Accept",
     * in="header",
     * required=true,
     * @OA\Schema(type="string", default="application/json")
     * ),
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
     * description="Product deleted from cart successfully",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Product deleted from cart successfully")
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthorized",
     * @OA\JsonContent(ref="#/components/schemas/ErrorUnauthorized")
     * ),
     * @OA\Response(
     * response=404,
     * description="Cart or Product not found",
     * @OA\JsonContent(ref="#/components/schemas/ErrorNotFound")
     * ),
     * @OA\Response(
     * response=422,
     * description="Validation Error",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="The given data was invalid."),
     * @OA\Property(property="errors", type="object",
     * @OA\Property(property="product_id", type="array", @OA\Items(type="string", example="The selected product_id is invalid."))
     * )
     * )
     * ),
     * @OA\Response(
     * response=500,
     * description="Internal Server Error (Deletion Failed)",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Product deletion failed")
     * )
     * )
     * )
     */
    public function deleteProduct(Request $request) {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $user = Auth::user();
        $cart = $user->cart;

        if (!$cart) {
            return response()->json([
                'message' => 'Cart not found'
            ], 404);
        }

        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $request->product_id)
            ->first();

        if (!$cartItem) {
            return response()->json([
                'message' => 'Product not found in cart'
            ], 404);
        }

        $deleted = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $request->product_id)
            ->delete();

        if($deleted === 0){
            return response()->json([
                'message' => 'Product deletion failed'
            ], 500);
        }

        return response()->json([
            'message' => 'Product deleted from cart successfully'
        ], 200);
    }
}
