<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Http\Controllers\Controller;

class AdminDashboardController extends Controller
{
    public function index(){
        $totalRevenue = Order::sum('total_amount');
        $totalOrders = Order::count();
        $totalProducts = Product::count();
        $totalCustomers = User::role('user')->count();
        
        $recentOrders = Order::with('user')->latest()->take(5)->get();
        $newCustomers = User::role('user')->latest()->take(5)->get();
        
        return view('admin.dashboard', compact('totalRevenue', 'totalOrders', 'totalProducts', 'totalCustomers', 'recentOrders', 'newCustomers'));
    }
}
