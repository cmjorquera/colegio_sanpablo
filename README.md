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
