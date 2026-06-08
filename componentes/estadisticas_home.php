<?php
$statsItems = $sectionItemsMap['estadisticas_home'] ?? [];
$visibleStats = array_values(array_filter($statsItems, static function (array $item): bool {
    return ($item['visible'] ?? 'si') === 'si';
}));

if (empty($visibleStats)) {
    $visibleStats = [
        [
            'titulo' => '30+',
            'subtitulo' => 'Anios de trayectoria',
            'descripcion' => 'Formando generaciones con identidad y cercania.',
            'imagen' => 'assets/images/icon/fanfact-icon1.png',
        ],
        [
            'titulo' => '6500+',
            'subtitulo' => 'Estudiantes acompanados',
            'descripcion' => 'Comunidad educativa activa y comprometida.',
            'imagen' => 'assets/images/icon/fanfact-icon2.png',
        ],
        [
            'titulo' => '400+',
            'subtitulo' => 'Actividades realizadas',
            'descripcion' => 'Experiencias academicas, deportivas y culturales.',
            'imagen' => 'assets/images/icon/fanfact-icon3.png',
        ],
        [
            'titulo' => '98%',
            'subtitulo' => 'Compromiso familiar',
            'descripcion' => 'Participacion cercana en el proceso educativo.',
            'imagen' => 'assets/images/icon/fanfact-icon4.png',
        ],
    ];
}

$statsSubtitle = cfg($sectionConfigsMap, 'estadisticas_home', 'subtitulo_bloque', 'Comunidad San Pablo');
$statsTitle = cfg($sectionConfigsMap, 'estadisticas_home', 'titulo_bloque', 'Datos que hablan de nuestra experiencia');
$statsDescription = cfg($sectionConfigsMap, 'estadisticas_home', 'descripcion_bloque', 'Indicadores destacados de nuestra vida escolar y trayectoria institucional.');

if (!function_exists('sp_stat_parts')) {
    function sp_stat_parts(string $value): array
    {
        $value = trim($value);
        if (preg_match('/^([0-9]+(?:[.,][0-9]+)?)(.*)$/', $value, $matches)) {
            return [
                'number' => (float) str_replace(',', '.', $matches[1]),
                'suffix' => trim($matches[2]),
                'raw' => $value,
            ];
        }

        return [
            'number' => 0,
            'suffix' => $value,
            'raw' => $value,
        ];
    }
}
?>
<section class="sp-estadisticas py-5" id="estadisticas" data-sp-stats>
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-label"><?= e($statsSubtitle) ?></span>
            <h2 class="section-title mt-2 mb-3"><?= e($statsTitle) ?></h2>
            <div class="divider-line mx-auto"></div>
            <p class="section-desc mx-auto mt-3" style="max-width: 720px;"><?= e($statsDescription) ?></p>
        </div>

        <div class="row g-4">
            <?php foreach ($visibleStats as $index => $item): ?>
                <?php
                $parts = sp_stat_parts((string) ($item['titulo'] ?? '0'));
                $label = trim((string) ($item['subtitulo'] ?? ''));
                $description = trim((string) ($item['descripcion'] ?? ''));
                $icon = trim((string) ($item['imagen'] ?? ''));
                if ($icon === '') {
                    $icon = 'assets/images/icon/fanfact-icon' . (($index % 4) + 1) . '.png';
                }
                ?>
                <div class="col-xl-3 col-md-6">
                    <article class="sp-stat-card h-100" data-sp-stat-card style="--stat-delay: <?= (int) ($index * 120) ?>ms;">
                        <div class="sp-stat-icon">
                            <img src="<?= e($icon) ?>" alt="" loading="lazy">
                        </div>
                        <div class="sp-stat-number">
                            <span data-sp-count="<?= e((string) $parts['number']) ?>">0</span><?= e($parts['suffix']) ?>
                        </div>
                        <?php if ($label !== ''): ?>
                            <h3><?= e($label) ?></h3>
                        <?php endif; ?>
                        <?php if ($description !== ''): ?>
                            <p><?= e($description) ?></p>
                        <?php endif; ?>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<style>
    .sp-estadisticas {
        background: #fff7e8;
        overflow: hidden;
    }

    .sp-stat-card {
        position: relative;
        display: flex;
        flex-direction: column;
        gap: 14px;
        min-height: 260px;
        padding: 34px 28px;
        border: 1px solid rgba(16, 35, 65, 0.08);
        border-radius: 22px;
        background: #ffffff;
        box-shadow: 0 18px 42px rgba(16, 35, 65, 0.08);
        opacity: 0;
        transform: translateY(34px);
        transition: opacity 650ms ease, transform 650ms ease, box-shadow 250ms ease;
        transition-delay: var(--stat-delay, 0ms);
    }

    .sp-stat-card.is-visible {
        opacity: 1;
        transform: translateY(0);
    }

    .sp-stat-card:hover {
        box-shadow: 0 22px 48px rgba(16, 35, 65, 0.12);
        transform: translateY(-4px);
    }

    .sp-stat-icon {
        width: 68px;
        height: 68px;
        display: grid;
        place-items: center;
        border-radius: 20px;
        background: #eaf4ff;
    }

    .sp-stat-icon img {
        max-width: 42px;
        max-height: 42px;
        object-fit: contain;
    }

    .sp-stat-number {
        color: #1976d2;
        font-size: clamp(34px, 4vw, 52px);
        line-height: 1;
        font-weight: 800;
        letter-spacing: 0;
    }

    .sp-stat-card h3 {
        margin: 0;
        color: #1b1f2a;
        font-size: 20px;
        line-height: 1.25;
        font-weight: 800;
    }

    .sp-stat-card p {
        margin: 0;
        color: #637083;
        font-size: 15px;
        line-height: 1.7;
    }

    @media (max-width: 575.98px) {
        .sp-stat-card {
            min-height: auto;
            padding: 28px 22px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var section = document.querySelector('[data-sp-stats]');
        if (!section) {
            return;
        }

        var cards = section.querySelectorAll('[data-sp-stat-card]');
        var counters = section.querySelectorAll('[data-sp-count]');
        var started = false;

        function formatNumber(value, decimals) {
            return value.toLocaleString('es-CL', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            });
        }

        function animateCounters() {
            counters.forEach(function (counter) {
                var target = parseFloat(counter.getAttribute('data-sp-count') || '0');
                var decimals = target % 1 === 0 ? 0 : 1;
                var duration = 1800;
                var startTime = null;

                function tick(timestamp) {
                    if (!startTime) {
                        startTime = timestamp;
                    }

                    var progress = Math.min((timestamp - startTime) / duration, 1);
                    var eased = 1 - Math.pow(1 - progress, 3);
                    counter.textContent = formatNumber(target * eased, decimals);

                    if (progress < 1) {
                        requestAnimationFrame(tick);
                    } else {
                        counter.textContent = formatNumber(target, decimals);
                    }
                }

                requestAnimationFrame(tick);
            });
        }

        function start() {
            if (started) {
                return;
            }

            started = true;
            cards.forEach(function (card) {
                card.classList.add('is-visible');
            });
            animateCounters();
        }

        if (!('IntersectionObserver' in window)) {
            start();
            return;
        }

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    start();
                    observer.disconnect();
                }
            });
        }, { threshold: 0.25 });

        observer.observe(section);
    });
</script>
