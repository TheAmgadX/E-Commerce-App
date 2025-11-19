<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CartItem;
use App\Models\Cart;

class CartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = \App\Models\User::all();

        foreach ($users as $user) {

            // If the user already has a cart, skip factory execution
            if ($user->cart) {
                continue;
            }

            // This triggers the factory
            Cart::factory()->create([
                'user_id' => $user->id,
            ]);
        }

    }
}
