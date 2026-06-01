<?php
require_once __DIR__ . '/includes/cms_helpers.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return cms_e($value);
    }
}

if (!function_exists('cfg')) {
    function cfg(array $configMap, string $sectionName, string $key, string $default = ''): string
    {
        return cms_cfg($configMap, $sectionName, $key, $default);
    }
}

try {
    $db = cms_get_connection();
    $site = cms_get_site_data($db);
} catch (Throwable $exception) {
    http_response_code(204);
    exit;
}

$sectionConfigsMap = $site['configs'] ?? [];
$sectionItemsMap = $site['items'] ?? [];
$institution = $site['institution'] ?? [];
$sections = $site['sections'] ?? [];

$_mSec = isset($sectionItemsMap['modal_informativo']) ? 'modal_informativo' : 'modal_bienvenida';
$_mSectionVisible = false;
foreach ($sections as $_section) {
    if (($_section['nombre_interno'] ?? '') === $_mSec && ($_section['visible'] ?? 'no') === 'si') {
        $_mSectionVisible = true;
        break;
    }
}
if (!$_mSectionVisible) {
    http_response_code(204);
    exit;
}

$_mItem = null;
foreach (($sectionItemsMap[$_mSec] ?? []) as $_mi) {
    if (($_mi['visible'] ?? 'si') === 'si') {
        $_mItem = $_mi;
        break;
    }
}

if (!$_mItem) {
    $_t = cfg($sectionConfigsMap, $_mSec, 'titulo', '');
    $_d = cfg($sectionConfigsMap, $_mSec, 'descripcion', '');
    if ($_t !== '' || $_d !== '') {
        $_mItem = [
            'titulo' => $_t,
            'descripcion' => $_d,
            'imagen' => cfg($sectionConfigsMap, $_mSec, 'imagen', ''),
            'boton_1_texto' => cfg($sectionConfigsMap, $_mSec, 'boton_texto', ''),
            'boton_1_url' => cfg($sectionConfigsMap, $_mSec, 'boton_url', '#'),
        ];
    }
}

if (!$_mItem) {
    http_response_code(204);
    exit;
}

$_mTitulo = trim((string) ($_mItem['titulo'] ?? ''));
$_mDesc = trim((string) ($_mItem['descripcion'] ?? ''));
$_mImg = trim((string) ($_mItem['imagen'] ?? ''));
$_mBtnTxt = trim((string) ($_mItem['boton_1_texto'] ?? ''));
$_mBtnUrl = trim((string) ($_mItem['boton_1_url'] ?? '#'));
$_mMostrar = cfg($sectionConfigsMap, $_mSec, 'mostrar', 'una_vez');
$_mDelay = max(0, (int) cfg($sectionConfigsMap, $_mSec, 'delay_ms', '1500'));
$_mHash = substr(hash('sha1', implode('|', [$_mSec, $_mTitulo, $_mDesc, $_mImg, $_mBtnTxt, $_mBtnUrl])), 0, 12);
$_mSeenKey = 'sp_mi_s_' . $_mHash;
$_mDismissKey = 'sp_mi_d_' . $_mHash;

if ($_mMostrar !== 'siempre' && (!empty($_COOKIE[$_mSeenKey]) || !empty($_COOKIE[$_mDismissKey]))) {
    http_response_code(204);
    exit;
}

if ($_mMostrar !== 'siempre') {
    setcookie($_mSeenKey, '1', [
        'expires' => 0,
        'path' => '/',
        'samesite' => 'Lax',
    ]);
}

