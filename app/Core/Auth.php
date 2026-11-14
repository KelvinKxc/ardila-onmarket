<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\Usuario;

/** Inicio y cierre de sesión, y control de acceso por rol. */
final class Auth
{
    public static function intentar(string $nombre, string $clave): bool
    {
        $usuario = Usuario::buscarPorNombre($nombre);

        // Se verifica SIEMPRE un hash (aunque el usuario no exista) para que el tiempo
        // de respuesta no revele si el nombre de usuario es real.
        $hash = $usuario['contrasena_hash'] ?? self::hashFalso();
        $coincide = password_verify($clave, $hash);

        if ($usuario === null || !$coincide || !(bool) $usuario['activo']) {
            return false;
        }

        if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
            Usuario::actualizarHash((int) $usuario['id_usuario'], password_hash($clave, PASSWORD_DEFAULT));
        }

        Session::regenerar(); // evita la fijación de sesión
        $_SESSION['usuario'] = [
            'id'     => (int) $usuario['id_usuario'],
            'nombre' => $usuario['nombre'],
            'rol'    => $usuario['rol'],
        ];
        return true;
    }

    public static function cerrar(): void
    {
        Session::destruir();
        Session::iniciar();
    }

    public static function exigirSesion(): void
    {
        if (usuario_actual() === null) {
            redirigir('login');
        }
    }

    /** @param string[] $roles */
    public static function exigirRol(array $roles): void
    {
        $usuario = usuario_actual();
        if ($usuario === null || !in_array($usuario['rol'], $roles, true)) {
            http_response_code(403);
            View::mostrar('errores/error', ['titulo' => 'Sin permiso', 'mensaje' => 'Tu rol no tiene acceso a esta sección.']);
            exit;
        }
    }

    private static function hashFalso(): string
    {
        static $hash = null;
        return $hash ??= password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
    }
}
