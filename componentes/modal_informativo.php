<?php
if (defined('MODAL_INFO_DONE') || defined('MODAL_INFORMATIVO_RENDERED')) { return; }
define('MODAL_INFO_DONE', true);
define('MODAL_INFORMATIVO_RENDERED', true);

$_mSec  = isset($sectionItemsMap['modal_informativo']) ? 'modal_informativo' : 'modal_bienvenida';
$_mItem = null;
foreach (($sectionItemsMap[$_mSec] ?? []) as $_mi) {
    if (($_mi['visible'] ?? 'si') === 'si') { $_mItem = $_mi; break; }
}

// Fallback desde seccion_config si no hay ítems
if (!$_mItem) {
    $_t = cfg($sectionConfigsMap, $_mSec, 'titulo', '');
    $_d = cfg($sectionConfigsMap, $_mSec, 'descripcion', '');
    if ($_t !== '' || $_d !== '') {
        $_mItem = [
            'titulo'        => $_t,
            'descripcion'   => $_d,
            'imagen'        => cfg($sectionConfigsMap, $_mSec, 'imagen', ''),
            'boton_1_texto' => cfg($sectionConfigsMap, $_mSec, 'boton_texto', ''),
            'boton_1_url'   => cfg($sectionConfigsMap, $_mSec, 'boton_url', '#'),
        ];
    }
}

if (!$_mItem) { return; }

$_mTitulo  = trim((string) ($_mItem['titulo']        ?? ''));
$_mDesc    = trim((string) ($_mItem['descripcion']   ?? ''));
$_mImg     = trim((string) ($_mItem['imagen']        ?? ''));
$_mBtnTxt  = trim((string) ($_mItem['boton_1_texto'] ?? ''));
$_mBtnUrl  = trim((string) ($_mItem['boton_1_url']   ?? '#'));
$_mMostrar = cfg($sectionConfigsMap, $_mSec, 'mostrar',  'una_vez');
$_mDelay   = max(0, (int) cfg($sectionConfigsMap, $_mSec, 'delay_ms', '1500'));
$_mHash    = substr(hash('sha1', implode('|', [$_mSec, $_mTitulo, $_mDesc, $_mImg, $_mBtnTxt, $_mBtnUrl])), 0, 12);
$_mSeenKey = 'sp_mi_s_' . $_mHash;
$_mDismissKey = 'sp_mi_d_' . $_mHash;

// Camino principal anti-pestañazo: si ya se vio en esta sesión, ni se renderiza.
if ($_mMostrar !== 'siempre' && (!empty($_COOKIE[$_mSeenKey]) || !empty($_COOKIE[$_mDismissKey]))) {
    return;
}

$_cv = static fn(?string $v, string $d): string => preg_match('/^#(?:[0-9a-fA-F]{3}){1,2}$/', trim((string)$v)) ? trim((string)$v) : $d;
$_p  = $_cv($institution['color_primario']   ?? null, '#2060B0');
$_s  = $_cv($institution['color_secundario'] ?? null, '#E8A030');

