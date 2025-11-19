<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class CartItem extends Model
{
    use HasFactory;

    protected $table = 'cart_items';
    public $timestamps = false;

    public $fillable = [
        'cart_id',
        'product_id',
        'quantity',
    ];

    public function cart(){
        return $this->belongsTo(Cart::class, 'cart_id');
    }

    public function product(){
        return $this->belongsTo(Product::class, 'product_id');
    }
}
