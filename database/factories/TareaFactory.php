<?php

namespace Database\Factories;

use App\Enums\EstadoTarea;
use App\Models\Tarea;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tarea>
 */
class TareaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'titulo' => rtrim(fake()->sentence(4), '.'),
            'descripcion' => fake()->optional()->paragraph(),
            'estado' => fake()->randomElement(EstadoTarea::cases()),
            'fecha_limite' => fake()->dateTimeBetween('now', '+2 months')->format('Y-m-d'),
        ];
    }

    public function pendiente(): static
    {
        return $this->state(['estado' => EstadoTarea::Pendiente]);
    }

    public function enProgreso(): static
    {
        return $this->state(['estado' => EstadoTarea::EnProgreso]);
    }

    public function completada(): static
    {
        return $this->state(['estado' => EstadoTarea::Completada]);
    }
}
