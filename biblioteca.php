<?php
// Historical PHP entry point; all rendering belongs to the generic controller.
unset($_GET['id'], $_GET['route_id']);
$_GET['route_slug'] = 'biblioteca';
require __DIR__ . '/pagina_menu.php';