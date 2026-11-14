<?php /** @var array $usuario */ ?>
<h1>Hola, <?= e($usuario['nombre']) ?></h1>
<p class="suave">Ingresaste como <strong><?= e($usuario['rol']) ?></strong>.</p>
<div class="rejilla">
    <article class="tarjeta">
        <h2>Propiedades</h2>
        <p class="suave">Registra y consulta propiedades On Market. Cada registro queda asociado a tu usuario.</p>
        <a class="btn btn-primario" href="<?= e(url('propiedades')) ?>">Abrir propiedades</a>
    </article>
    <article class="tarjeta">
        <h2>Comparables y ofertas</h2>
        <p class="suave">Promedio de comparables, oferta máxima y seguimiento.</p>
        <span class="etiqueta">Próximamente</span>
    </article>
    <?php if ($usuario['rol'] === 'administrador'): ?>
    <article class="tarjeta">
        <h2>Usuarios</h2>
        <p class="suave">Crea usuarios, asigna roles y administra el acceso.</p>
        <a class="btn btn-primario" href="<?= e(url('usuarios')) ?>">Administrar usuarios</a>
    </article>
    <?php endif; ?>
</div>
