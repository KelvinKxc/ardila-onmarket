<?php
declare(strict_types=1);

// Funciones de ayuda para los scripts de la terminal (bin/).

if (PHP_SAPI !== 'cli') {
    exit('Este script solo se ejecuta desde la terminal.');
}

function salir(string $mensaje, int $codigo = 1): never
{
    fwrite($codigo === 0 ? STDOUT : STDERR, $mensaje . PHP_EOL);
    exit($codigo);
}

function preguntar(string $texto, string $defecto = ''): string
{
    echo $texto . ($defecto !== '' ? " [$defecto]" : '') . ': ';
    $r = trim((string) fgets(STDIN));
    return $r === '' ? $defecto : $r;
}

function confirmar(string $texto): bool
{
    return in_array(strtolower(preguntar($texto . ' (s/n)', 'n')), ['s', 'si', 'sí', 'y', 'yes'], true);
}

/** Lee una contraseña SIN mostrarla en pantalla. */
function leerSecreto(string $texto): string
{
    echo $texto;
    $esTerminal = function_exists('stream_isatty') && stream_isatty(STDIN);

    if ($esTerminal && PHP_OS_FAMILY === 'Windows') {
        $cmd = 'powershell -NoProfile -Command "$p = Read-Host -AsSecureString; '
             . '$b = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($p); '
             . '[Runtime.InteropServices.Marshal]::PtrToStringAuto($b)"';
        $salida = [];
        $codigo = 1;
        @exec($cmd, $salida, $codigo);
        if ($codigo === 0) {
            echo PHP_EOL;
            return trim(implode('', $salida));
        }
        echo PHP_EOL . '(No se pudo ocultar la entrada: se mostrará lo que escribes) ';
    } elseif ($esTerminal) {
        @shell_exec('stty -echo');
        $v = fgets(STDIN);
        @shell_exec('stty echo');
        echo PHP_EOL;
        return rtrim((string) $v, "\r\n");
    }
    return rtrim((string) fgets(STDIN), "\r\n");
}

/** Contraseña aleatoria (solo letras y números) para la cuenta de MySQL de la aplicación. */
function generarClave(int $largo = 24): string
{
    $alfabeto = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
    $max = strlen($alfabeto) - 1;
    $clave = '';
    for ($i = 0; $i < $largo; $i++) {
        $clave .= $alfabeto[random_int(0, $max)];
    }
    return $clave;
}

/** Separa un archivo .sql en sentencias (ignora los comentarios de línea completa). */
function dividirSentencias(string $sql): array
{
    $sql = preg_replace('/^\s*--.*$/m', '', $sql) ?? '';
    $partes = preg_split('/;\s*(?:\R|$)/', $sql) ?: [];
    return array_values(array_filter(array_map('trim', $partes), static fn ($s) => $s !== ''));
}
