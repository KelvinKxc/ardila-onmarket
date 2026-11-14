<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;

final class PanelController
{
    public function inicio(): void
    {
        View::mostrar('panel/inicio', ['titulo' => 'Panel principal', 'usuario' => usuario_actual()]);
    }
}
