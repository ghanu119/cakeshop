<?php

namespace Database\Factories;

use App\Support\AuthGuards;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function customer(): static
    {
        return $this->state(fn (array $attributes) => [
            'password' => null,
            'registered_via' => 'front_otp',
        ])->afterCreating(function (\App\Models\User $user) {
            $user->assignRole(Role::findByName('Customer', AuthGuards::STAFF));
        });
    }

    /**
     * Indicate the customer verified via WhatsApp (phone-only, no email).
     */
    public function whatsappVerified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email' => null,
            'email_verified_at' => null,
            'phone' => (string) fake()->numerify('9#########'),
            'phone_verified_at' => now(),
            'whatsapp_verified_at' => now(),
        ]);
    }
}
