<?php
declare(strict_types=1);

namespace App\Core;

/** Token anti-CSRF: evita que otro sitio envíe formularios en nombre del usuario. */
final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public static function valido(?string $enviado): bool
    {
        return is_string($enviado)
            && !empty($_SESSION['_csrf'])
            && hash_equals($_SESSION['_csrf'], $enviado);
    }
}
