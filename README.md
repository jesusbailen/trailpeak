# Trailpeak - Tienda Online

Proyecto final de Desarrollo Web en Entorno Servidor (DAW 2º).  
Aplicación e-commerce en PHP + MySQL.

## Requisitos
- PHP 8.x
- MySQL/MariaDB
- Servidor web (Apache/Nginx)

## Instalación local
1) Clona el repositorio.
2) Crea el archivo de configuración:
   - Copia `config/env.php.example` como `config/env.php`
   - Ajusta credenciales de BD y `BASE_URL`.
   - Si usas Stripe (modo test), añade `STRIPE_SECRET`.
3) Importa la base de datos:
   - `trailpeak_local.sql` en tu MySQL local.
4) Abre en el navegador:
   - `http://localhost/ud6/ud6/Tienda_Trailpeak_FINAL/index.php`

## Estructura
- `index.php` catálogo y filtros
- `admin/` panel de administración
- `config/` configuración y conexión
- `includes/` header y footer comunes
- `css/`, `img/`, `vendor/`

## Hosting (InfinityFree)
1) Sube la carpeta `Tienda_Trailpeak_FINAL` a tu hosting.
2) Ajusta `BASE_URL` en `config/env.php`.
3) Importa `trailpeak_local.sql` en phpMyAdmin.

## Notas
- `config/env.php` no se sube al repo (contiene credenciales).
- Para acceso administrador, usa las credenciales indicadas por el autor del proyecto.
