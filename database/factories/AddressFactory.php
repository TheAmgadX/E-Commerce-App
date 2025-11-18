<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Address::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $phoneNumbers = [];
        $count = $this->faker->numberBetween(1, 3);
        
        for ($i = 0; $i < $count; $i++) {
            // Generates a mock phone number like +2010XXXXXXXX
            $phoneNumbers[] = '+20' . $this->faker->numberBetween(100000000, 129999999);
        }

        return [
            'country' => $this->faker->country(),
            'city' => $this->faker->city(),
            'street' => $this->faker->streetAddress() . ', Apt ' . $this->faker->buildingNumber(),
            'phone_numbers' => $phoneNumbers,
        ];
    }
}