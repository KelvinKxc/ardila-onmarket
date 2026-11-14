<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Rol
{
    public static function idPorNombre(string $nombre): ?int
    {
        $st = Database::conexion()->prepare('SELECT id_rol FROM rol WHERE nombre = :nombre LIMIT 1');
        $st->execute(['nombre' => $nombre]);
        $id = $st->fetchColumn();
        return $id === false ? null : (int) $id;
    }
}
