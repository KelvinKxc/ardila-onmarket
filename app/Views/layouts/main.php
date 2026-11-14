<?php /** @var string $contenido */ /** @var string|null $titulo */ ?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(($titulo ?? '') !== '' ? $titulo . ' · ' : '') ?><?= e(config('nombre')) ?></title>
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body>
<?php $u = usuario_actual(); ?>
<?php if ($u !== null): ?>
    <header class="barra">
        <a class="marca" href="<?= e(url('')) ?>">Ardila Property</a>
        <nav class="barra-der">
            <span class="usuario"><?= e($u['nombre']) ?> · <?= e($u['rol']) ?></span>
            <form method="post" action="<?= e(url('logout')) ?>">
                <?= csrf_campo() ?>
                <button type="submit" class="btn btn-claro">Cerrar sesión</button>
            </form>
        </nav>
    </header>
<?php endif; ?>

<main class="contenedor">
    <?php foreach (\App\Core\Session::tomarFlashes() as $f): ?>
        <div class="mensaje mensaje-<?= e($f['tipo']) ?>" role="alert"><?= e($f['mensaje']) ?></div>
    <?php endforeach; ?>
    <?= $contenido ?>
</main>
</body>
</html>
