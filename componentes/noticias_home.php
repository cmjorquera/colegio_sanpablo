<?php
$newsItems = $sectionItemsMap['noticias_home'] ?? [];
$cantidad = (int) cfg($sectionConfigsMap, 'noticias_home', 'cantidad_items', '4');
$newsItems = array_slice($newsItems, 0, $cantidad > 0 ? $cantidad : 4);
$tituloBloque = cfg($sectionConfigsMap, 'noticias_home', 'titulo_bloque', 'Últimas Noticias');
$textoBoton = cfg($sectionConfigsMap, 'noticias_home', 'texto_boton', 'Ver todas las noticias');
$urlBoton = cfg($sectionConfigsMap, 'noticias_home', 'url_boton', '#');
?>
<section class="sp-noticias" id="noticias">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-5 flex-wrap gap-3">
            <div>
                <span class="section-label">Novedades</span>
                <h2 class="section-title"><?= e($tituloBloque) ?></h2>
                <div class="divider-line"></div>
            </div>
            <a href="<?= e($urlBoton) ?>" class="btn-ver-mas"><?= e($textoBoton) ?></a>
        </div>
        <div class="row g-4">
            <?php foreach ($newsItems as $item): ?>
                <?php
                $categoria = $item['etiqueta'] ?? '';
                if (!empty($item['id_categoria']) && isset($categoriesById[(int) $item['id_categoria']])) {
                    $categoria = $categoriesById[(int) $item['id_categoria']]['nombre'];
                }
                ?>
                <div class="col-md-6 col-lg-3">
                    <div class="noticia-card">
                        <div class="img-wrap">
                            <img src="<?= e($item['imagen'] ?: 'assets/images/frontis_01.jpg') ?>" alt="<?= e($item['titulo']) ?>">
                        </div>
                        <div class="card-body">
                            <?php if ($categoria !== ''): ?><span class="tag"><?= e($categoria) ?></span><?php endif; ?>
                            <h5><?= e($item['titulo']) ?></h5>
                            <p><?= e($item['descripcion']) ?></p>
                            <?php if (!empty($item['fecha_publicacion'])): ?>
                                <div class="meta"><i class="fas fa-calendar-alt"></i> <?= e($item['fecha_publicacion']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
