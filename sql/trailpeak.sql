-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 26-01-2026 a las 11:57:31
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `trailpeak_local`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `id_categoria` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `id_padre` int(11) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`id_categoria`, `nombre`, `activo`) VALUES
(1, 'Ropa técnica', 1),
(2, 'Zapatillas', 1),
(3, 'Accesorios', 1),
(4, 'Ofertas', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_pedido`
--

CREATE TABLE `detalle_pedido` (
  `id_detalle` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(8,2) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_pedido`
--

INSERT INTO `detalle_pedido` (`id_detalle`, `id_pedido`, `id_producto`, `cantidad`, `precio_unitario`, `activo`) VALUES
(1, 1, 4, 1, 89.90, 1),
(2, 2, 4, 2, 89.90, 1),
(3, 3, 4, 1, 89.90, 1),
(4, 4, 1, 1, 29.90, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido`
--

CREATE TABLE `pedido` (
  `id_pedido` int(11) NOT NULL,
  `fecha` datetime NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `stripe_session_id` varchar(255) DEFAULT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedido`
--

INSERT INTO `pedido` (`id_pedido`, `fecha`, `total`, `id_usuario`, `activo`, `stripe_session_id`, `estado`) VALUES
(1, '2026-01-24 00:02:17', 89.90, 3, 1, 'cs_test_a1eyIRFBnyUrI0nvgwGavCLzwGVYmypzayYHveOdwfpiP068D0OooK74v4', 'pendiente'),
(2, '2026-01-24 20:18:16', 179.80, 3, 1, 'cs_test_a1cFtbEzYZqZawaUuaFBqSFrRD4KwCP3T84gGmTGuFR5ZtDoWhDuU4Kzly', 'pendiente'),
(3, '2026-01-24 20:46:57', 89.90, 3, 1, 'cs_test_a1BZvA4sevPGsMPSdonAMgsXDoUh53JiBqkRS1XYFT4JEt6YGfdmMZUo5v', 'enviado'),
(4, '2026-01-25 19:44:20', 29.90, 4, 1, 'cs_test_a1cuhKVva2IO7uzYd1hn4XgVXDdsMvAAFL8Uu9R6tTuf17hlwMNBHNczZY', 'pendiente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `id_producto` int(11) NOT NULL,
  `sku` varchar(50) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(8,2) NOT NULL,
  `stock` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `imagen_path` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`id_producto`, `nombre`, `descripcion`, `precio`, `stock`, `id_categoria`, `activo`) VALUES
(1, 'Camiseta Trail', 'Transpirable y ligera', 29.90, 10, 1, 1),
(2, 'Short técnico', 'Secado rápido', 34.90, 8, 1, 1),
(3, 'Zapatilla Mountain Pro', 'Agarre y estabilidad', 129.90, 6, 2, 1),
(4, 'Mochila 5L', 'Hidratación incluida', 89.90, 4, 3, 1),
(5, 'Chaleco', '', 49.90, 5, 3, 1),
(6, 'Zapatiillas Speed Trail Pro', '', 90.90, 12, 2, 1),
(7, 'Bolsa de hidratación', '', 14.90, 25, 3, 1),
(8, 'Guantes', '', 19.90, 12, 4, 0),
(9, 'Cortavientos', '', 39.90, 12, 4, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','empleado','cliente') NOT NULL DEFAULT 'cliente',
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `nombre`, `email`, `password`, `rol`, `activo`) VALUES
(1, 'Admin', 'admin@trailpeak.com', '$2y$10$bTZ8UAMXK4l267bge10tfunC.rkLVUgCXtgd4PKSuEqP4yYB64D6O', 'admin', 1),
(2, 'Empleado', 'empleado@trailpeak.com', '$2y$10$rw6C0TkZJEevIJ/CX22LeuzCKGqfRojquGmDcf6vNRgsHhEieLDVO', 'empleado', 1),
(3, 'Cliente', 'cliente@trailpeak.com', '$2y$10$z0m5v33BNSrtBm4Mes7KbOFdy6pwX2ZYPSjlkMw1SOVW9.0g/WV7O', 'cliente', 1),
(4, 'Jesús', 'jesusbailensanchez@gmail.com', '$2y$10$ptZqGFwnD7x9nbTVs7ITKuZNcru6lnHU2CvksKPcncX6VQQmp2kTW', 'cliente', 1),
(5, 'Clara', 'clarabailen@gmail.com', '$2y$10$edrrpKkwSEWY6W5aSIixqOqKePU60KnDes4sgBrLna584YaRWp8.y', 'cliente', 1),
(6, 'Test User', 'testuser1@trailpeak.local', '$2y$10$22h9cnLQUqdpLxy0Ehf/H.7yC1RXuoX.XPWn8ibWpCOOkiUesY49G', 'cliente', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`id_categoria`),
  ADD KEY `id_padre` (`id_padre`);

--
-- Indices de la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `idx_det_pedido` (`id_pedido`),
  ADD KEY `idx_det_producto` (`id_producto`);

--
-- Indices de la tabla `pedido`
--
ALTER TABLE `pedido`
  ADD PRIMARY KEY (`id_pedido`),
  ADD UNIQUE KEY `stripe_session_id` (`stripe_session_id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`id_producto`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `id_categoria` (`id_categoria`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `pedido`
--
ALTER TABLE `pedido`
  MODIFY `id_pedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD CONSTRAINT `fk_detalle_pedido_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `pedido` (`id_pedido`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detalle_pedido_producto` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`) ON UPDATE CASCADE;
ALTER TABLE `categoria`
  ADD CONSTRAINT `fk_categoria_padre` FOREIGN KEY (`id_padre`) REFERENCES `categoria` (`id_categoria`) ON UPDATE CASCADE ON DELETE SET NULL;
ALTER TABLE `producto`
  ADD CONSTRAINT `fk_producto_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categoria` (`id_categoria`) ON UPDATE CASCADE;
ALTER TABLE `pedido`
  ADD CONSTRAINT `fk_pedido_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
