# 🏔️ Trailpeak - Tienda Online

![Trailpeak logo](img/ui/logo_trailpeak.png)

## 📝 Descripción de la tienda
Trailpeak es una tienda online de equipamiento de trail running. Permite navegar por un catálogo de productos, realizar compras como cliente registrado o invitado y gestionar pedidos mediante un panel de administración.

## ✨ Características
- Catálogo con categorías dinámicas y filtros por nombre, precio y SKU.
- Carrito con ajuste de cantidades.
- Registro, login y perfil de usuario.
- Panel de administración con CRUD y gestión de pedidos.
- Informes con gráficas.
- Integración con Stripe en modo test.

## 🛠️ Tecnologías utilizadas
- PHP 8.4.13
- MySQL / MariaDB
- Bootstrap 5
- Chart.js
- Stripe PHP SDK

## 🧩 Estructura del proyecto
```text
Tienda_Trailpeak_FINAL/
├─ index.php                     (catálogo y filtros)
├─ producto.php                  (detalle de producto)
├─ cart.php                      (carrito)
├─ checkout.php                  (pago)
├─ success.php                   (confirmación pedido)
├─ cancel.php                    (cancelación)
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

## 🚀 Instalación
1) Clona el repositorio.
2) Copia `config/env.example.php` como `config/env.php`.
3) Configura BD, `BASE_URL` y `STRIPE_SECRET` en `config/env.php`.
4) Importa la base de datos (ver apartado siguiente).
5) Abre en el navegador:
   - `http://localhost/ud6/ud6/Tienda_Trailpeak_FINAL/index.php`

## 🗄️ Base de datos
Tablas principales:
- `usuario`
- `categoria`
- `producto`
- `pedido`
- `detalle_pedido`

Campos clave:
- Bajas lógicas con `activo` en `usuario`, `categoria`, `producto`.
- Pedidos con estado `pendiente`, `enviado`, `entregado`.

### 📄 Script SQL de ejemplo
El script incluido es `sql/trailpeak.sql`.

### 📥 Importación del SQL
1) Abre phpMyAdmin.
2) Selecciona tu base de datos.
3) Pestaña Importar.
4) Sube `sql/trailpeak.sql`.
5) Confirma la importación.

## ▶️ Uso
- Acceso público al catálogo y carrito.
- Registro y login para clientes.
- Panel admin para administradores:
  - Gestión de productos, categorías y pedidos.
  - Gestión de usuarios.
  - Informes.

## ✅ Funcionalidades disponibles
- Catálogo con búsqueda, filtrado y ordenación.
- Detalle de producto con opción "Ver más".
- Carrito con ajustes de cantidad.
- Pedidos y estado de pedidos.
- Panel de administración con CRUD e informes.

## 🖼️ Capturas de pantalla
Agrega tus capturas en `docs/screenshots/` y actualiza rutas si lo necesitas.



## 🎓 Contexto educativo
Proyecto final del módulo Desarrollo Web en Entorno Servidor (DAW 2).

## 🔒 Seguridad
Medidas implementadas:
- Consultas preparadas (PDO).
- Saneamiento con `filter_input` y `htmlspecialchars`.
- Passwords con `password_hash` y `password_verify`.
- Control de acceso por roles.
- Bajas lógicas en lugar de eliminaciones permanentes.
- Validación básica de uploads.

Recomendaciones si fuese un caso real:
- Variables de entorno para credenciales.
- HTTPS y HSTS.
- CSRF tokens en formularios sensibles.
- Rate limiting y protección anti fuerza bruta.
- Logs y auditoría de acciones admin.
- Backups automáticos de BD.

## 📌 Estado del proyecto
Completado y estable para entrega académica.

## ✅ Funcionalidades completadas
- Catálogo, carrito, usuarios, pedidos, administración, informes, integración básica de pago.

## 🔮 Mejoras futuras posibles
- Emails automáticos de confirmación.
- Facturas PDF.
- Paginación avanzada.
- Mejoras de accesibilidad y SEO.

## 📄 Licencia
MIT

## 👤 Autor
- Jesús Bailén

## 📬 Contacto
- GitHub: https://github.com/jesusbailen
- LinkedIn: https://linkedin.com/n/jesusbailen

---
Gracias por revisar el proyecto.
