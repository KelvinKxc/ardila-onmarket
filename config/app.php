<?php
declare(strict_types=1);

use App\Core\Env;

// Toda la configuración sale del archivo .env (fuera del código y de Git).
return [
    'nombre'       => 'Ardila Property · Propiedades On Market',
    'entorno'      => Env::obtener('APP_ENV', 'production'),
    'debug'        => Env::booleano('APP_DEBUG', false),
    'zona_horaria' => Env::obtener('APP_TIMEZONE', 'America/Bogota'),
    'sesion'       => [
        'nombre'            => 'ARDILA_SESION',
        'duracion_minutos'  => (int) Env::obtener('SESSION_MINUTES', '30'),
    ],
    'db' => [
        'host'    => Env::obtener('DB_HOST', '127.0.0.1'),
        'puerto'  => Env::obtener('DB_PORT', '3306'),
        'nombre'  => Env::obtener('DB_NAME', 'ardila_onmarket'),
        'usuario' => Env::obtener('DB_USER', 'ardila_app'),
        'clave'   => Env::obtener('DB_PASS', ''),
    ],
];
