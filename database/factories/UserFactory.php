<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
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
            'telefono' => fake()->unique()->numerify('3#########'),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role_id' => Role::where('nombre', 'solicitante')->value('id') ?? 1,
            'activo' => true,
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

    public function administrador(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => Role::where('nombre', 'administrador')->value('id'),
        ]);
    }

    public function agricultor(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => Role::where('nombre', 'agricultor')->value('id'),
        ]);
    }

    public function tecnico(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => Role::where('nombre', 'tecnico')->value('id'),
        ]);
    }

    public function solicitante(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => Role::where('nombre', 'solicitante')->value('id'),
        ]);
    }

    public function inactivo(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => false,
        ]);
    }
}
