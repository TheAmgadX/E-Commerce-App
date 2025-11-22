<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderMails;
use App\Models\OrderItem;
use App\Enums\OrderStatus;

class OrderServices
{
    static public function sendOrderEmail($order, $emailMsg, $emailSubject, $emailHeadline){
        Mail::to($order->user->email)->send(new OrderMails(
            $order,
            $emailSubject,
            $emailHeadline,
            $emailMsg
        ));    
    }

    static public function getOrders($user) {
        return Order::where('user_id', $user->id)
            ->with(['address', 'products.images'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    static private function validateAddress($user, $address_id){
        $address = $user->addresses()->find($address_id);

        if (!$address) {
            return [false, 'Address not found', 404, null];
        }

        return [true, null, 200, $address];
    }

    static private function updateInventory($product, $newQuantity){
        $product->update([
            'stock_quantity' => $newQuantity
        ]);
    }

    static private function createOrderItems($cart, $order){
        foreach ($cart->items as $cartItem) {
            if($cartItem->product->stock_quantity < $cartItem->quantity){
                return false;
            }

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $cartItem->product_id,
                'quantity' => $cartItem->quantity,
            ]);
            
            // update Inventory
            $newQuantity = $cartItem->product->stock_quantity - $cartItem->quantity;
            self::updateInventory($cartItem->product, $newQuantity);
        }

        return true;
    }

    static private function createOrderDB($user, $address, $cart){
        DB::beginTransaction();
        
        try {
            $totalAmount = 0;

            // Calculate total amount
            foreach ($cart->items as $cartItem) {
                $totalAmount += $cartItem->product->price * $cartItem->quantity;
            }

            // Create the order
            $order = Order::create([
                'user_id' => $user->id,
                'address_id' => $address->id,
                'total_amount' => $totalAmount,
                'status' => OrderStatus::Pending,
            ]);
            
            // Create order items from cart items and update inventory
            if(!self::createOrderItems($cart, $order)){
                DB::rollBack();

                return [false, 'Product quantity is not available', 'Order creation failed', 400, $order];
            }

            // Clear the cart
            $cart->items()->delete();

            DB::commit();
            
            // Load relationships for response
            $order->load(['address', 'products.images']);

            return [true, null, 'Order created successfully', 201, $order];

        } catch (\Exception $e) {
            DB::rollBack();
            
            return [false, $e->getMessage(), 'Order creation failed', 500, $order];
        }
    }

    static public function createOrder($user, $address_id) {
        $cart = $user->cart;

        $cart->load(['items.product']);

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json([
                'message' => 'Cart is empty. Cannot create order.'
            ], 400);
        }

        [$success, $msg, $code, $address] = self::validateAddress($user, $address_id);

        if(!$success){
            return response()->json([
                'message' => $msg,
            ], $code);
        }

        [$success, $error, $msg, $code, $order] = self::createOrderDB($user, $address, $cart);

        if($success){
            $emailMsg = 'Your Order has been placed successfully at: ' . now()->format('Y-m-d H:i:s');
            self::sendOrderEmail($order, $emailMsg, 'Order Notification', 'Order Received');
            return response()->json([
                'message' => $msg
            ], $code);
        } else {
            $emailMsg = 'Your Order placement has been failed at: ' . now()->format('Y-m-d H:i:s') . '\nPlease try again later';
            self::sendOrderEmail($order, $emailMsg, 'Order Notification', 'Order Failed');
            return response()->json([
                'message' => $msg,
                'error' => $error
            ], $code);
        }
    }

    static private function removeOrderProductDB($order, $orderProduct){
        DB::beginTransaction();
        /**
         * 1- get the old order quantity 
         * 3- calculate new Product Quantity = current quantity + old order quantity
         * 5- calculate the new total_amount for the order and update the values.
         * 6- remove the order product
         * 7- update the inventory
         */

        try {
            $oldOrderQuantity = $orderProduct->pivot->quantity;

            $newProductQuantity = $orderProduct->stock_quantity + $oldOrderQuantity;

            $oldPrice = $orderProduct->price * $oldOrderQuantity;

            $order->total_amount -= $oldPrice;
                        
            $order->save();

            $orderProduct->pivot->delete(); // delete product from order items
            
            self::updateInventory($orderProduct, $newProductQuantity);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return [false, $e, 500, "Product removal from order failed"];
        }

        return [true, null, 200, "Product removed from order successfully"];        
    }

