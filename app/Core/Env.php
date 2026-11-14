<?php
declare(strict_types=1);

namespace App\Core;

/** Lee las variables del archivo .env (credenciales fuera del código). */
final class Env
{
    /** @var array<string,string> */
    private static array $variables = [];

    public static function cargar(string $archivo): void
    {
        if (!is_file($archivo) || !is_readable($archivo)) {
            return;
        }
        foreach (file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $linea) {
            $linea = trim($linea);
            if ($linea === '' || $linea[0] === '#' || !str_contains($linea, '=')) {
                continue;
            }
            [$clave, $valor] = explode('=', $linea, 2);
            $valor = trim($valor);
            if (strlen($valor) >= 2 && ($valor[0] === '"' || $valor[0] === "'") && $valor[-1] === $valor[0]) {
                $valor = substr($valor, 1, -1);
            }
            self::$variables[trim($clave)] = $valor;
        }
    }

    public static function obtener(string $clave, ?string $defecto = null): ?string
    {
        return self::$variables[$clave] ?? $defecto;
    }

    public static function booleano(string $clave, bool $defecto = false): bool
    {
        $v = self::$variables[$clave] ?? null;
        return $v === null ? $defecto : in_array(strtolower($v), ['1', 'true', 'si', 'yes', 'on'], true);
    }
}
