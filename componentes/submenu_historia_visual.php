<?php if (!empty($historyItems)): ?>
<section class="historia-visual" aria-labelledby="historiaVisualTitulo">
  <div class="historia-visual__contenedor">
    <header class="historia-visual__cabecera"><span>Nuestra trayectoria</span><h2 id="historiaVisualTitulo">Historia</h2></header>
    <div class="historia-visual__carrusel" data-history-carousel>
      <?php foreach ($historyItems as $item): ?>
        <article class="historia-hito">
          <?php if (!empty($item['imagen'])): ?><img src="<?= e($item['imagen']) ?>" alt="<?= e($item['imagen_alt'] ?: $item['titulo']) ?>" loading="lazy"><?php endif; ?>
          <h3><?= e($item['titulo']) ?></h3>
          <div class="historia-hito__contenido"><?= cms_basic_content_html($item['contenido'] ?? '') ?></div>
        </article>
      <?php endforeach; ?>
    </div>
    <?php if (count($historyItems) > 1): ?><nav class="historia-visual__navegacion" aria-label="Navegación de historia"><button type="button" data-history-prev aria-label="Historia anterior"><i class="fas fa-arrow-left"></i></button><span data-history-status aria-live="polite"></span><button type="button" data-history-next aria-label="Historia siguiente"><i class="fas fa-arrow-right"></i></button></nav><?php endif; ?>
  </div>
</section>
<?php endif; ?>