    static private function updateOrderProductDB($order, $newOrderQuantity, $orderProduct){
        DB::beginTransaction();
        /**
         * 1- get the old order quantity 
         * 2- get the new order quantity 
         * 3- calculate new Product Quantity = current quantity + old order quantity - new order quantity
         * 4- validate if the Inventory has this new quantity
         * 5- calculate the new total_amount for the order and update the values.
         * 6- update the order product quantity
         * 7- update the inventory
         */

        try {
            $oldOrderQuantity = $orderProduct->pivot->quantity;

            $newProductQuantity = $orderProduct->stock_quantity + $oldOrderQuantity - $newOrderQuantity;

            if($newProductQuantity < 0){
                DB::rollBack();

                return [false, null, 400, 'Product quantity is not available'];
            }

            $oldPrice = $orderProduct->price * $oldOrderQuantity;

            $order->total_amount -= $oldPrice;

            $newPrice = $orderProduct->price * $newOrderQuantity;

            $order->total_amount += $newPrice;
                        
            $order->save();
            
            self::updateInventory($orderProduct, $newProductQuantity);
            $orderProduct->pivot->quantity = $newOrderQuantity;
            $orderProduct->pivot->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return [false, $e, 500, "Product update in order failed"];
        }

        return [true, null, 200, "Product updated in order successfully"];
    }

    static private function validateOrderTransaction($order){
        if ($order->status !== OrderStatus::Pending) {
            return [false, 'Order cannot be updated as it is no longer pending', 400];
        }

        return [true, null, null];
    }

    static public function updateOrderProduct($order, $data){
        // validate the transaction is valid or not.
        [$success, $msg, $code] = self::validateOrderTransaction($order);

        if(!$success){
            return response()->json([
                'message' => $msg
            ], $code);
        }

        $productId   = (int) $data['product_id'];
        $newQuantity = (int) $data['quantity'];

        $orderProduct = $order->products()->where('products.id', $productId)->first();

        if (!$orderProduct) {
            return response()->json([
                'message' => 'Product not found in this order'
            ], 404);
        }

        if($orderProduct->pivot->quantity == $newQuantity){
            return response()->json([
                'message' => 'Product quantity is not changed'
            ], 400);
        }

        // start updating the product.
        $success = false;
        $error = null;
        $code = 500;
        $msg = 'Unknown error';

        if($newQuantity === 0){
            // if order doesn't have other products cancel the order.
            if($order->products()->count() === 1){
                return self::cancelOrder($order);
            }

            [$success, $error, $code, $msg] = self::removeOrderProductDB($order, $orderProduct);
        } else {
            [$success, $error, $code, $msg] = self::updateOrderProductDB($order,
                                                $newQuantity, $orderProduct);
        }

        if($success){
            $emailMsg = 'Your Order has been updated successfully at: ' . now()->format('Y-m-d H:i:s');
            self::sendOrderEmail($order, $emailMsg, 'Order Notification', 'Order Updated');
            return response()->json([
                'message' => $msg
            ], $code);
        } else {
            $emailMsg = 'Your Order update has been failed at: ' . now()->format('Y-m-d H:i:s') . '\nPlease try again later';
            self::sendOrderEmail($order, $emailMsg, 'Order Notification', 'Order Update Failed');
            return response()->json([
                'message' => $msg,
                'error' => $error->getMessage()
            ], $code);
        }
    }

    static public function cancelOrderDB($order){
        DB::beginTransaction();

        $order->status = OrderStatus::Cancelled;

        try{
            // update Inventory
            foreach($order->products as $orderProduct){
                $newQuantity = $orderProduct->pivot->quantity + $orderProduct->stock_quantity;
                self::updateInventory($orderProduct, $newQuantity);
            }

            $order->save();
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            return [false, $e->getMessage(), 500, 'Order cancellation failed'];
        }

        return [true, null, 'Order cancelled successfully', 200];
    }

    static public function cancelOrder($order){
        // validate the transaction is valid or not.
        [$success, $msg, $code] = self::validateOrderTransaction($order);

        if(!$success){
            return response()->json([
                'message' => $msg
            ], $code);
        }

        [$success, $error, $msg, $code] =  self::cancelOrderDB($order);

        if($success){
            $emailMsg = 'Your Order has been cancelled successfully at: ' . now()->format('Y-m-d H:i:s');
            self::sendOrderEmail($order, $emailMsg, 'Order Notification', 'Order Cancelled');
            return response()->json([
                'message' => $msg
            ], $code);
        } else {
            $emailMsg = 'Your Order cancellation has been failed at: ' . now()->format('Y-m-d H:i:s') . '\nPlease try again later';
            self::sendOrderEmail($order, $emailMsg, 'Order Notification', 'Order Cancellation Failed');
            return response()->json([
                'message' => $msg,
                'error' => $error->getMessage()
            ], $code);
        }
    }
}