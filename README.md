🏔️ TrailPeak — Tienda Online de Trail Running
📋 Descripción

TrailPeak es una tienda online desarrollada como proyecto educativo del segundo curso del Ciclo Formativo de Grado Superior en Desarrollo de Aplicaciones Web (DAW).

La aplicación permite a los usuarios navegar por un catálogo de productos de trail running, realizar compras tanto como usuario registrado como invitado, y gestionar pedidos mediante una pasarela de pago integrada con Stripe.

El proyecto implementa un sistema completo de roles, carrito, checkout, panel de administración e informes, aplicando buenas prácticas de desarrollo backend con PHP y MySQL.

✨ Características principales
🏠 Tienda

Catálogo de productos organizado por categorías

Búsqueda, filtrado y ordenación de productos

Visualización de productos activos

Diseño responsive con Bootstrap



🛒 Carrito y compra


Carrito de compra con gestión de cantidades

Compra como:

Usuario registrado

Invitado (sin registro previo)

Integración de pago con Stripe (modo test)

Generación automática de pedidos

Estados de pedido:

Pendiente

Enviado

Entregado



👤 Usuarios y autenticación


Registro, login y logout seguros

Contraseñas cifradas con password_hash()

Panel de usuario con:

Visualización de pedidos

Edición de datos personales

Cambio de contraseña



👨‍💼 Panel de administración


Acceso protegido por rol

Gestión de:

Productos (CRUD + baja lógica)

Categorías

Pedidos y estados

Usuarios (solo administrador)

Panel de informes con:

Ventas totales

Productos más vendidos

Ingresos agrupados por mes



👥 Sistema de roles


Visitante: navegación, carrito y compra como invitado

Cliente: compras, pedidos y perfil

Empleado: gestión de productos, categorías y pedidos

Administrador: gestión completa + informes + usuarios



🛠️ Tecnologías utilizadas

🔧 Backend

· PHP 7.4+

· MySQL / MariaDB

· PDO (consultas preparadas)


🎨 Frontend

· HTML5 / CSS3

· Bootstrap 5

· JavaScript (Vanilla)


🌐 Servicios externos


Stripe Checkout (modo test)



📁 Estructura del proyecto

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
├── includes/             # Header, footer, mensajes flash
│
├── css/                  # Estilos
├── img/                  # Imágenes del proyecto
│
├── index.php             # Catálogo principal
├── cart.php
├── checkout.php
├── success.php
├── cancel.php
├── login.php
├── register.php
├── mis_pedidos.php
├── mis_datos.php
│
└── trailpeak_local.sql   # Script de base de datos


🚀 Instalación en local
📦 Requisitos

XAMPP / WAMP / MAMP

PHP 7.4 o superior

MySQL / MariaDB

Navegador web moderno

⚙️ Pasos

Copiar el proyecto en la carpeta htdocs

Crear una base de datos (por ejemplo: trailpeak_local)

Importar el archivo:

trailpeak_local.sql


Copiar:

config/env.example.php → config/env.php


Configurar credenciales de base de datos y Stripe en env.php

Acceder desde el navegador:

http://localhost/TrailPeak/

💳 Stripe (modo test)

Tarjeta de prueba:

Número: 4242 4242 4242 4242
Fecha: cualquiera futura
CVC: cualquiera

🔐 Usuarios de prueba

Administrador

Email: admin@trailpeak.com

Password: Admin123

Empleado

Email: empleado@trailpeak.test

Password: Empleado123

Cliente

Email: cliente@trailpeak.test

Password: Cliente123

⚠️ Credenciales incluidas únicamente con fines educativos y de prueba.



🔒 Seguridad

Contraseñas cifradas

PDO + consultas preparadas

Control de acceso por rol

Baja lógica de usuarios y productos

Sanitización de salida (htmlspecialchars)

Separación de configuración por entorno



⚠️ Proyecto educativo. Para producción real se recomienda añadir CSRF tokens, HTTPS, rate limiting, etc.



🚧 Estado del proyecto

✅ Proyecto completado y totalmente funcional
Cumple los requisitos funcionales de la práctica final de DWES (DAW).



👨‍💻 Autor

Jesús Bailén Sánchez
Web Developer & Publicist
