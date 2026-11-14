<?php /** @var array $usuario */ ?>
<h1>Hola, <?= e($usuario['nombre']) ?></h1>
<p class="suave">Ingresaste como <strong><?= e($usuario['rol']) ?></strong>.</p>

<div class="rejilla">
    <article class="tarjeta">
        <h2>Propiedades</h2>
        <p class="suave">Registro, consulta y análisis de propiedades On Market.</p>
        <span class="etiqueta">Próximamente</span>
    </article>
    <article class="tarjeta">
        <h2>Comparables y ofertas</h2>
        <p class="suave">Promedio de comparables, oferta máxima y seguimiento.</p>
        <span class="etiqueta">Próximamente</span>
    </article>
    <?php if ($usuario['rol'] === 'administrador'): ?>
    <article class="tarjeta">
        <h2>Usuarios</h2>
        <p class="suave">Crear usuarios y asignar roles.</p>
        <span class="etiqueta">Próximamente</span>
    </article>
    <?php endif; ?>
</div>
