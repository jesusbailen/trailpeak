# 🏔️ TrailPeak — Tienda Online de Trail Running

---

## 📋 Descripción

**TrailPeak** es una tienda online desarrollada como **proyecto educativo** del segundo curso del  
**Ciclo Formativo de Grado Superior en Desarrollo de Aplicaciones Web (DAW)**.

La aplicación permite a los usuarios navegar por un catálogo de productos de *trail running*, realizar compras tanto como **usuario registrado** como **invitado**, y gestionar pedidos mediante una **pasarela de pago integrada con Stripe**.

El proyecto implementa un sistema completo de **roles**, **carrito**, **checkout**, **panel de administración** e **informes**, aplicando buenas prácticas de desarrollo backend con **PHP y MySQL**.

---

## ✨ Características principales

---

### 🏠 Tienda
- Catálogo de productos organizado por categorías
- Búsqueda, filtrado y ordenación de productos
- Visualización de productos activos
- Diseño responsive con **Bootstrap**

---

### 🛒 Carrito y compra
- Carrito de compra con gestión de cantidades
- Compra como:
  - Usuario registrado
  - Invitado (sin registro previo)
- Integración de pago con **Stripe (modo test)**
- Generación automática de pedidos
- Estados de pedido:
  - Pendiente
  - Enviado
  - Entregado

---

### 👤 Usuarios y autenticación
- Registro, login y logout seguros
- Contraseñas cifradas con `password_hash()`
- Panel de usuario:
  - Visualización de pedidos
  - Edición de datos personales
  - Cambio de contraseña

---

### 👨‍💼 Panel de administración
- Acceso protegido por rol
- Gestión de:
  - Productos (CRUD + baja lógica)
  - Categorías
  - Pedidos y estados
  - Usuarios (solo administrador)
- Panel de informes:
  - Ventas totales
  - Productos más vendidos
  - Ingresos agrupados por mes

---

### 👥 Sistema de roles
- **Visitante**: navegación, carrito y compra como invitado
- **Cliente**: compras, pedidos y perfil
- **Empleado**: gestión de productos, categorías y pedidos
- **Administrador**: gestión completa + informes + usuarios

---

## 🛠️ Tecnologías utilizadas

### Backend
- PHP 7.4+
- MySQL / MariaDB
- PDO (consultas preparadas)

### Frontend
- HTML5 / CSS3
- Bootstrap 5
- JavaScript (Vanilla)

### Servicios externos
- Stripe Checkout (modo test)

---

## 📁 Estructura del proyecto

```plaintext
Tienda_Trailpeak_FINAL/
├── admin/
├── config/
├── css/
├── img/
├── includes/
├── lib/
├── vendor/
├── index.php
├── cart.php
├── checkout.php
├── success.php
├── cancel.php
├── login.php
├── register.php
├── mis_pedidos.php
├── mis_datos.php
└── trailpeak_local.sql

