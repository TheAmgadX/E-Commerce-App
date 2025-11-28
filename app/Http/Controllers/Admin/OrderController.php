<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Enums\OrderStatus;
use App\Services\OrderServices;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) {
        $query = Order::query()->with('user');

        if ($request->has('status')) {
            $status = $request->input('status');
            if ($status) {
                $query->where('status', $status);
            }
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(10);
        $statuses = OrderStatus::cases();

        return view('admin.orders.index', compact('orders', 'statuses'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order) {
        $order->load(['user', 'address', 'products.images']);
        $statuses = OrderStatus::cases();
        return view('admin.orders.show', compact('order', 'statuses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order) {
        $request->validate([
            'status' => ['required', 'string'],
            'comment' => ['nullable', 'string'],
        ]);

        $status = OrderStatus::tryFrom($request->status);
        
        if (!$status) {
             return back()->with('error', 'Invalid status.');
        }

        $order->update([
            'status' => $status,
        ]);

        // Send Email
        $emailMsg = "Your Order #{$order->id} status has been updated to {$status->value}.";
        if ($request->comment) {
            $emailMsg .= "\n\nAdmin Comment: " . $request->comment;
        }
        
        OrderServices::sendOrderEmail($order, $emailMsg, 'Order Status Update', 'Order Updated');

        return redirect()->route('admin.orders.show', $order)->with('success', 'Order updated successfully.');
    }
}
