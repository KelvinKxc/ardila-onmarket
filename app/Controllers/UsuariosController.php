<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\View;
use App\Models\GestionUsuario;
use PDOException;

final class UsuariosController
{
    public function index(): void
    {
        View::mostrar('usuarios/index', [
            'titulo' => 'Administración de usuarios',
            'usuarios' => GestionUsuario::listar(),
            'roles' => GestionUsuario::roles(),
        ]);
    }

    public function crear(): void
    {
        $nombreCompleto = trim((string) ($_POST['nombre_completo'] ?? ''));
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $clave = (string) ($_POST['clave'] ?? '');
        $confirmacion = (string) ($_POST['confirmacion'] ?? '');
        $rol = (int) ($_POST['id_rol'] ?? 0);
        if (mb_strlen($nombreCompleto, 'UTF-8') < 3 || mb_strlen($nombreCompleto, 'UTF-8') > 120 || preg_match('/[\x00-\x1F\x7F]/', $nombreCompleto)) {
            flash('error', 'El nombre completo debe tener entre 3 y 120 caracteres y no contener caracteres de control.');
            redirigir('usuarios');
        }
        if (!preg_match('/^[a-zA-Z0-9._-]{3,40}$/D', $nombre)) {
            flash('error', 'El usuario debe tener entre 3 y 40 caracteres: letras, números, puntos, guiones o guion bajo.');
            redirigir('usuarios');
        }
        if (strlen($clave) < 12 || strlen($clave) > 128 || !preg_match('/[A-Z]/', $clave) || !preg_match('/[a-z]/', $clave) || !preg_match('/[0-9]/', $clave) || !preg_match('/[^a-zA-Z0-9]/', $clave)) {
            flash('error', 'La contraseña debe tener entre 12 y 128 caracteres, mayúscula, minúscula, número y símbolo.');
            redirigir('usuarios');
        }
        if ($clave !== $confirmacion) {
            flash('error', 'Las contraseñas no coinciden.');
            redirigir('usuarios');
        }
        if (!in_array($rol, array_column(GestionUsuario::roles(), 'id_rol'), false)) {
            flash('error', 'Selecciona un rol válido.');
            redirigir('usuarios');
        }
        try {
            GestionUsuario::crear($nombreCompleto, $nombre, password_hash($clave, PASSWORD_DEFAULT), $rol);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                flash('error', 'Ese nombre de usuario ya está registrado.');
            } else {
                registrar_log('ERROR', 'Crear usuario: ' . $e->getMessage());
                flash('error', 'No fue posible crear el usuario.');
            }
            redirigir('usuarios');
        }
        flash('exito', 'Usuario creado. Ya puede iniciar sesión con sus credenciales.');
        redirigir('usuarios');
    }

    public function cambiarEstado(): void
    {
        $id = filter_var($_POST['id_usuario'] ?? null, FILTER_VALIDATE_INT);
        $activo = (string) ($_POST['activo'] ?? '');
        if (!$id || !in_array($activo, ['0', '1'], true)) {
            flash('error', 'Solicitud inválida.');
            redirigir('usuarios');
        }
        if ($id === (int) (usuario_actual()['id'] ?? 0)) {
            flash('error', 'No puedes desactivar tu propia cuenta.');
            redirigir('usuarios');
        }
        GestionUsuario::cambiarEstado($id, (int) $activo);
        flash('exito', 'Estado del usuario actualizado.');
        redirigir('usuarios');
    }
}
