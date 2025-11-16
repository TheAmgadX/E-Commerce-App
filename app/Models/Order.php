<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\OrderStatus;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'address_id',
        'total_amount',
        'status',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function address(){
        return $this->belongsTo(Address::class);
    }

    public function items(){
        return $this->hasMany(OrderItem::class, 'order_items');
    }

    public function products(){
        return $this->belongsToMany(Product::class, 'order_items')->withPivot('quantitiy');
    }
}
