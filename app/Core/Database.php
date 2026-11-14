<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

/** Conexión única a MySQL con PDO. Todas las consultas usan parámetros (sin inyección SQL). */
final class Database
{
    private static ?PDO $pdo = null;

    public static function conexion(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            config('db.host'),
            config('db.puerto'),
            config('db.nombre')
        );

        try {
            self::$pdo = new PDO($dsn, (string) config('db.usuario'), (string) config('db.clave'), [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            // El detalle va al log; al usuario solo un mensaje genérico (sin datos de conexión).
            registrar_log('ERROR', 'Conexión a la base de datos: ' . $e->getMessage());
            throw new RuntimeException('No se pudo conectar a la base de datos. Revisa el archivo .env y que MySQL esté iniciado.');
        }
        return self::$pdo;
    }

    /** Permite reemplazar la conexión (por ejemplo en pruebas automáticas). */
    public static function usar(PDO $pdo): void
    {
        self::$pdo = $pdo;
    }
}
