<?php
declare(strict_types=1);

/**
 * Instalación inicial (se ejecuta una sola vez):
 *   1. Crea la base de datos y las tablas.
 *   2. Crea un usuario de MySQL exclusivo para la aplicación, con permisos mínimos
 *      y una contraseña aleatoria.
 *   3. Guarda esa contraseña en el archivo .env (nunca se muestra ni va en el código).
 *
 * Uso:  php bin/instalar.php
 */

require __DIR__ . '/lib/cli.php';

$raiz = dirname(__DIR__);
$archivoEnv = $raiz . '/.env';

if (is_file($archivoEnv)) {
    salir("Ya existe el archivo .env, así que la instalación ya se hizo.\n"
        . "Si quieres reinstalar, renombra o borra .env y vuelve a ejecutar este script.", 0);
}

echo "== Instalación: Ardila Property · Propiedades On Market ==\n\n";
echo "Necesito una cuenta de MySQL con permiso para crear bases de datos.\n";
echo "En Laragon es 'root' y normalmente no tiene contraseña (deja vacío y pulsa Enter).\n\n";

$host   = preguntar('Servidor MySQL', '127.0.0.1');
$puerto = preguntar('Puerto', '3306');
$admin  = preguntar('Usuario administrador de MySQL', 'root');
$clave  = leerSecreto('Contraseña de ese usuario (puede estar vacía): ');

try {
    $pdo = new PDO("mysql:host=$host;port=$puerto;charset=utf8mb4", $admin, $clave, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    salir("No se pudo conectar a MySQL: " . $e->getMessage() . "\n¿Está iniciado MySQL en Laragon?");
}
unset($clave);

// 1) Base de datos y tablas
echo "\n[1/3] Creando la base de datos y las tablas... ";
$sql = file_get_contents($raiz . '/database/01_schema.sql');
if ($sql === false) {
    salir('No se pudo leer database/01_schema.sql');
}
foreach (dividirSentencias($sql) as $sentencia) {
    try {
        $pdo->exec($sentencia);
    } catch (PDOException $e) {
        salir("\nFalló esta sentencia del esquema:\n  " . substr(preg_replace('/\s+/', ' ', $sentencia) ?? '', 0, 90) . "...\nMySQL dice: " . $e->getMessage()
            . "\nCopia este mensaje y pásaselo a quien te esté ayudando.");
    }
}
echo "listo\n";

// 2) Usuario de MySQL solo para la aplicación (sin permisos de administrador)
echo "[2/3] Creando el usuario de MySQL de la aplicación (permisos mínimos)... ";
$usuarioApp = 'ardila_app';
$claveApp   = generarClave(24);
$claveSql   = $pdo->quote($claveApp);
foreach (['localhost', '127.0.0.1'] as $origen) {
    $pdo->exec("CREATE USER IF NOT EXISTS '$usuarioApp'@'$origen' IDENTIFIED BY $claveSql");
    $pdo->exec("ALTER USER '$usuarioApp'@'$origen' IDENTIFIED BY $claveSql");
    $pdo->exec("GRANT SELECT, INSERT, UPDATE, DELETE ON ardila_onmarket.* TO '$usuarioApp'@'$origen'");
}
$pdo->exec('FLUSH PRIVILEGES');
echo "listo\n";

// 3) Archivo .env con las credenciales
echo "[3/3] Guardando la configuración en .env... ";
$plantilla = file_get_contents($raiz . '/.env.example');
if ($plantilla === false) {
    salir('No se encontró .env.example');
}
$valores = ['DB_HOST' => $host, 'DB_PORT' => $puerto, 'DB_NAME' => 'ardila_onmarket', 'DB_USER' => $usuarioApp, 'DB_PASS' => $claveApp];
foreach ($valores as $clave => $valor) {
    $plantilla = preg_replace_callback('/^' . $clave . '=.*$/m', static fn () => "$clave=$valor", $plantilla) ?? $plantilla;
}
if (file_put_contents($archivoEnv, $plantilla, LOCK_EX) === false) {
    salir('No se pudo escribir el archivo .env');
}
@chmod($archivoEnv, 0600);
echo "listo\n";

// Comprobación: la aplicación debe poder entrar con su propio usuario
try {
    new PDO("mysql:host=$host;port=$puerto;dbname=ardila_onmarket;charset=utf8mb4", $usuarioApp, $claveApp, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    salir("La instalación terminó, pero el usuario de la aplicación no puede entrar: " . $e->getMessage());
}
unset($claveApp, $claveSql, $valores);

echo "\nInstalación completa.\n";
echo "Las credenciales de la base de datos quedaron SOLO en el archivo .env (no las compartas ni las subas a Git).\n";
echo "Siguiente paso:  php bin/crear_admin.php\n";
