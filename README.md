🏔️ TrailPeak — Tienda Online de Trail Running
📋 Descripción

TrailPeak es una tienda online desarrollada como proyecto educativo del segundo curso del Ciclo Formativo de Grado Superior en Desarrollo de Aplicaciones Web (DAW).
La aplicación permite a los usuarios navegar por un catálogo de productos de trail running, realizar compras como cliente registrado o invitado, y gestionar pedidos mediante una pasarela de pago integrada con Stripe.

El proyecto incluye un sistema completo de roles, carrito, checkout, panel de administración e informes, aplicando buenas prácticas de desarrollo backend con PHP y MySQL.

✨ Características principales
🏠 Tienda

· Catálogo de productos por categorías

· Búsqueda, filtrado y ordenación de productos

· Visualización de productos activos

· Diseño responsive con Bootstrap

🛒 Carrito y compra

· Carrito de compra con gestión de cantidades

· Compra como:

  ·Usuario registrado

  ·Invitado (sin registro previo)

· Integración de pago con Stripe (modo test)

· Generación automática de pedidos

· Estados de pedido: pendiente, enviado, entregado

👤 Usuarios y autenticación

· Registro, login y logout seguros

· Contraseñas hasheadas (password_hash)

· Panel de usuario:

  · Ver pedidos

  · Editar datos personales

  · Cambiar contraseña

👨‍💼 Panel de administración

· Acceso protegido por rol

· Gestión de:

  · Productos (CRUD + baja lógica)

  · Categorías

  · Pedidos y estados

  · Usuarios (solo admin)

· Panel de informes:

  · Ventas totales

  · Productos más vendidos

  · Ingresos por mes

👥 Sistema de roles

· Visitante: navegar, carrito, compra como invitado

· Cliente: compras, pedidos, perfil

· Empleado: gestión de productos, categorías y pedidos

· Admin: gestión completa + informes + usuarios

🛠️ Tecnologías utilizadas

Backend

· PHP 7.4+

· MySQL / MariaDB

· PDO (consultas preparadas)

Frontend

· Bootstrap 5

· HTML5 / CSS3

· JavaScript (vanilla)

Servicios externos

Stripe Checkout (modo test)

📁 Estructura del proyecto (simplificada)
TrailPeak/
│
├── admin/                # Panel de administración
│   ├── pedidos.php
│   ├── productos.php
│   ├── categorias.php
│   ├── usuarios.php
│   └── informes.php
│
├── config/
│   ├── env.php           # Configuración real (NO se sube)
│   └── env.example.php   # Configuración de ejemplo
│
├── partials/             # Header, footer, mensajes flash
│
├── assets/
│   ├── css/
│   └── img/
│
├── index.php             # Catálogo principal
├── carrito.php
├── checkout.php
├── success.php
├── cancel.php
├── login.php
├── register.php
├── mis_pedidos.php
├── mis_datos.php
│
└── sql/
    └── trailpeak.sql     # Script de base de datos

🚀 Instalación en local
Requisitos

· XAMPP / WAMP / MAMP

· PHP 7.4 o superior

· MySQL / MariaDB

· Navegador web moderno

Pasos

1. Copiar el proyecto en htdocs

2. Crear una base de datos (ej. trailpeak_local)

3. Importar el archivo:

sql/trailpeak.sql


4. Copiar:

config/env.example.php → config/env.php

5. Configurar credenciales de BD y Stripe en env.php

6. Acceder desde el navegador:

http://localhost/TrailPeak/

💳 Stripe (modo test)

Tarjeta de prueba:

4242 4242 4242 4242
Fecha: cualquiera futura
CVC: cualquiera

🔐 Usuarios de prueba (ejemplo)
Admin:
  email: admin@trailpeak.com
  password: Admin123

Empleado:
  email: empleado@trailpeak.test
  password: Empleado123

Cliente:
  email: cliente@trailpeak.test
  password: Cliente123

  🔒 Seguridad

· Contraseñas cifradas

· PDO + consultas preparadas

· Control de acceso por rol

· Baja lógica (activo)

· Sanitización de salida (htmlspecialchars)

· Separación de configuración por entorno

⚠️ Proyecto educativo. Para producción real se recomienda añadir CSRF tokens, HTTPS, rate limiting, etc.

🚧 Estado del proyecto

✅ Proyecto completado y funcional
Cumple los requisitos funcionales de la práctica final de DWES (DAW).

👨‍💻 Autor

Jesús Bailén Sánchez
Estudiante de 2º DAW
