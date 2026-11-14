<?php
declare(strict_types=1);

namespace App\Core;

/** Reglas de validación reutilizables. */
final class Validador
{
    public const CLAVE_MINIMA = 12;
    public const CLAVE_MAXIMA_BYTES = 72; // límite real de bcrypt

    /** @return string[] lista de problemas (vacía = contraseña aceptable) */
    public static function problemasClave(string $clave, string $nombreUsuario = ''): array
    {
        $p = [];
        if (strlen($clave) < self::CLAVE_MINIMA) {
            $p[] = 'Debe tener al menos ' . self::CLAVE_MINIMA . ' caracteres.';
        }
        if (strlen($clave) > self::CLAVE_MAXIMA_BYTES) {
            $p[] = 'No puede superar ' . self::CLAVE_MAXIMA_BYTES . ' bytes.';
        }
        if (!preg_match('/\p{L}/u', $clave) || !preg_match('/\d/', $clave)) {
            $p[] = 'Debe combinar letras y números.';
        }
        if ($nombreUsuario !== '' && stripos($clave, $nombreUsuario) !== false) {
            $p[] = 'No puede contener el nombre de usuario.';
        }
        return $p;
    }
}
