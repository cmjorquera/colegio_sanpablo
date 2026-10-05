TABLAS DEL MÓDULO MENÚS Y SUBMENÚS

menus almacena la navegación principal. sub_menus almacena sus opciones y la URL pública.
sub_menu_paginas almacena un único contenido editorial administrable por submenú.
sub_menu_pagina_media almacena cero o más imágenes o videos por submenú.

Relación lógica: menus.id_menu -> sub_menus.id_menu; sub_menus.id_sub_menu ->
sub_menu_paginas.id_sub_menu y sub_menu_pagina_media.id_sub_menu. El volcado actual no
declara claves foráneas físicas; sí declara UNIQUE para sub_menu_paginas.id_sub_menu.

admin.php?panel=menus, junto con includes/cms_helpers.php, administra los datos.
pagina_submenu.php?id=ID los presenta públicamente.

sub_menu_historia_item almacena hitos visuales ordenados exclusivamente para Institucional /
Historia (id_sub_menu=2). Su estructura revisable está en sub_menu_historia_item.sql.
