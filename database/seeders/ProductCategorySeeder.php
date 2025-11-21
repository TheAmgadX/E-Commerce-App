<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all();
        
        if ($categories->isEmpty()) {
            return;
        }

        Product::all()->each(function (Product $product) use ($categories) {
            $product->categories()->sync(
                $categories->random(3)->pluck('id')
            );
        });
    }
}
