<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\ProductMetric;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory, SoftDeletes;


    protected $fillable = [
        'name',
        'description',
        'price',
        'stock_quantity',
        'metric',
    ];

    protected $hidden = [
        'stock_quantity',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'metric' => ProductMetric::class,
    ];

    public function categories(){
        return $this->belongsToMany(Category::class, 'product_categories');
    }

    public function images(){
        return $this->hasMany(ProductImage::class);
    }

    public function wishlistedBy(){
        return $this->belongsToMany(User::class, 'wishlist');
    }

    public function orders(){
        return $this->belongsToMany(Order::class, 'order_items')->withPivot('quantity');
    }

    public function orderItems(){
        return $this->hasMany(OrderItem::class);
    }

    public function cartItems(){
        return $this->hasMany(CartItems::class);
    }
}
