<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get all existing users
        $users = User::all();

        // Loop through all users and create 1 address for them
        foreach ($users as $user) {
            Address::factory()->create([
                'user_id' => $user->id,
            ]);
        }

        $this->command->info('Successfully created 1 address for each of the ' . $users->count() . ' users.');
    }
}