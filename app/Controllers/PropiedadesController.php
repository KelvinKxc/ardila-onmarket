<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\View;
use App\Models\RegistroPropiedad;
use PDOException;

final class PropiedadesController
{
    public function index(): void
    {
        View::mostrar('propiedades/index', [
            'titulo' => 'Propiedades On Market',
            'propiedades' => RegistroPropiedad::listar(),
        ]);
    }
    public function crear(): void
    {
        $campos = ['direccion', 'precio_lista', 'habitaciones', 'banos', 'garaje', 'sqft', 'taxes_anuales', 'ultimo_valor_compra', 'fecha_listado', 'tipo_remodelacion', 'descripcion'];
        $datos = [];
        foreach ($campos as $campo) {
            $datos[$campo] = trim((string) ($_POST[$campo] ?? ''));
        }
        if (mb_strlen($datos['direccion']) < 8 || mb_strlen($datos['direccion']) > 150) {
            flash('error', 'Escribe una dirección completa (entre 8 y 150 caracteres).');
            redirigir('propiedades');
        }
        foreach (['precio_lista', 'habitaciones', 'sqft', 'taxes_anuales', 'ultimo_valor_compra'] as $campo) {
            if ($datos[$campo] !== '' && (!ctype_digit($datos[$campo]) || (float) $datos[$campo] > 4294967295)) {
                flash('error', 'Revisa los campos numéricos: deben ser cantidades positivas sin signos ni comas.');
                redirigir('propiedades');
            }
        }
        if ($datos['precio_lista'] === '' || (int) $datos['precio_lista'] < 1 || ($datos['habitaciones'] !== '' && (int) $datos['habitaciones'] > 255)) {
            flash('error', 'Precio obligatorio mayor que cero; habitaciones entre 0 y 255.');
            redirigir('propiedades');
        }
        if ($datos['banos'] !== '' && (!preg_match('/^\d{1,2}(\.\d)?$/', $datos['banos']) || (float) $datos['banos'] > 99.9)) {
            flash('error', 'Baños debe ser un número válido, por ejemplo 1, 1.5 o 2.');
            redirigir('propiedades');
        }
        if (!in_array($datos['garaje'], ['si', 'no', 'no_se'], true) || !in_array($datos['tipo_remodelacion'], ['', 'liviana', 'mediana', 'pesada'], true)) {
            flash('error', 'Selecciona opciones válidas de garaje y remodelación.');
            redirigir('propiedades');
        }
        if ($datos['fecha_listado'] !== '') {
            $fecha = \DateTimeImmutable::createFromFormat('!Y-m-d', $datos['fecha_listado']);
            if (!$fecha || $fecha->format('Y-m-d') !== $datos['fecha_listado']) {
                flash('error', 'La fecha de listado no es válida.');
                redirigir('propiedades');
            }
        }
        if (mb_strlen($datos['descripcion']) > 10000) {
            flash('error', 'La descripción no puede superar 10.000 caracteres.');
            redirigir('propiedades');
        }
        foreach (['habitaciones', 'banos', 'sqft', 'taxes_anuales', 'ultimo_valor_compra', 'fecha_listado', 'tipo_remodelacion', 'descripcion'] as $campo) {
            if ($datos[$campo] === '') $datos[$campo] = null;
        }
        $datos['id_usuario_registro'] = (int) (usuario_actual()['id'] ?? 0);
        try {
            RegistroPropiedad::crear($datos);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                flash('error', 'Ya existe una propiedad con esa dirección. Revisa el listado.');
            } else {
                registrar_log('ERROR', 'Crear propiedad: ' . $e->getMessage());
                flash('error', 'No fue posible guardar la propiedad.');
            }
            redirigir('propiedades');
        }
        flash('exito', 'Propiedad registrada correctamente.');
        redirigir('propiedades');
    }
}
