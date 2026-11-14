<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Database;

final class RegistroPropiedad
{
    public static function listar(): array
    {
        return Database::conexion()->query('SELECT p.id_propiedad, p.direccion, p.precio_lista, p.habitaciones, p.banos, p.sqft, p.tipo_remodelacion, p.fecha_registro, u.nombre AS registrado_por FROM propiedad p INNER JOIN usuario u ON u.id_usuario = p.id_usuario_registro ORDER BY p.fecha_registro DESC, p.id_propiedad DESC LIMIT 250')->fetchAll();
    }
    public static function crear(array $d): void
    {
        $sql = 'INSERT INTO propiedad (direccion, precio_lista, habitaciones, banos, garaje, sqft, taxes_anuales, ultimo_valor_compra, fecha_listado, tipo_remodelacion, descripcion, id_usuario_registro) VALUES (:direccion, :precio_lista, :habitaciones, :banos, :garaje, :sqft, :taxes_anuales, :ultimo_valor_compra, :fecha_listado, :tipo_remodelacion, :descripcion, :id_usuario_registro)';
        Database::conexion()->prepare($sql)->execute($d);
    }
}
