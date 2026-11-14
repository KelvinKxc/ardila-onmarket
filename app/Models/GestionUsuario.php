<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database;

final class GestionUsuario
{
    public static function roles(): array
    {
        return Database::conexion()->query('SELECT id_rol, nombre FROM rol ORDER BY id_rol')->fetchAll();
    }
    public static function listar(): array
    {
        return Database::conexion()->query('SELECT u.id_usuario, u.nombre, u.nombre_completo, u.activo, r.nombre AS rol FROM usuario u JOIN rol r ON r.id_rol=u.id_rol ORDER BY u.id_usuario DESC')->fetchAll();
    }
    public static function crear(string $nombreCompleto, string $nombre, string $hash, int $idRol): void
    {
        $st = Database::conexion()->prepare('INSERT INTO usuario (nombre_completo, nombre, contrasena_hash, id_rol, activo) VALUES (:nombre_completo, :nombre, :hash, :rol, 1)');
        $st->execute(['nombre_completo' => $nombreCompleto, 'nombre' => $nombre, 'hash' => $hash, 'rol' => $idRol]);
    }
    public static function cambiarEstado(int $id, int $activo): void
    {
        $st = Database::conexion()->prepare('UPDATE usuario SET activo = :activo WHERE id_usuario = :id');
        $st->execute(['activo' => $activo, 'id' => $id]);
    }
}
