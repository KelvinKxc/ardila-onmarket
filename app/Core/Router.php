<?php
declare(strict_types=1);

namespace App\Core;

/** Enrutador sencillo: asocia "MÉTODO + ruta" con un método de un controlador. */
final class Router
{
    /** @var array<string,array<string,array{0:array{0:string,1:string},1:array<string,mixed>}>> */
    private array $rutas = [];

    /** @param array{0:string,1:string} $accion  @param array<string,mixed> $opciones  roles => [...], publica => bool */
    public function get(string $ruta, array $accion, array $opciones = []): void
    {
        $this->rutas['GET'][$ruta] = [$accion, $opciones];
    }

    /** @param array{0:string,1:string} $accion  @param array<string,mixed> $opciones */
    public function post(string $ruta, array $accion, array $opciones = []): void
    {
        $this->rutas['POST'][$ruta] = [$accion, $opciones];
    }

    public function despachar(string $metodo, string $ruta): void
    {
        $metodo = strtoupper($metodo);

        if (!isset($this->rutas[$metodo][$ruta])) {
            foreach ($this->rutas as $m => $lista) {
                if (isset($lista[$ruta])) {
                    http_response_code(405);
                    header('Allow: ' . $m);
                    View::mostrar('errores/error', ['titulo' => 'Método no permitido', 'mensaje' => 'Esta dirección no acepta ese tipo de petición.']);
                    return;
                }
            }
            http_response_code(404);
            View::mostrar('errores/error', ['titulo' => 'No encontrado', 'mensaje' => 'La página que buscas no existe.']);
            return;
        }

        [$accion, $opciones] = $this->rutas[$metodo][$ruta];

        // Todo formulario (POST) debe traer su token CSRF
        if ($metodo === 'POST' && !Csrf::valido($_POST['_csrf'] ?? null)) {
            http_response_code(403);
            View::mostrar('errores/error', ['titulo' => 'Solicitud no válida', 'mensaje' => 'El formulario expiró o no es válido. Vuelve atrás, recarga la página e inténtalo de nuevo.']);
            return;
        }

        // Rutas privadas (por defecto): exigen sesión y, si se indica, un rol
        if (empty($opciones['publica'])) {
            Auth::exigirSesion();
            if (!empty($opciones['roles'])) {
                Auth::exigirRol($opciones['roles']);
            }
        }

        [$clase, $funcion] = $accion;
        (new $clase())->$funcion();
    }
}
