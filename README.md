# Trailpeak - Tienda Online

## Descripcion de la tienda
Trailpeak es una tienda online de equipamiento de trail running. Permite navegar por un catalogo de productos, realizar compras como cliente registrado o invitado y gestionar pedidos mediante un panel de administracion.

## Caracteristicas
- Catalogo con categorias dinamicas y filtros por nombre, precio y SKU.
- Carrito con ajuste de cantidades.
- Registro, login y perfil de usuario.
- Panel de administracion con CRUD y gestion de pedidos.
- Informes con graficas.
- Integracion con Stripe en modo test.

## Tecnologias utilizadas
- PHP 8.x
- MySQL / MariaDB
- Bootstrap 5
- Chart.js
- Stripe PHP SDK

## Estructura del proyecto
```text
Tienda_Trailpeak_FINAL/
├─ index.php                     (catalogo y filtros)
├─ producto.php                  (detalle de producto)
├─ cart.php                      (carrito)
├─ checkout.php                  (pago)
├─ success.php                   (confirmacion pedido)
├─ cancel.php                    (cancelacion)
├─ login.php                     (acceso)
├─ register.php                  (registro)
├─ mis_datos.php                 (perfil)
├─ mis_pedidos.php               (historial)
├─ admin/                        (panel admin)
│  ├─ categorias.php
│  ├─ productos.php
│  ├─ producto_form.php
│  ├─ pedidos.php
│  ├─ usuarios.php
│  └─ informes.php
├─ scripts/                      (seeds y utilidades)
│  ├─ seed_chaleco.php
│  ├─ seed_ofertas.php
│  └─ seed_productos.php
├─ sql/
│  └─ trailpeak.sql
├─ config/
│  ├─ db.php
│  ├─ env.example.php
│  └─ stripe.php
├─ includes/
│  ├─ header.php
│  └─ footer.php
├─ css/
├─ img/
├─ uploads/
└─ vendor/
```

## Instalacion
1) Clona el repositorio.
2) Copia `config/env.example.php` como `config/env.php`.
3) Configura BD, `BASE_URL` y `STRIPE_SECRET` en `config/env.php`.
4) Importa la base de datos (ver apartado siguiente).
5) Abre en el navegador:
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
El script incluido es `sql/trailpeak.sql`.

### Importacion del SQL
1) Abre phpMyAdmin.
2) Selecciona tu base de datos.
3) Pestana Importar.
4) Sube `sql/trailpeak.sql`.
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
- GitHub: https://github.com/jesusbailen

## Contexto educativo
Proyecto final del modulo Desarrollo Web en Entorno Servidor (DAW 2).

## Seguridad
Medidas implementadas:
- Consultas preparadas (PDO).
- Saneamiento con `filter_input` y `htmlspecialchars`.
- Passwords con `password_hash` y `password_verify`.
- Control de acceso por roles.
- Bajas logicas en lugar de eliminaciones permanentes.
- Validacion basica de uploads.

Recomendaciones si fuese un caso real:
- Variables de entorno para credenciales.
- HTTPS y HSTS.
- CSRF tokens en formularios sensibles.
- Rate limiting y proteccion anti fuerza bruta.
- Logs y auditoria de acciones admin.
- Backups automaticos de BD.

## Estado del proyecto
Completado y estable para entrega academica.

## Funcionalidades completadas
- Catalogo, carrito, usuarios, pedidos, administracion, informes, integracion basica de pago.

## Mejoras futuras posibles
- Emails automaticos de confirmacion.
- Facturas PDF.
- Paginacion avanzada.
- Mejoras de accesibilidad y SEO.

## Licencia
MIT

## Contacto
- GitHub: https://github.com/jesusbailen

---
Gracias por revisar el proyecto.
