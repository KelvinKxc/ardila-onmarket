<?php
declare(strict_types=1);

use App\Core\Csrf;
use App\Core\Session;

/** Lee la configuración con notación de puntos: config('db.host'). */
function config(string $clave, mixed $defecto = null): mixed
{
    static $cfg = null;
    $cfg ??= require BASE_PATH . '/config/app.php';

    $valor = $cfg;
    foreach (explode('.', $clave) as $parte) {
        if (!is_array($valor) || !array_key_exists($parte, $valor)) {
            return $defecto;
        }
        $valor = $valor[$parte];
    }
    return $valor;
}

/** Escapa texto para mostrarlo en HTML (evita XSS). Úsala SIEMPRE al imprimir datos. */
function e(?string $texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function es_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['SERVER_PORT'] ?? '') === '443';
}

/** Carpeta base de la URL (vacía si el sitio está en la raíz del dominio). */
function ruta_base(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }
    $script = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    $uri = rawurldecode((string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'));

    // Si Apache reescribió la petición hacia /public, la carpeta visible es la anterior
    if ($script !== '' && !str_starts_with($uri, $script) && str_ends_with($script, '/public')) {
        $script = substr($script, 0, -7);
    }
    return $base = $script;
}

/** Ruta de la petición sin la carpeta base: "/login", "/", etc. */
function request_path(): string
{
    $uri = rawurldecode((string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'));
    $base = ruta_base();
    if ($base !== '' && str_starts_with($uri, $base)) {
        $uri = substr($uri, strlen($base));
    }
    return '/' . trim($uri, '/');
}

function url(string $ruta = ''): string
{
    return ruta_base() . '/' . ltrim($ruta, '/');
}

function asset(string $ruta): string
{
    return url('assets/' . ltrim($ruta, '/'));
}

function redirigir(string $ruta): never
{
    header('Location: ' . url($ruta));
    exit;
}

function csrf_campo(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(Csrf::token()) . '">';
}

function usuario_actual(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

function flash(string $tipo, string $mensaje): void
{
    Session::flash($tipo, $mensaje);
}

function registrar_log(string $nivel, string $mensaje): void
{
    $carpeta = BASE_PATH . '/storage/logs';
    if (!is_dir($carpeta)) {
        @mkdir($carpeta, 0750, true);
    }
    $linea = sprintf("[%s] %s %s\n", date('Y-m-d H:i:s'), $nivel, str_replace(["\r", "\n"], ' ', $mensaje));
    @file_put_contents($carpeta . '/app-' . date('Y-m-d') . '.log', $linea, FILE_APPEND | LOCK_EX);
}

function enviar_cabeceras_seguridad(): void
{
    header_remove('X-Powered-By');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: same-origin');
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; frame-ancestors 'none'; form-action 'self'; base-uri 'self'");
    header('Cache-Control: no-store');
    if (es_https()) {
        header('Strict-Transport-Security: max-age=31536000');
    }
}
