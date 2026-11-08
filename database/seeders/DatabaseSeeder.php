<?php

namespace Database\Seeders;

use App\Models\Tarea;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Usuario de prueba documentado en el README (contraseña: "password")
        $usuario = User::factory()->create([
            'name' => 'Usuario de Prueba',
            'email' => 'test@example.com',
        ]);

        Tarea::factory()->count(5)->pendiente()->for($usuario)->create();
        Tarea::factory()->count(3)->enProgreso()->for($usuario)->create();
        Tarea::factory()->count(4)->completada()->for($usuario)->create();

        // Otro usuario con sus propias tareas: sirve para comprobar que cada uno solo ve las suyas
        Tarea::factory()->count(3)->create();
    }
}
