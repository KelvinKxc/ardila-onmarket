<?php
declare(strict_types=1);

use App\Core\Env;
use App\Core\Session;
use App\Core\View;

define('BASE_PATH', dirname(__DIR__));

// Autocarga: App\Core\Router -> app/Core/Router.php
spl_autoload_register(static function (string $clase): void {
    $prefijo = 'App\\';
    if (strncmp($clase, $prefijo, strlen($prefijo)) !== 0) {
        return;
    }
    $ruta = BASE_PATH . '/app/' . str_replace('\\', '/', substr($clase, strlen($prefijo))) . '.php';
    if (is_file($ruta)) {
        require $ruta;
    }
});

require BASE_PATH . '/app/helpers.php';

Env::cargar(BASE_PATH . '/.env');
date_default_timezone_set((string) config('zona_horaria'));

// Los errores van al log, no a la pantalla (salvo APP_DEBUG=true en desarrollo).
// Sin argumentos en las trazas: así una contraseña nunca queda escrita en un log.
error_reporting(E_ALL);
ini_set('display_errors', config('debug') ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', BASE_PATH . '/storage/logs/php-errores.log');
ini_set('zend.exception_ignore_args', '1');

set_exception_handler(static function (Throwable $e): void {
    registrar_log('ERROR', get_class($e) . ': ' . $e->getMessage() . ' en ' . basename($e->getFile()) . ':' . $e->getLine());

    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, 'Error: ' . $e->getMessage() . PHP_EOL);
        exit(1);
    }
    if (!headers_sent()) {
        http_response_code(500);
    }
    if (config('debug')) {
        echo '<pre>' . e($e->getMessage()) . '</pre>';
        return;
    }
    View::mostrar('errores/500', ['titulo' => 'Error del servidor'], 'layouts/main');
});

if (PHP_SAPI !== 'cli') {
    enviar_cabeceras_seguridad();
    Session::iniciar();
}
