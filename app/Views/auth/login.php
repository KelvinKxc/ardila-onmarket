<section class="tarjeta tarjeta-login">
    <h1>Iniciar sesión</h1>
    <p class="suave">Sistema de propiedades On Market</p>

    <form method="post" action="<?= e(url('login')) ?>" autocomplete="off">
        <?= csrf_campo() ?>
        <label for="nombre">Usuario</label>
        <input id="nombre" name="nombre" type="text" maxlength="80" required autofocus autocomplete="username">

        <label for="clave">Contraseña</label>
        <input id="clave" name="clave" type="password" maxlength="1000" required autocomplete="current-password">

        <button type="submit" class="btn btn-primario">Entrar</button>
    </form>
</section>
