<?php
declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\PanelController;
use App\Core\Router;

$router = new Router();

// Públicas
$router->get('/login',  [AuthController::class, 'mostrarLogin'],   ['publica' => true]);
$router->post('/login', [AuthController::class, 'iniciarSesion'],  ['publica' => true]);

// Privadas (requieren sesión; 'roles' limita por rol)
$router->get('/',        [PanelController::class, 'inicio']);
$router->post('/logout', [AuthController::class, 'cerrarSesion']);

return $router;
