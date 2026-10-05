<?php
$spotifyItems = array_values(array_filter($sectionItemsMap['spotify_podcast_home'] ?? [], static function (array $item): bool {
    return ($item['visible'] ?? 'si') === 'si';
}));

$spotifyChannel = null;
$spotifyEpisodes = [];
foreach ($spotifyItems as $spotifyItem) {
    if (($spotifyItem['etiqueta'] ?? '') === 'canal_spotify' && $spotifyChannel === null) {
        $spotifyChannel = $spotifyItem;
        continue;
    }

    if (($spotifyItem['etiqueta'] ?? '') === 'episodio_spotify') {
        $spotifyEpisodes[] = $spotifyItem;
    }
}

$spotifySubtitle = cfg($sectionConfigsMap, 'spotify_podcast_home', 'subtitulo_bloque', 'Podcast institucional');
$spotifyTitle = cfg($sectionConfigsMap, 'spotify_podcast_home', 'titulo_bloque', 'Escucha el Colegio San Pablo en Spotify');
$spotifyDescription = cfg($sectionConfigsMap, 'spotify_podcast_home', 'descripcion_bloque', 'Conversaciones, historias y experiencias de nuestra comunidad educativa para escuchar cuando quieras.');
$spotifyChannelName = trim((string) ($spotifyChannel['titulo'] ?? cfg($sectionConfigsMap, 'spotify_podcast_home', 'nombre_canal', 'Podcast Colegio San Pablo')));
$spotifyChannelAuthor = trim((string) ($spotifyChannel['subtitulo'] ?? cfg($sectionConfigsMap, 'spotify_podcast_home', 'autor_canal', 'Colegio San Pablo')));
$spotifyChannelDescription = trim((string) ($spotifyChannel['descripcion'] ?? $spotifyDescription));
$spotifyButtonText = trim((string) ($spotifyChannel['boton_1_texto'] ?? cfg($sectionConfigsMap, 'spotify_podcast_home', 'texto_boton', 'Ir al canal de Spotify')));
$spotifyButtonUrl = trim((string) ($spotifyChannel['boton_1_url'] ?? ($spotifyChannel['url'] ?? cfg($sectionConfigsMap, 'spotify_podcast_home', 'url_boton', 'https://open.spotify.com/'))));
$spotifyCover = trim((string) ($spotifyChannel['imagen'] ?? ''));
$spotifyShowEpisodes = cfg($sectionConfigsMap, 'spotify_podcast_home', 'mostrar_episodios', 'si') !== 'no';
$spotifyLimit = max(1, (int) cfg($sectionConfigsMap, 'spotify_podcast_home', 'cantidad_items', '3'));
$spotifyShowQr = cfg($sectionConfigsMap, 'spotify_podcast_home', 'mostrar_qr', 'si') !== 'no';
$spotifyQrTitle = cfg($sectionConfigsMap, 'spotify_podcast_home', 'texto_qr', 'Escuchanos desde tu celular');
$spotifyQrDescription = cfg($sectionConfigsMap, 'spotify_podcast_home', 'descripcion_qr', 'Escanea el codigo o abre Spotify para seguir los nuevos episodios del colegio.');
$spotifyEpisodes = array_slice($spotifyEpisodes, 0, $spotifyLimit);
$spotifySafeUrl = $spotifyButtonUrl !== '' ? $spotifyButtonUrl : 'https://open.spotify.com/';

if (!function_exists('sp_spotify_episode_duration')) {
    function sp_spotify_episode_duration(array $item): string
    {
        $subtitle = trim((string) ($item['subtitulo'] ?? ''));
        if ($subtitle === '') {
            return '';
        }

        $parts = preg_split('/[·|]/u', $subtitle);
        if (!$parts) {
            return $subtitle;
        }

        return trim((string) end($parts));
    }
}

