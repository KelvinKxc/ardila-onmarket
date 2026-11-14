<?php
declare(strict_types=1);
use App\Controllers\AuthController;
use App\Controllers\PanelController;
use App\Controllers\UsuariosController;
use App\Controllers\PropiedadesController;
use App\Core\Router;

$router = new Router();
$router->get('/login', [AuthController::class, 'mostrarLogin'], ['publica' => true]);
$router->post('/login', [AuthController::class, 'iniciarSesion'], ['publica' => true]);
$router->get('/', [PanelController::class, 'inicio']);
$router->post('/logout', [AuthController::class, 'cerrarSesion']);
$router->get('/usuarios', [UsuariosController::class, 'index'], ['roles' => ['administrador']]);
$router->post('/usuarios/crear', [UsuariosController::class, 'crear'], ['roles' => ['administrador']]);
$router->post('/usuarios/estado', [UsuariosController::class, 'cambiarEstado'], ['roles' => ['administrador']]);
$router->get('/propiedades', [PropiedadesController::class, 'index'], ['roles' => ['administrador', 'analista', 'encargado']]);
$router->post('/propiedades/crear', [PropiedadesController::class, 'crear'], ['roles' => ['administrador', 'analista', 'encargado']]);
return $router;
