<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Usuario
{
    /** @return array<string,mixed>|null */
    public static function buscarPorNombre(string $nombre): ?array
    {
        $st = Database::conexion()->prepare(
            'SELECT u.id_usuario, u.nombre, u.contrasena_hash, u.activo, r.nombre AS rol
               FROM usuario u
               JOIN rol r ON r.id_rol = u.id_rol
              WHERE u.nombre = :nombre
              LIMIT 1'
        );
        $st->execute(['nombre' => $nombre]);
        $fila = $st->fetch();
        return $fila === false ? null : $fila;
    }

    public static function crear(string $nombre, string $hash, int $idRol): int
    {
        $pdo = Database::conexion();
        $st = $pdo->prepare('INSERT INTO usuario (id_rol, nombre, contrasena_hash) VALUES (:rol, :nombre, :hash)');
        $st->execute(['rol' => $idRol, 'nombre' => $nombre, 'hash' => $hash]);
        return (int) $pdo->lastInsertId();
    }

    public static function actualizarHash(int $idUsuario, string $hash): void
    {
        $st = Database::conexion()->prepare('UPDATE usuario SET contrasena_hash = :hash WHERE id_usuario = :id');
        $st->execute(['hash' => $hash, 'id' => $idUsuario]);
    }

    public static function contarPorRol(string $rol): int
    {
        $st = Database::conexion()->prepare(
            'SELECT COUNT(*) FROM usuario u JOIN rol r ON r.id_rol = u.id_rol WHERE r.nombre = :rol'
        );
        $st->execute(['rol' => $rol]);
        return (int) $st->fetchColumn();
    }
}