if (!function_exists('sp_spotify_format_date')) {
    function sp_spotify_format_date(?string $date): string
    {
        $date = trim((string) $date);
        if ($date === '') {
            return '';
        }

        $timestamp = strtotime($date);
        if (!$timestamp) {
            return $date;
        }

        return date('d/m/Y', $timestamp);
    }
}
?>
<section class="sp-spotify-podcast" id="spotify-podcast-home" style="--spotify-brand:#1db954; --spotify-dark:#122018;">
    <div class="container">
        <div class="sp-spotify-shell">
            <div class="sp-spotify-copy">
                <span class="section-label"><?= e($spotifySubtitle) ?></span>
                <h2 class="section-title"><?= e($spotifyTitle) ?></h2>
                <div class="divider-line"></div>
                <?php if ($spotifyDescription !== ''): ?>
                    <p class="sp-spotify-lead"><?= e($spotifyDescription) ?></p>
                <?php endif; ?>

                <div class="sp-spotify-benefits" aria-label="Beneficios del podcast">
                    <span><i class="fab fa-spotify"></i> Spotify</span>
                    <span><i class="fas fa-headphones-alt"></i> Comunidad</span>
                    <span><i class="fas fa-mobile-alt"></i> Celular</span>
                </div>

                <a class="sp-spotify-main-btn" href="<?= e($spotifySafeUrl) ?>" target="_blank" rel="noopener noreferrer">
                    <i class="fab fa-spotify"></i>
                    <?= e($spotifyButtonText !== '' ? $spotifyButtonText : 'Ir al canal de Spotify') ?>
                </a>
            </div>

            <div class="sp-spotify-cover-card">
                <div class="sp-spotify-cover">
                    <?php if ($spotifyCover !== ''): ?>
                        <img src="<?= e($spotifyCover) ?>" alt="<?= e($spotifyChannelName) ?>" loading="lazy">
                    <?php else: ?>
                        <div class="sp-spotify-cover-fallback">
                            <i class="fab fa-spotify"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="sp-spotify-channel">
                    <span>Canal oficial</span>
                    <h3><?= e($spotifyChannelName) ?></h3>
                    <?php if ($spotifyChannelAuthor !== ''): ?>
                        <p><?= e($spotifyChannelAuthor) ?></p>
                    <?php endif; ?>
                    <?php if ($spotifyChannelDescription !== ''): ?>
                        <small><?= e($spotifyChannelDescription) ?></small>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($spotifyShowEpisodes): ?>
                <div class="sp-spotify-episodes">
                    <div class="sp-spotify-episodes-head">
                        <span>Episodios destacados</span>
                        <i class="fab fa-spotify"></i>
                    </div>

                    <?php if (!empty($spotifyEpisodes)): ?>
                        <?php foreach ($spotifyEpisodes as $episode): ?>
                            <?php
                            $episodeUrl = trim((string) ($episode['boton_1_url'] ?? ''));
                            if ($episodeUrl === '') {
                                $episodeUrl = trim((string) ($episode['url'] ?? ''));
                            }
                            if ($episodeUrl === '') {
                                $episodeUrl = $spotifySafeUrl;
                            }
                            $episodeDate = sp_spotify_format_date($episode['fecha_publicacion'] ?? '');
                            $episodeDuration = sp_spotify_episode_duration($episode);
                            ?>
                            <article class="sp-spotify-episode">
                                <a class="sp-spotify-play" href="<?= e($episodeUrl) ?>" target="_blank" rel="noopener noreferrer" aria-label="Escuchar <?= e($episode['titulo'] ?? 'episodio') ?>">
                                    <i class="fas fa-play"></i>
                                </a>
                                <div class="sp-spotify-episode-img">
                                    <?php if (!empty($episode['imagen'])): ?>
                                        <img src="<?= e($episode['imagen']) ?>" alt="<?= e($episode['titulo'] ?? 'Episodio') ?>" loading="lazy">
                                    <?php else: ?>
                                        <i class="fab fa-spotify"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="sp-spotify-episode-body">
                                    <h3><?= e($episode['titulo'] ?? 'Episodio destacado') ?></h3>
                                    <?php if (!empty($episode['descripcion'])): ?>
                                        <p><?= e($episode['descripcion']) ?></p>
                                    <?php endif; ?>
                                    <div class="sp-spotify-meta">
                                        <?php if ($episodeDate !== ''): ?>
                                            <span><i class="far fa-calendar-alt"></i><?= e($episodeDate) ?></span>
                                        <?php endif; ?>
                                        <?php if ($episodeDuration !== ''): ?>
                                            <span><i class="far fa-clock"></i><?= e($episodeDuration) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <a class="sp-spotify-listen" href="<?= e($episodeUrl) ?>" target="_blank" rel="noopener noreferrer">
                                    <?= e(trim((string) ($episode['boton_1_texto'] ?? '')) !== '' ? $episode['boton_1_texto'] : 'Escuchar episodio') ?>
                                </a>
                            </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="sp-spotify-empty">
                            <i class="fab fa-spotify"></i>
                            <span>Agrega episodios destacados desde el panel.</span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($spotifyShowQr): ?>
            <div class="sp-spotify-mobile-cta">
                <div class="sp-spotify-qr" aria-hidden="true">
                    <i class="fas fa-qrcode"></i>
                </div>
                <div>
                    <h3><?= e($spotifyQrTitle) ?></h3>
                    <p><?= e($spotifyQrDescription) ?></p>
                </div>
                <a href="<?= e($spotifySafeUrl) ?>" target="_blank" rel="noopener noreferrer">
                    Abrir Spotify <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
    .sp-spotify-podcast {
        padding: 86px 0;
        background:
            radial-gradient(circle at 12% 8%, rgba(29, 185, 84, 0.12), transparent 28%),
            linear-gradient(180deg, #ffffff 0%, #f5fbf7 100%);
        overflow: hidden;
    }

    .sp-spotify-shell {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(240px, 0.78fr) minmax(300px, 1.1fr);
        gap: 26px;
        align-items: stretch;
        padding: 30px;
        border: 1px solid rgba(18, 32, 24, 0.08);
        border-radius: 28px;
        background: rgba(255, 255, 255, 0.94);
        box-shadow: 0 24px 70px rgba(18, 32, 24, 0.1);
    }

    .sp-spotify-copy,
    .sp-spotify-cover-card,
    .sp-spotify-episodes {
        min-width: 0;
    }

    .sp-spotify-lead {
        max-width: 520px;
        margin: 18px 0 0;
        color: #5a6860;
        font-size: 16px;
        line-height: 1.75;
    }

    .sp-spotify-benefits {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin: 24px 0;
    }

    .sp-spotify-benefits span {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 12px;
        border-radius: 999px;
        background: #eff8f1;
        color: #23352a;
        font-size: 13px;
        font-weight: 700;
    }

    .sp-spotify-benefits i {
        color: var(--spotify-brand);
    }

    .sp-spotify-main-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        min-height: 48px;
        padding: 13px 20px;
        border-radius: 999px;
        background: var(--spotify-brand);
        color: #ffffff;
        font-weight: 800;
        box-shadow: 0 16px 34px rgba(29, 185, 84, 0.26);
    }

    .sp-spotify-main-btn:hover {
        color: #ffffff;
        background: #179947;
        transform: translateY(-2px);
    }

    .sp-spotify-cover-card {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 440px;
        padding: 18px;
        border-radius: 24px;
        background: linear-gradient(160deg, #122018, #183424 60%, #1db954);
        color: #ffffff;
    }

    .sp-spotify-cover {
        width: 100%;
        aspect-ratio: 1 / 1;
        border-radius: 22px;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.11);
        box-shadow: 0 18px 38px rgba(0, 0, 0, 0.22);
    }

    .sp-spotify-cover img,
    .sp-spotify-episode-img img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
    }

    .sp-spotify-cover-fallback {
        width: 100%;
        height: 100%;
        display: grid;
        place-items: center;
        color: var(--spotify-brand);
        font-size: 88px;
        background: #101915;
    }

    .sp-spotify-channel {
        padding-top: 18px;
    }

    .sp-spotify-channel span,
    .sp-spotify-episodes-head span {
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0;
        text-transform: uppercase;
    }

    .sp-spotify-channel h3 {
        margin: 8px 0 4px;
        color: #ffffff;
        font-size: 25px;
        line-height: 1.12;
        font-weight: 900;
    }

    .sp-spotify-channel p,
    .sp-spotify-channel small {
        display: block;
        margin: 0;
        color: rgba(255, 255, 255, 0.78);
        line-height: 1.55;
    }

    .sp-spotify-episodes {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .sp-spotify-episodes-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        color: var(--spotify-dark);
    }

    .sp-spotify-episodes-head i {
        color: var(--spotify-brand);
        font-size: 24px;
    }

    .sp-spotify-episode {
        position: relative;
        display: grid;
        grid-template-columns: 76px minmax(0, 1fr);
        gap: 14px;
        align-items: center;
        padding: 14px;
        border: 1px solid rgba(18, 32, 24, 0.08);
        border-radius: 20px;
        background: #ffffff;
        box-shadow: 0 12px 32px rgba(18, 32, 24, 0.06);
    }

    .sp-spotify-episode-img {
        width: 76px;
        height: 76px;
        border-radius: 17px;
        display: grid;
        place-items: center;
        overflow: hidden;
        background: #122018;
        color: var(--spotify-brand);
        font-size: 30px;
    }

    .sp-spotify-play {
        position: absolute;
        left: 60px;
        top: 56px;
        width: 34px;
        height: 34px;
        display: grid;
        place-items: center;
        border-radius: 50%;
        background: var(--spotify-brand);
        color: #ffffff;
        box-shadow: 0 8px 18px rgba(29, 185, 84, 0.35);
        z-index: 1;
    }

    .sp-spotify-play:hover {
        color: #ffffff;
        background: #179947;
    }

    .sp-spotify-episode-body h3 {
        margin: 0 0 5px;
        color: #17231c;
        font-size: 16px;
        line-height: 1.25;
        font-weight: 800;
    }

    .sp-spotify-episode-body p {
        margin: 0 0 7px;
        color: #647168;
        font-size: 13px;
        line-height: 1.45;
    }

    .sp-spotify-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        color: #5a6860;
        font-size: 12px;
        font-weight: 700;
    }

    .sp-spotify-meta span {
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .sp-spotify-meta i {
        color: var(--spotify-brand);
    }

    .sp-spotify-listen {
        grid-column: 2;
        justify-self: start;
        color: var(--spotify-brand);
        font-size: 13px;
        font-weight: 800;
    }

    .sp-spotify-listen:hover {
        color: #0f7f39;
    }

    .sp-spotify-empty {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 18px;
        border: 1px dashed rgba(29, 185, 84, 0.38);
        border-radius: 18px;
        color: #526057;
        background: #f7fbf8;
    }

    .sp-spotify-empty i {
        color: var(--spotify-brand);
        font-size: 28px;
    }

    .sp-spotify-mobile-cta {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        gap: 18px;
        align-items: center;
        margin-top: 18px;
        padding: 18px 22px;
        border-radius: 22px;
        background: #122018;
        color: #ffffff;
    }

    .sp-spotify-qr {
        width: 58px;
        height: 58px;
        display: grid;
        place-items: center;
        border-radius: 16px;
        background: #ffffff;
        color: var(--spotify-dark);
        font-size: 28px;
    }

    .sp-spotify-mobile-cta h3 {
        margin: 0 0 4px;
        color: #ffffff;
        font-size: 18px;
        font-weight: 850;
    }

    .sp-spotify-mobile-cta p {
        margin: 0;
        color: rgba(255, 255, 255, 0.75);
        font-size: 14px;
        line-height: 1.55;
    }

    .sp-spotify-mobile-cta a {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #ffffff;
        font-weight: 800;
        white-space: nowrap;
    }

    .sp-spotify-mobile-cta a:hover {
        color: var(--spotify-brand);
    }

    @media (max-width: 1199.98px) {
        .sp-spotify-shell {
            grid-template-columns: minmax(0, 1fr) minmax(280px, 0.9fr);
        }

        .sp-spotify-episodes {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 767.98px) {
        .sp-spotify-podcast {
            padding: 58px 0;
        }

        .sp-spotify-shell {
            grid-template-columns: 1fr;
            padding: 18px;
            border-radius: 22px;
        }

        .sp-spotify-cover-card {
            min-height: auto;
        }

        .sp-spotify-episode {
            grid-template-columns: 66px minmax(0, 1fr);
        }

        .sp-spotify-episode-img {
            width: 66px;
            height: 66px;
        }

        .sp-spotify-play {
            left: 52px;
            top: 50px;
        }

        .sp-spotify-mobile-cta {
            grid-template-columns: 1fr;
            text-align: left;
        }
    }
</style>
