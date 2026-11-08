<?php

namespace App\Enums;

enum EstadoTarea: string
{
    case Pendiente = 'pendiente';
    case EnProgreso = 'en_progreso';
    case Completada = 'completada';

    /**
     * Valores admitidos, útil para migraciones y documentación.
     *
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
