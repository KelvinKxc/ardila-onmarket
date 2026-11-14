<?php
declare(strict_types=1);

namespace App\Core;

use InvalidArgumentException;

/** Dibuja una vista dentro de un layout. */
final class View
{
    /** @param array<string,mixed> $datos */
    public static function mostrar(string $vista, array $datos = [], string $layout = 'layouts/main'): void
    {
        $contenido = self::capturar($vista, $datos);
        echo self::capturar($layout, $datos + ['contenido' => $contenido]);
    }

    /** @param array<string,mixed> $datos */
    private static function capturar(string $vista, array $datos): string
    {
        if (!preg_match('#^[a-z0-9_/-]+$#', $vista)) {
            throw new InvalidArgumentException('Nombre de vista no válido.');
        }
        $archivo = BASE_PATH . '/app/Views/' . $vista . '.php';
        extract($datos, EXTR_SKIP);
        ob_start();
        require $archivo;
        return (string) ob_get_clean();
    }
}
