<?php

namespace App\Policies;

use App\Models\Tarea;
use App\Models\User;

/**
 * Un usuario solo puede ver, modificar o eliminar sus propias tareas.
 */
class TareaPolicy
{
    public function view(User $user, Tarea $tarea): bool
    {
        return $tarea->user_id === $user->id;
    }

    public function update(User $user, Tarea $tarea): bool
    {
        return $tarea->user_id === $user->id;
    }

    public function delete(User $user, Tarea $tarea): bool
    {
        return $tarea->user_id === $user->id;
    }
}