$_cv = static fn(?string $v, string $d): string => preg_match('/^#(?:[0-9a-fA-F]{3}){1,2}$/', trim((string) $v)) ? trim((string) $v) : $d;
$_p = $_cv($institution['color_primario'] ?? null, '#2060B0');
$_s = $_cv($institution['color_secundario'] ?? null, '#E8A030');
$_rgb = static function (string $h): string {
    $h = ltrim($h, '#');
    if (strlen($h) === 3) {
        $h = $h[0] . $h[0] . $h[1] . $h[1] . $h[2] . $h[2];
    }
    return hexdec(substr($h, 0, 2)) . ',' . hexdec(substr($h, 2, 2)) . ',' . hexdec(substr($h, 4, 2));
};
$_pr = $_rgb($_p);
?>
<style>
#sp-mi{position:fixed;inset:0;z-index:99990;display:flex;align-items:center;justify-content:center;padding:20px;background:rgba(4,8,20,.74);backdrop-filter:blur(5px);-webkit-backdrop-filter:blur(5px);opacity:0;visibility:hidden;pointer-events:none;transition:opacity .18s ease}
#sp-mi[hidden]{display:none!important}
#sp-mi.sp-mi-open{opacity:1;visibility:visible;pointer-events:auto}
.sp-mi-bg{position:absolute;inset:0;cursor:pointer}
.sp-mi-wrap{position:relative;z-index:1;width:min(520px,100%);transform:translateY(10px) scale(.98);transition:transform .18s ease}
#sp-mi.sp-mi-open .sp-mi-wrap{transform:none}
.sp-mi-card{background:#fff;border-radius:18px;overflow:hidden;box-shadow:0 2px 8px rgba(<?=$_pr?>,.14),0 24px 64px rgba(4,8,20,.38);position:relative}
.sp-mi-bar{height:5px;background:linear-gradient(90deg,<?=$_p?>,<?=$_s?>)}
.sp-mi-x{position:absolute;top:12px;right:12px;width:32px;height:32px;border-radius:50%;background:rgba(0,0,0,.07);border:none;color:#555;font-size:14px;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;z-index:2;outline:none}
.sp-mi-x:hover{background:rgba(<?=$_pr?>,.12);color:<?=$_p?>}
.sp-mi-img{width:100%;max-height:260px;overflow:hidden;line-height:0}
.sp-mi-img img{width:100%;height:260px;object-fit:cover;display:block}
.sp-mi-body{padding:24px 26px 18px}
.sp-mi-title{font-size:21px;font-weight:800;line-height:1.25;margin:0 0 10px;padding-right:30px;color:<?=$_p?>}
.sp-mi-text{font-size:15px;line-height:1.65;color:#4a5568;margin:0 0 20px}
.sp-mi-btn{display:inline-block;padding:11px 26px;border-radius:50px;color:#fff!important;font-size:14px;font-weight:700;text-decoration:none;background:<?=$_p?>;box-shadow:0 4px 14px rgba(<?=$_pr?>,.34)}
.sp-mi-foot{padding:12px 26px 18px;text-align:center;border-top:1px solid #f0f2f5}
.sp-mi-dismiss{background:none;border:none;font-size:12px;cursor:pointer;text-decoration:underline;opacity:.65;padding:0;color:<?=$_p?>}
@media(max-width:540px){#sp-mi{padding:12px}.sp-mi-title{font-size:18px}.sp-mi-body{padding:18px 16px 14px}.sp-mi-foot{padding:10px 16px 14px}.sp-mi-img img{height:200px}}
</style>
<div id="sp-mi" hidden aria-hidden="true" role="dialog" aria-modal="true" aria-label="<?=e($_mTitulo ?: 'Información')?>" data-delay="<?=(int) $_mDelay?>" data-mode="<?=e($_mMostrar)?>" data-dismiss-key="<?=e($_mDismissKey)?>">
    <div class="sp-mi-bg"></div>
    <div class="sp-mi-wrap">
        <div class="sp-mi-card">
            <div class="sp-mi-bar"></div>
            <button class="sp-mi-x" id="sp-mi-x" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
            <?php if ($_mImg !== ''): ?>
                <div class="sp-mi-img">
                    <img src="<?=e($_mImg)?>" alt="<?=e($_mTitulo)?>" loading="lazy" onerror="this.closest('.sp-mi-img').style.display='none'">
                </div>
            <?php endif; ?>
            <div class="sp-mi-body">
                <?php if ($_mTitulo !== ''): ?><h3 class="sp-mi-title"><?=e($_mTitulo)?></h3><?php endif; ?>
                <?php if ($_mDesc !== ''): ?><p class="sp-mi-text"><?=nl2br(e($_mDesc))?></p><?php endif; ?>
                <?php if ($_mBtnTxt !== ''): ?>
                    <a href="<?=e($_mBtnUrl)?>" class="sp-mi-btn" <?=($_mBtnUrl !== '#') ? 'target="_blank" rel="noopener noreferrer"' : ''?>><?=e($_mBtnTxt)?></a>
                <?php endif; ?>
            </div>
            <?php if ($_mMostrar !== 'siempre'): ?>
                <div class="sp-mi-foot">
                    <button id="sp-mi-dismiss" class="sp-mi-dismiss">No volver a mostrar</button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
