# Trailpeak - Tienda Online

Tienda online completa para equipamiento de trail running. El proyecto incluye catalogo con filtros, carrito, gestion de usuarios, panel de administracion, pedidos e informes, siguiendo una arquitectura modular en PHP y MySQL.

## Caracteristicas
- Catalogo con categorias dinamicas, filtros por nombre, precio y codigo (SKU).
- Carrito de compra con ajuste de cantidades.
- Registro, login y perfil de usuario.
- Panel de administracion con CRUD y gestion de pedidos.
- Informes con graficas (ventas e ingresos mensuales).
- Integracion con Stripe en modo test.

## Tecnologias utilizadas
- PHP 8.x
- MySQL / MariaDB
- Bootstrap 5
- Chart.js
- Stripe PHP SDK

## Estructura del proyecto
- `index.php`: catalogo principal
- `producto.php`: detalle de producto
- `cart.php`, `checkout.php`, `success.php`: carrito y compra
- `admin/`: panel de administracion
- `config/`: configuracion y conexion
- `includes/`: header y footer comunes
- `css/`, `img/`, `vendor/`, `uploads/`

## Instalacion
1) Clona el repositorio.
2) Crea el archivo de configuracion:
   - Copia `config/env.php.example` como `config/env.php`.
   - Ajusta credenciales de BD, `BASE_URL` y `STRIPE_SECRET`.
3) Importa la base de datos (ver apartado siguiente).
4) Abre en el navegador:
   - `http://localhost/ud6/ud6/Tienda_Trailpeak_FINAL/index.php`

## Base de datos
Tablas principales:
- `usuario`
- `categoria`
- `producto`
- `pedido`
- `detalle_pedido`

Campos clave:
- Bajas logicas con `activo` en `usuario`, `categoria`, `producto`.
- Pedidos con estado `pendiente`, `enviado`, `entregado`.

### Script SQL de ejemplo
El script incluido es `trailpeak_local.sql`.

### Importacion del SQL
1) Abre phpMyAdmin.
2) Selecciona tu base de datos.
3) Pestaña **Importar**.
4) Sube `trailpeak_local.sql`.
5) Confirma la importacion.

## Uso
- Acceso publico al catalogo y carrito.
- Registro y login para clientes.
- Panel admin para empleados y administradores:
  - Gestion de productos, categorias y pedidos.
  - Gestion de usuarios (solo admin).
  - Informes (solo admin).

## Funcionalidades disponibles
- Catalogo con busqueda, filtrado y ordenacion.
- Detalle de producto con opcion "Ver mas".
- Carrito con ajustes de cantidad.
- Pedidos y estado de pedidos.
- Panel de administracion con CRUD e informes.

## Capturas de pantalla
Agrega tus capturas en `docs/screenshots/` y actualiza rutas si lo necesitas.

- ![Home](docs/screenshots/home.png)
- ![Producto](docs/screenshots/producto.png)
- ![Carrito](docs/screenshots/carrito.png)
- ![Admin](docs/screenshots/admin.png)

## Autor
- Jesus Bailen
- GitHub: `https://github.com/jesusbailen`

## Contexto educativo
Proyecto final del modulo Desarrollo Web en Entorno Servidor (DAW 2º).

## Seguridad
Medidas implementadas:
- Consultas preparadas (PDO).
- Saneamiento con `filter_input` y `htmlspecialchars`.
- Passwords con `password_hash` y `password_verify`.
- Control de acceso por roles.
- Bajas logicas en lugar de eliminaciones permanentes.
- Validacion basica de uploads.

Recomendaciones si fuese un caso real:
- Mover credenciales a variables de entorno.
- Forzar HTTPS y HSTS.
- CSRF tokens en formularios sensibles.
- Rate limiting y proteccion anti-fuerza bruta.
- Logs y auditoria de acciones admin.
- Backups automaticos de BD.

## Estado del proyecto
Completado y estable para entrega academica.

## Funcionalidades completadas
- Catalogo, carrito, usuarios, pedidos, administracion, informes, integracion basica de pago.

## Mejoras futuras
- Emails automaticos de confirmacion.
- Facturas PDF.
- Paginacion avanzada.
- Mejoras de accesibilidad y SEO.

## Licencia
MIT

## Contacto
- GitHub: `https://github.com/jesusbailen`

---
Gracias por revisar el proyecto.
