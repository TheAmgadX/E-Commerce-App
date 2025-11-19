<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Cart;
use App\Models\Product;

class CartItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'cart_id' => null,
            'product_id' => Product::inRandomOrder()->first()?->id ?? Product::factory(),
            'quantity' => fake()->numberBetween(1, 10),
        ];
    }
}