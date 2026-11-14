<h1>Administración de usuarios</h1>
<p class="suave">Solo los administradores pueden crear cuentas y cambiar su estado.</p>
<p><a class="enlace" href="<?= e(url('')) ?>">← Volver al panel</a></p>
<div class="modulo-dos-columnas">
<section class="tarjeta">
<h2>Crear nuevo usuario</h2>
<p class="suave">Completa los datos para dar acceso al sistema.</p>
<form method="post" action="<?= e(url('usuarios/crear')) ?>">
<?= csrf_campo() ?>
<label for="nombre_completo">Nombre completo del empleado *</label>
<input id="nombre_completo" name="nombre_completo" type="text" minlength="3" maxlength="120" autocomplete="name" placeholder="Ejemplo: Juan Carlos Pérez" required>
<small>Escribe el nombre y los apellidos del empleado. Este nombre aparecerá en el listado y permitirá identificar quién registra cada propiedad.</small>
<label for="nombre">Nombre de usuario para iniciar sesión *</label>
<input id="nombre" name="nombre" type="text" minlength="3" maxlength="40" pattern="[A-Za-z0-9._-]{3,40}" autocomplete="off" placeholder="ejemplo: analista01" required>
<small>Entre 3 y 40 caracteres, sin espacios. Puedes usar letras, números, punto, guion y guion bajo. Debe ser único.</small>
<label for="rol">Rol del usuario *</label>
<select id="rol" name="id_rol" required>
<option value="">Selecciona un rol</option>
<?php foreach ($roles as $r): ?>
<option value="<?= (int) $r['id_rol'] ?>"><?= e(ucfirst($r['nombre'])) ?></option>
<?php endforeach; ?>
</select>
<small><strong>Administrador:</strong> crea cuentas y propiedades. <strong>Analista:</strong> registra propiedades. <strong>Encargado:</strong> registra y consulta propiedades.</small>
<label for="clave">Contraseña inicial *</label>
<input id="clave" name="clave" type="password" minlength="12" maxlength="128" autocomplete="new-password" required>
<small>Mínimo 12 caracteres, con mayúscula, minúscula, número y símbolo. No reutilices la contraseña de admin.</small>
<label for="confirmacion">Confirmar contraseña *</label>
<input id="confirmacion" name="confirmacion" type="password" minlength="12" maxlength="128" autocomplete="new-password" required>
<button class="btn btn-primario" type="submit">Crear usuario</button>
</form>
</section>
<section class="tarjeta">
<h2>Usuarios registrados</h2>
<div class="tabla-scroll"><table><thead><tr><th>Empleado</th><th>Usuario de acceso</th><th>Rol</th><th>Estado</th><th>Acción</th></tr></thead><tbody>
<?php foreach ($usuarios as $u): ?>
<tr><td><?= e($u['nombre_completo'] ?: $u['nombre']) ?></td><td><?= e($u['nombre']) ?></td><td><?= e($u['rol']) ?></td><td><?= (int)$u['activo'] ? 'Activo' : 'Inactivo' ?></td>
<td><?php if ((int)$u['id_usuario'] !== (int)(usuario_actual()['id'] ?? 0)): ?>
<form method="post" action="<?= e(url('usuarios/estado')) ?>">
<?= csrf_campo() ?>
<input type="hidden" name="id_usuario" value="<?= (int)$u['id_usuario'] ?>">
<input type="hidden" name="activo" value="<?= (int)$u['activo'] ? '0' : '1' ?>">
<button class="btn btn-claro" type="submit"><?= (int)$u['activo'] ? 'Desactivar' : 'Activar' ?></button>
</form>
<?php else: ?><span class="etiqueta">Tu cuenta</span><?php endif; ?></td></tr>
<?php endforeach; ?>
</tbody></table></div>
<p class="suave"><small>Desactivar impide nuevos inicios de sesión. Para cerrar sesiones ya abiertas se requiere agregar revocación de sesión.</small></p>
</section>
</div>
