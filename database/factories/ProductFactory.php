<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;
use App\Models\Product;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'description' => fake()->text(),
            'price' => fake()->numberBetween(100, 1000),
            'stock_quantity' => fake()->numberBetween(1, 100),
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Product $product) {
            $categories = Category::inRandomOrder()->limit(3)->get();
            if ($categories->isNotEmpty()) {
                $product->categories()->syncWithoutDetaching($categories->pluck('id'));
            }
        });
    }
}
