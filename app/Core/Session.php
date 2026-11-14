<?php
declare(strict_types=1);

namespace App\Core;

/** Sesión con cookie protegida y cierre automático por inactividad. */
final class Session
{
    public static function iniciar(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        session_name((string) config('sesion.nombre'));
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => es_https(),
            'httponly' => true,      // JavaScript no puede leer la cookie
            'samesite' => 'Lax',     // no se envía desde sitios externos en formularios
        ]);
        session_start();

        $maximo = max(1, (int) config('sesion.duracion_minutos')) * 60;
        if (isset($_SESSION['_ultima_actividad']) && time() - (int) $_SESSION['_ultima_actividad'] > $maximo) {
            self::destruir();
            session_start();
            self::flash('aviso', 'Tu sesión expiró por inactividad. Ingresa de nuevo.');
        }
        $_SESSION['_ultima_actividad'] = time();
    }

    /** Cambia el identificador de sesión (se hace al iniciar sesión). */
    public static function regenerar(): void
    {
        session_regenerate_id(true);
    }

    public static function destruir(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires'  => time() - 3600,
                'path'     => $p['path'],
                'secure'   => $p['secure'],
                'httponly' => $p['httponly'],
                'samesite' => $p['samesite'] ?: 'Lax',
            ]);
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public static function flash(string $tipo, string $mensaje): void
    {
        $_SESSION['_flash'][] = ['tipo' => $tipo, 'mensaje' => $mensaje];
    }

    /** @return array<int,array{tipo:string,mensaje:string}> */
    public static function tomarFlashes(): array
    {
        $f = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $f;
    }
}
