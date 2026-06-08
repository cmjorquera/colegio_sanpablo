![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=flat&logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=flat&logo=javascript&logoColor=black)
![Bootstrap](https://img.shields.io/badge/Bootstrap-7952B3?style=flat&logo=bootstrap&logoColor=white)

# Colegio San Pablo

Sistema web de gestión y publicación institucional para el Colegio San Pablo.  
Incluye sitio público, panel CMS administrativo, gestión de menús, contenedores visuales, noticias, eventos, calendario, configuración institucional y auditoría de cambios.

## Características principales

- Sitio público dinámico basado en secciones administrables.
- Panel administrativo para gestionar contenido institucional.
- Administración de menús y submenús.
- Gestión de contenedores del home: hero, noticias, calendario, video, galería, FAQ, estadísticas, modal informativo y footer.
- Carga y administración de imágenes institucionales.
- Módulo de noticias y detalle de noticias.
- Módulo de eventos con importación desde plantilla Excel/CSV.
- Registro de auditoría para cambios administrativos.
- Diseño responsive basado en Bootstrap y assets personalizados.

## Tecnologías

- PHP
- MySQL / MariaDB
- JavaScript
- Bootstrap
- jQuery
- PHPMailer
- FPDF
- HTML5 / CSS3

## Estructura general

```text
.
├── index.php                  # Sitio público principal
├── admin.php                  # Panel CMS administrativo
├── class/                     # Conexión y clases base
├── includes/                  # Helpers, layout admin, scripts y funciones compartidas
├── componentes/               # Componentes visibles del sitio público
├── ajax/                      # Endpoints AJAX del panel administrativo
├── assets/                    # CSS, JS, imágenes, fuentes y recursos visuales
├── uploads/                   # Archivos subidos desde el CMS
├── logica/                    # Scripts SQL, documentación y modelo de datos
├── PDF/                       # Librería FPDF
└── PHPMailer/                 # Librería para envío de correos
```

## Requisitos

- PHP 8.0 o superior recomendado.
- MySQL o MariaDB.
- Servidor local como XAMPP, Laragon, WAMP o similar.
- Navegador moderno.

## Instalación local

1. Clonar el repositorio.

```bash
git clone <url-del-repositorio>
```

2. Copiar el proyecto dentro del directorio del servidor local.

```text
htdocs/colegio_sanpablo
```

3. Crear la base de datos MySQL.

```sql
CREATE DATABASE qaseduc_colegio_spablo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

4. Importar el archivo SQL ubicado en:

```text
logica/qaseduc_colegio_spablo.sql
```

5. Configurar las credenciales de conexión en:

```text
class/conexion.php
```

6. Abrir el sitio desde el navegador.

```text
http://localhost/colegio_sanpablo/
```

## Acceso al panel administrativo

El panel CMS se encuentra en:

```text
http://localhost/colegio_sanpablo/admin.php
```

El acceso requiere una sesión administrativa válida.

## Ramas

- `main`: versión estable / producción.
- `qa`: rama de pruebas y validación.

## Seguridad

- No subir credenciales reales al repositorio.
- Revisar la configuración de conexión antes de publicar el proyecto.
- Mantener respaldos de la base de datos antes de realizar cambios importantes.
- Verificar permisos de escritura en la carpeta `uploads/`.
- Restringir el acceso al panel administrativo.

## Autor

Cristian Jorquera
