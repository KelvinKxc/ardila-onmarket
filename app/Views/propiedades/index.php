<h1>Propiedades On Market</h1>
<p class="suave">Registra nuevas oportunidades. El usuario responsable se registra automáticamente.</p>
<p><a class="enlace" href="<?= e(url('')) ?>">← Volver al panel</a></p>
<section class="tarjeta">
<h2>Agregar propiedad</h2>
<p class="suave">Los campos con * son obligatorios. Montos expresados en USD y superficies en pies cuadrados (sqft).</p>
<form method="post" action="<?= e(url('propiedades/crear')) ?>">
<?= csrf_campo() ?>
<div class="modulo-form-grid">
<div class="campo-ancho"><label for="direccion">Dirección completa *</label><input id="direccion" name="direccion" type="text" maxlength="150" minlength="8" placeholder="Ej.: 123 Main St, Saint Paul, MN 55104" required><small>Incluye número, calle, ciudad, estado y ZIP. No registres la misma dirección dos veces.</small></div>
<div><label for="precio_lista">Precio de lista (USD) *</label><input id="precio_lista" name="precio_lista" type="number" min="1" max="4294967295" step="1" required placeholder="Ej.: 145000"><small>Solo números, sin $ ni comas.</small></div>
<div><label for="fecha_listado">Fecha de listado</label><input id="fecha_listado" name="fecha_listado" type="date"><small>Fecha en que se publicó el anuncio.</small></div>
<div><label for="habitaciones">Habitaciones</label><input id="habitaciones" name="habitaciones" type="number" min="0" max="255" step="1" placeholder="Ej.: 3"></div>
<div><label for="banos">Baños</label><input id="banos" name="banos" type="number" min="0" max="99.9" step="0.5" placeholder="Ej.: 1.5"><small>Utiliza 1, 1.5, 2, 2.5, etc.</small></div>
<div><label for="sqft">Área construida (sqft)</label><input id="sqft" name="sqft" type="number" min="0" max="4294967295" step="1" placeholder="Ej.: 1400"></div>
<div><label for="garaje">Garaje</label><select id="garaje" name="garaje"><option value="no_se">No se sabe</option><option value="si">Sí</option><option value="no">No</option></select></div>
<div><label for="taxes_anuales">Impuestos anuales (USD)</label><input id="taxes_anuales" name="taxes_anuales" type="number" min="0" max="4294967295" step="1" placeholder="Ej.: 2700"></div>
<div><label for="ultimo_valor_compra">Último precio de compra (USD)</label><input id="ultimo_valor_compra" name="ultimo_valor_compra" type="number" min="0" max="4294967295" step="1" placeholder="Ej.: 98000"></div>
<div><label for="tipo_remodelacion">Nivel de remodelación estimado</label><select id="tipo_remodelacion" name="tipo_remodelacion"><option value="">Sin evaluar</option><option value="liviana">Liviana</option><option value="mediana">Mediana</option><option value="pesada">Pesada</option></select><small>Liviana: acabados. Mediana: varias áreas. Pesada: intervención integral. Es una estimación preliminar.</small></div>
<div class="campo-ancho"><label for="descripcion">Descripción y observaciones</label><textarea id="descripcion" name="descripcion" rows="4" maxlength="10000" placeholder="Estado del inmueble, reparaciones, información del anuncio y observaciones..."></textarea></div>
</div>
<button class="btn btn-primario" type="submit">Guardar propiedad</button>
</form>
</section>
<section class="tarjeta separacion"><h2>Últimas propiedades registradas</h2><p class="suave">Se muestran hasta 250 propiedades recientes.</p>
<div class="tabla-scroll"><table><thead><tr><th>Dirección</th><th>Precio</th><th>Hab.</th><th>Baños</th><th>sqft</th><th>Registró</th><th>Fecha</th></tr></thead><tbody>
<?php foreach ($propiedades as $p): ?><tr><td><?= e($p['direccion']) ?></td><td>US$ <?= number_format((float)$p['precio_lista'], 0, '.', ',') ?></td><td><?= e((string)($p['habitaciones'] ?? '—')) ?></td><td><?= e((string)($p['banos'] ?? '—')) ?></td><td><?= e((string)($p['sqft'] ?? '—')) ?></td><td><?= e($p['registrado_por']) ?></td><td><?= e($p['fecha_registro']) ?></td></tr><?php endforeach; ?>
<?php if (!$propiedades): ?><tr><td colspan="7">Todavía no hay propiedades registradas.</td></tr><?php endif; ?>
</tbody></table></div></section>
