<?php $depth = (strpos($_SERVER['PHP_SELF'], '/pages/') !== false) ? '../' : ''; ?>
        </main><!-- /.content -->
    </div><!-- /.main-wrap -->
</div><!-- /.layout -->

<!-- Toast container -->
<div id="toast-container" class="toast-container"></div>

<!-- Off-canvas overlay -->
<div id="oc-overlay" class="oc-overlay" onclick="closeOC()"></div>

<!-- Off-canvas panel -->
<div id="oc-panel" class="oc-panel">
    <div class="oc-header">
        <span id="oc-titulo" class="oc-titulo"></span>
        <button class="oc-close" onclick="closeOC()"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div id="oc-body" class="oc-body"></div>
</div>

<script src="<?= $depth ?>js/datatables.js"></script>
<script src="<?= $depth ?>js/api.js"></script>
<script src="<?= $depth ?>js/funciones.js"></script>
<script src="<?= $depth ?>js/sidebar.js"></script>
<script src="<?= $depth ?>js/validaciones.js"></script>
</body>
</html>
