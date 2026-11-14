<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;

final class AuthController
{
    public function mostrarLogin(): void
    {
        if (usuario_actual() !== null) {
            redirigir('');
        }
        View::mostrar('auth/login', ['titulo' => 'Iniciar sesión']);
    }

    public function iniciarSesion(): void
    {
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $clave  = (string) ($_POST['clave'] ?? '');

        if ($nombre === '' || $clave === '' || strlen($nombre) > 80 || strlen($clave) > 1000) {
            flash('error', 'Escribe tu usuario y tu contraseña.');
            redirigir('login');
        }

        if (!Auth::intentar($nombre, $clave)) {
            usleep(random_int(200_000, 450_000)); // frena los intentos automáticos
            registrar_log('AVISO', 'Intento de acceso fallido para el usuario "' . substr($nombre, 0, 40) . '" desde ' . ($_SERVER['REMOTE_ADDR'] ?? '?'));
            flash('error', 'Usuario o contraseña incorrectos.'); // mensaje genérico a propósito
            redirigir('login');
        }

        redirigir('');
    }

    public function cerrarSesion(): void
    {
        Auth::cerrar();
        flash('exito', 'Cerraste sesión correctamente.');
        redirigir('login');
    }
}
