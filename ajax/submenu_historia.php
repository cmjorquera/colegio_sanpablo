<?php
session_start();
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../includes/cms_helpers.php';
require_once __DIR__ . '/../includes/admin_permissions.php';

try {
    if (empty($_SESSION['admin_logged'])) { throw new RuntimeException('Sesión administrativa no válida.'); }
    admin_requerir_permiso('submenus_publicos', 'editar');
    $db=cms_get_connection(); $action=(string)($_POST['accion']??$_GET['accion']??'listar');
    $idSubMenu=(int)($_POST['id_sub_menu']??$_GET['id_sub_menu']??0);
    if($idSubMenu!==2){throw new RuntimeException('Historia visual solo está disponible para el submenú 2.');}
    $payload=['ok'=>true];
    if($action==='listar'){$payload['items']=cms_list_submenu_history_items($db,2,false);}
    elseif($action==='guardar'){$payload['mensaje']='Hito guardado correctamente';$payload['item']=cms_save_submenu_history_item($db,$_POST);}
    elseif($action==='eliminar'){cms_delete_submenu_history_item($db,(int)($_POST['id_historia_item']??0));$payload['mensaje']='Hito eliminado correctamente';}
    elseif($action==='toggle'){$payload['visible']=cms_toggle_submenu_history_item($db,(int)($_POST['id_historia_item']??0));}
    elseif($action==='ordenar'){cms_reorder_submenu_history_items($db,(array)($_POST['items']??[]));$payload['mensaje']='Orden actualizado correctamente';}
    else{throw new RuntimeException('Acción no válida.');}
    echo json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
} catch(Throwable $e){http_response_code(400);echo json_encode(['ok'=>false,'mensaje'=>$e->getMessage()],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);}
