# Ardila Property · Sistema de propiedades On Market

Sistema web (PHP 8 + MySQL) para registrar, organizar y consultar propiedades On Market y sus comparables.
Proyecto de grado, Tecnología en Desarrollo de Sistemas Informáticos, UTS.

## Estructura de carpetas

```
ardila-onmarket/
├─ public/              ← ÚNICA carpeta que ve el navegador
│  ├─ index.php         punto de entrada de toda la aplicación
│  ├─ .htaccess         envía las rutas a index.php
│  └─ assets/css/       estilos (después: js, imágenes)
├─ app/                 ← código de la aplicación (NO accesible desde el navegador)
│  ├─ Core/             piezas base: Router, Database, Session, Csrf, Auth, View, Env, Validador
│  ├─ Controllers/      reciben la petición y deciden qué mostrar
│  ├─ Models/           consultas a la base de datos (una clase por tabla)
│  ├─ Views/            lo que se dibuja (layouts/, auth/, panel/, errores/)
│  ├─ bootstrap.php     arranque: autocarga, .env, errores, cabeceras, sesión
│  ├─ helpers.php       funciones cortas: e(), url(), asset(), csrf_campo()...
│  └─ routes.php        lista de rutas y quién puede entrar a cada una
├─ config/app.php       configuración (lee los valores del .env)
├─ database/            scripts SQL (01_schema.sql)
├─ bin/                 scripts de terminal: instalar.php, crear_admin.php
├─ storage/logs/        registros de errores y accesos fallidos
├─ .env                 credenciales reales (lo crea el instalador; NUNCA se comparte ni se sube a Git)
└─ .env.example         ejemplo sin contraseñas
```

Patrón: **Controlador → Modelo → Vista** (MVC sencillo, sin framework, para que se entienda cada pieza).

## Puesta en marcha con Laragon

1. Copia la carpeta `ardila-onmarket` dentro de `C:\laragon\www\`.
2. En Laragon pulsa **Iniciar Todo** (Apache y MySQL deben quedar activos).
3. En Laragon pulsa **Terminal** y escribe:
   ```
   cd C:\laragon\www\ardila-onmarket
   php bin\instalar.php
   ```
   - Pide un usuario de MySQL: en Laragon es `root` y normalmente **sin contraseña** (pulsa Enter).
   - Crea la base de datos, las tablas y un usuario de MySQL propio de la aplicación con **permisos mínimos** y contraseña aleatoria, que se guarda solo en `.env`.
4. Crea el primer administrador (la contraseña se escribe en la terminal, no se ve y no queda en ningún archivo):
   ```
   php bin\crear_admin.php
   ```
5. Abre **http://ardila-onmarket.test** (Laragon crea ese dominio solo; si no aparece, reinicia Apache desde Laragon).
6. Para ver las tablas: botón **Base de Datos** de Laragon (HeidiSQL) → base `ardila_onmarket`.

## Seguridad básica incluida

| Qué | Cómo |
|---|---|
| Sin contraseñas en el código | Todo sale de `.env`; el admin se crea por terminal |
| Contraseñas de usuarios | `password_hash` (bcrypt); en la base solo se guarda el hash |
| Usuario de MySQL de la app | No es `root`: solo SELECT, INSERT, UPDATE, DELETE sobre `ardila_onmarket` |
| Inyección SQL | Todas las consultas usan PDO con parámetros |
| XSS | Todo dato impreso pasa por `e()` y hay una política CSP |
| CSRF | Cada formulario POST lleva un token que el servidor verifica |
| Sesión | Cookie `HttpOnly` + `SameSite`, cambio de ID al iniciar sesión, cierre por inactividad (30 min) |
| Login | Mensaje genérico (no revela si el usuario existe) y registro de intentos fallidos |
| Carpetas internas | Solo `public/` es accesible; `.htaccess` bloquea `app`, `config`, `bin`, `storage`, `.env` |
| Errores | No se muestran al usuario; quedan en `storage/logs` |

## Reglas para seguir programando

- Nunca escribas una contraseña, clave de API o usuario real en un archivo `.php`: va en `.env` y se lee con `config(...)`.
- Para mostrar cualquier dato en una vista, usa siempre `<?= e($dato) ?>`.
- Toda consulta SQL con datos del usuario usa `prepare()` + `execute([...])`, nunca concatenar texto.
- Todo formulario `POST` incluye `<?= csrf_campo() ?>`.
- Rutas nuevas: se agregan en `app/routes.php`; `['roles' => ['administrador']]` limita por rol.
- `.env` no se sube a Git (ya está en `.gitignore`).

## Pendiente (siguientes pasos)

- Módulo de propiedades (registrar, listar, editar) con detección de duplicados.
- Comparables y cálculo de ARV / oferta máxima.
- Integración con la API de IA (el servidor llama a la API; la clave va en `.env`).
- Gestión de usuarios desde el panel del administrador.
- Bloqueo temporal por intentos fallidos de login (requiere agregar campos a la tabla `usuario`).