$_rgb = static function (string $h): string {
    $h = ltrim($h, '#');
    if (strlen($h) === 3) { $h = $h[0].$h[0].$h[1].$h[1].$h[2].$h[2]; }
    return hexdec(substr($h,0,2)).','.hexdec(substr($h,2,2)).','.hexdec(substr($h,4,2));
};
$_pr = $_rgb($_p);
?>
<style>
#sp-mi{position:fixed;inset:0;z-index:99990;display:none!important;align-items:center;justify-content:center;padding:20px;background:rgba(4,8,20,.74);backdrop-filter:blur(5px);-webkit-backdrop-filter:blur(5px);opacity:0;visibility:hidden;pointer-events:none;transition:opacity .28s ease,visibility 0s linear .28s}
#sp-mi.sp-mi-ready{display:flex!important}
#sp-mi.sp-mi-open{opacity:1;visibility:visible;pointer-events:auto;transition:opacity .28s ease}
.sp-mi-bg{position:absolute;inset:0;cursor:pointer}
.sp-mi-wrap{position:relative;z-index:1;width:min(520px,100%);transform:translateY(24px) scale(.97);transition:transform .32s cubic-bezier(.22,.68,0,1.2)}
#sp-mi.sp-mi-open .sp-mi-wrap{transform:none}
.sp-mi-card{background:#fff;border-radius:18px;overflow:hidden;box-shadow:0 2px 8px rgba(<?=$_pr?>,.14),0 24px 64px rgba(4,8,20,.38);position:relative}
.sp-mi-bar{height:5px;background:linear-gradient(90deg,<?=$_p?>,<?=$_s?>)}
.sp-mi-x{position:absolute;top:12px;right:12px;width:32px;height:32px;border-radius:50%;background:rgba(0,0,0,.07);border:none;color:#555;font-size:14px;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;transition:background .18s,color .18s,transform .15s;z-index:2;outline:none}
.sp-mi-x:hover{background:rgba(<?=$_pr?>,.12);color:<?=$_p?>;transform:rotate(90deg)}
.sp-mi-img{width:100%;max-height:260px;overflow:hidden;line-height:0}
.sp-mi-img img{width:100%;height:260px;object-fit:cover;display:block;transition:transform .4s}
.sp-mi-card:hover .sp-mi-img img{transform:scale(1.04)}
.sp-mi-body{padding:24px 26px 18px}
.sp-mi-title{font-size:21px;font-weight:800;line-height:1.25;margin:0 0 10px;padding-right:30px;color:<?=$_p?>}
.sp-mi-text{font-size:15px;line-height:1.65;color:#4a5568;margin:0 0 20px}
.sp-mi-btn{display:inline-block;padding:11px 26px;border-radius:50px;color:#fff!important;font-size:14px;font-weight:700;text-decoration:none;background:<?=$_p?>;box-shadow:0 4px 14px rgba(<?=$_pr?>,.34);transition:filter .2s,transform .15s,box-shadow .2s}
.sp-mi-btn:hover{filter:brightness(1.1);transform:translateY(-2px);box-shadow:0 8px 20px rgba(<?=$_pr?>,.44);color:#fff!important}
.sp-mi-foot{padding:12px 26px 18px;text-align:center;border-top:1px solid #f0f2f5}
.sp-mi-dismiss{background:none;border:none;font-size:12px;cursor:pointer;text-decoration:underline;opacity:.65;transition:opacity .15s;padding:0;color:<?=$_p?>}
.sp-mi-dismiss:hover{opacity:1}
@media(max-width:540px){#sp-mi{padding:12px}.sp-mi-title{font-size:18px}.sp-mi-body{padding:18px 16px 14px}.sp-mi-foot{padding:10px 16px 14px}.sp-mi-img img{height:200px}}
</style>

<?php /* Oculto por defecto: solo JS agrega .sp-mi-ready/.sp-mi-open si corresponde mostrarlo. */ ?>
<div id="sp-mi" style="display:none!important;visibility:hidden;opacity:0;" aria-hidden="true" role="dialog" aria-modal="true" aria-label="<?=e($_mTitulo ?: 'Información')?>">
    <div class="sp-mi-bg"></div>
    <div class="sp-mi-wrap">
        <div class="sp-mi-card">
            <div class="sp-mi-bar"></div>
            <button class="sp-mi-x" id="sp-mi-x" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
            <?php if ($_mImg !== ''): ?>
            <div class="sp-mi-img">
                <img src="<?=e($_mImg)?>" alt="<?=e($_mTitulo)?>" loading="lazy"
                     onerror="this.closest('.sp-mi-img').style.display='none'">
            </div>
            <?php endif; ?>
            <div class="sp-mi-body">
                <?php if ($_mTitulo !== ''): ?><h3 class="sp-mi-title"><?=e($_mTitulo)?></h3><?php endif; ?>
                <?php if ($_mDesc !== ''): ?><p class="sp-mi-text"><?=nl2br(e($_mDesc))?></p><?php endif; ?>
                <?php if ($_mBtnTxt !== ''): ?>
                <a href="<?=e($_mBtnUrl)?>" class="sp-mi-btn"
                   <?=($_mBtnUrl !== '#') ? 'target="_blank" rel="noopener noreferrer"' : ''?>>
                    <?=e($_mBtnTxt)?>
                </a>
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

<script>
(function(){
    var el    = document.getElementById('sp-mi');
    var xBtn  = document.getElementById('sp-mi-x');
    var dBtn  = document.getElementById('sp-mi-dismiss');
    var delay = <?=(int)$_mDelay?>;
    var mode  = <?=json_encode($_mMostrar)?>;
    var seenKey = <?=json_encode($_mSeenKey)?>;
    var dismissKey = <?=json_encode($_mDismissKey)?>;
    if (!el) return;

    function setCookie(name, value, seconds){
        var cookie = encodeURIComponent(name) + '=' + encodeURIComponent(value) + '; path=/; SameSite=Lax';
        if (seconds) cookie += '; Max-Age=' + seconds;
        document.cookie = cookie;
    }
    function hasCookie(name){
        return document.cookie.split(';').some(function(part){
            return part.trim().indexOf(encodeURIComponent(name) + '=') === 0;
        });
    }
    function dismissed(){ return hasCookie(dismissKey); }
    function seen(){ return hasCookie(seenKey); }

    function open(){
        if (!document.body.contains(el) || !shouldOpen()) return;
        el.removeAttribute('style');
        el.classList.add('sp-mi-ready');
        el.offsetHeight;                        // fuerza reflow
        el.classList.add('sp-mi-open');
        el.setAttribute('aria-hidden','false');
        document.body.style.overflow = 'hidden';
        if (mode !== 'siempre') setCookie(seenKey, '1');
    }
    function close(){
        el.classList.remove('sp-mi-open');
        el.setAttribute('aria-hidden','true');
        document.body.style.overflow = '';
        setTimeout(function(){ el.classList.remove('sp-mi-ready'); }, 300);
    }
    function dismiss(){
        setCookie(dismissKey, String(Date.now()), 7 * 86400);
        if (mode !== 'siempre') setCookie(seenKey, '1');
        close();
    }

    function shouldOpen(){
        if (dismissed()) return false;
        if (mode==='siempre') return true;
        return !seen();
    }

    function removeIfNotNeeded(){
        if (shouldOpen()) return false;
        if (el.parentNode) el.parentNode.removeChild(el);
        return true;
    }

    if (removeIfNotNeeded()) return;

    if (shouldOpen()){
        window.addEventListener('load', function(){
            setTimeout(open, delay);
        });
    }

    window.addEventListener('pageshow', function(){
        if (!el || !document.body.contains(el)) return;
        if (!shouldOpen()) {
            el.classList.remove('sp-mi-open', 'sp-mi-ready');
            el.setAttribute('aria-hidden','true');
            document.body.style.overflow = '';
            removeIfNotNeeded();
        }
    });

    if (xBtn) xBtn.addEventListener('click', close);
    if (dBtn) dBtn.addEventListener('click', dismiss);
    el.querySelector('.sp-mi-bg').addEventListener('click', close);
    document.addEventListener('keydown', function(e){
        if (e.key==='Escape' && el.classList.contains('sp-mi-open')) close();
    });
})();
</script>
