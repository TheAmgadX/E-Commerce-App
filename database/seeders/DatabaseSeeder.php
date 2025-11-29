<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Roles and Admin Users (Super Admin, Admin)
        $this->call(AdminSeeder::class);

        // 2. Create Regular Users (Customers)
        User::factory(10)->create();

        // 3. Create Categories
        $this->call(CategorySeeder::class);

        // 4. Create Products (and their images)
        $this->call(ProductSeeder::class);

        // 5. Link Products to Categories
        $this->call(ProductCategorySeeder::class);

        // 6. Create Addresses for ALL users
        $this->call(AddressSeeder::class);

        // 7. Create Carts for ALL users
        $this->call(CartSeeder::class);

        // 8. Add Items to Carts
        $this->call(CartItemSeeder::class);

        // 9. Orders (Currently empty, but ready for future implementation)
        // $this->call(OrderSeeder::class);
        // $this->call(OrderItemSeeder::class);
    }
}
