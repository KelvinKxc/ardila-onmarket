<?php
declare(strict_types=1);

// Único punto de entrada de la aplicación (front controller).
require_once dirname(__DIR__) . '/app/bootstrap.php';

$router = require BASE_PATH . '/app/routes.php';
$router->despachar($_SERVER['REQUEST_METHOD'] ?? 'GET', request_path());
