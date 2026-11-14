<?php
declare(strict_types=1);

/**
 * Crea un usuario administrador. La contraseña se pide por la terminal (sin mostrarla)
 * y se guarda cifrada: no existe ninguna contraseña escrita en el código.
 *
 * Uso:  php bin/crear_admin.php
 */

require __DIR__ . '/lib/cli.php';
require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;
use App\Core\Validador;
use App\Models\Rol;
use App\Models\Usuario;

echo "== Crear usuario administrador ==\n\n";

try {
    Database::conexion();
} catch (RuntimeException $e) {
    salir($e->getMessage() . "\n¿Ya ejecutaste 'php bin/instalar.php'?");
}

$idRol = Rol::idPorNombre('administrador');
if ($idRol === null) {
    salir("No existe el rol 'administrador'. Ejecuta primero: php bin/instalar.php");
}

if (Usuario::contarPorRol('administrador') > 0
    && !confirmar('Ya existe al menos un administrador. ¿Crear otro?')) {
    salir('No se creó ningún usuario.', 0);
}

// Nombre de usuario
while (true) {
    $nombre = preguntar('Nombre de usuario (3 a 80 caracteres, sin espacios)');
    if (preg_match('/^[\p{L}\p{N}._-]{3,80}$/u', $nombre)) {
        break;
    }
    echo "  Usa solo letras, números, punto, guion o guion bajo.\n";
}

// Contraseña (dos veces, sin mostrarla)
$min = Validador::CLAVE_MINIMA;
echo "\nLa contraseña debe tener mínimo $min caracteres, con letras y números.\n";
while (true) {
    $clave1 = leerSecreto('Contraseña: ');
    $problemas = Validador::problemasClave($clave1, $nombre);
    if ($problemas) {
        echo '  - ' . implode("\n  - ", $problemas) . "\n";
        continue;
    }
    $clave2 = leerSecreto('Repite la contraseña: ');
    if (!hash_equals($clave1, $clave2)) {
        echo "  Las contraseñas no coinciden. Inténtalo de nuevo.\n";
        continue;
    }
    break;
}

$hash = password_hash($clave1, PASSWORD_DEFAULT);
unset($clave1, $clave2);

try {
    Usuario::crear($nombre, $hash, $idRol);
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        salir("Ya existe un usuario llamado '$nombre'. Elige otro nombre.");
    }
    throw $e;
}

echo "\nAdministrador '$nombre' creado correctamente.\n";
echo "Ya puedes entrar al sistema con ese usuario y la contraseña que acabas de escribir.\n";
