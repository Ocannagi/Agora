-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 10-11-2025 a las 00:04:52
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
-- Base de datos: `agora`
--
CREATE DATABASE IF NOT EXISTS `agora` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `agora`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `antiguedad`
--

DROP TABLE IF EXISTS `antiguedad`;
CREATE TABLE `antiguedad` (
  `antId` int(10) UNSIGNED NOT NULL,
  `antScatId` int(10) UNSIGNED NOT NULL,
  `antPerId` int(10) UNSIGNED NOT NULL,
  `antNombre` varchar(50) NOT NULL DEFAULT 'Sin nombre',
  `antDescripcion` varchar(500) NOT NULL,
  `antUsrId` int(10) UNSIGNED NOT NULL,
  `antFechaInsert` datetime NOT NULL DEFAULT current_timestamp(),
  `antTipoEstado` char(2) NOT NULL DEFAULT 'RD',
  `antFechaEstado` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONES PARA LA TABLA `antiguedad`:
--   `antPerId`
--       `periodo` -> `perId`
--   `antScatId`
--       `subcategoria` -> `scatId`
--   `antTipoEstado`
--       `tipoestado` -> `tteTipoEstado`
--   `antUsrId`
--       `usuario` -> `usrId`
--

--
-- Volcado de datos para la tabla `antiguedad`
--

INSERT INTO `antiguedad` (`antId`, `antScatId`, `antPerId`, `antNombre`, `antDescripcion`, `antUsrId`, `antFechaInsert`, `antTipoEstado`, `antFechaEstado`) VALUES
(1, 7, 2, 'Vitrina doble puerta', 'Vitrina Barroca circa 1656. Hermosos detalles en oro. Madera maciza de ébano. Dos puertas.', 5, '2025-05-24 19:41:04', 'VE', '2025-11-03 15:01:51'),
(2, 3, 1, 'Ángel de Mármol', 'Hermosa escultura renacentista de un ángel. Mármol. Circa 1486.', 3, '2025-05-24 19:51:02', 'RD', '2025-05-24 19:51:02'),
(3, 8, 4, 'Mesa redonda medieval', 'Mesa redonda del Rey Arturo. Ébano. Circa 520 D.C. Excelente estado.', 5, '2025-06-01 21:37:12', 'VE', '2025-11-03 14:58:17'),
(4, 6, 2, 'Armario de estilo Barroco', 'Armario Barroco. Es una preciosura', 5, '2025-06-16 15:24:14', 'VE', '2025-11-03 15:00:48'),
(5, 8, 2, 'Mesita ratona otomana', 'Prueba con mesa de vidrio editada 3', 5, '2025-10-24 00:06:14', 'VE', '2025-11-03 15:01:18'),
(6, 5, 3, 'Cuadro de Machu Pichu', 'Machu Pichu Pop Art y colorido. Qué más querés', 2, '2025-10-27 15:39:10', 'VE', '2025-11-03 15:32:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `antiguedadalaventa`
--

DROP TABLE IF EXISTS `antiguedadalaventa`;
CREATE TABLE `antiguedadalaventa` (
  `aavId` int(10) UNSIGNED NOT NULL,
  `aavAntId` int(10) UNSIGNED NOT NULL,
  `aavUsrIdVendedor` int(10) UNSIGNED NOT NULL,
  `aavDomOrigen` int(10) UNSIGNED NOT NULL,
  `aavPrecioVenta` decimal(15,2) UNSIGNED NOT NULL,
  `aavTadId` int(10) UNSIGNED DEFAULT NULL,
  `aavFechaPublicacion` datetime NOT NULL DEFAULT current_timestamp(),
  `aavFechaRetiro` datetime DEFAULT NULL,
  `aavHayVenta` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONES PARA LA TABLA `antiguedadalaventa`:
--   `aavAntId`
--       `antiguedad` -> `antId`
--   `aavDomOrigen`
--       `domicilio` -> `domId`
--   `aavTadId`
--       `tasaciondigital` -> `tadId`
--   `aavUsrIdVendedor`
--       `usuario` -> `usrId`
--

--
-- Volcado de datos para la tabla `antiguedadalaventa`
--

INSERT INTO `antiguedadalaventa` (`aavId`, `aavAntId`, `aavUsrIdVendedor`, `aavDomOrigen`, `aavPrecioVenta`, `aavTadId`, `aavFechaPublicacion`, `aavFechaRetiro`, `aavHayVenta`) VALUES
(1, 3, 2, 2, 781000000.13, 1, '2025-09-11 12:02:56', NULL, 1),
(2, 1, 4, 4, 1500632.25, NULL, '2025-10-27 14:39:46', '2025-10-27 15:26:54', 0),
(3, 1, 4, 4, 1600342.23, NULL, '2025-10-27 15:33:36', NULL, 1),
(4, 6, 4, 4, 5698895.35, NULL, '2025-10-27 20:47:57', NULL, 1),
(5, 5, 1, 13, 500523.18, NULL, '2025-10-27 22:51:07', NULL, 1),
(6, 3, 5, 1, 800000000.23, NULL, '2025-11-03 14:58:17', NULL, 0),
(7, 4, 5, 1, 5987345.00, NULL, '2025-11-03 15:00:48', NULL, 0),
(8, 5, 5, 1, 675987.00, NULL, '2025-11-03 15:01:18', NULL, 0),
(9, 1, 5, 1, 987543.00, NULL, '2025-11-03 15:01:51', NULL, 0),
(10, 6, 2, 2, 6325589.00, NULL, '2025-11-03 15:32:36', NULL, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

DROP TABLE IF EXISTS `categoria`;
CREATE TABLE `categoria` (
  `catId` int(10) UNSIGNED NOT NULL,
  `catDescripcion` varchar(50) NOT NULL,
  `catFechaBaja` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONES PARA LA TABLA `categoria`:
--

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`catId`, `catDescripcion`, `catFechaBaja`) VALUES
(1, 'Pinturas', NULL),
(2, 'Esculturas', NULL),
(3, 'Muebles', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compraventa`
--

DROP TABLE IF EXISTS `compraventa`;
CREATE TABLE `compraventa` (
  `covId` int(10) UNSIGNED NOT NULL,
  `covUsrComprador` int(10) UNSIGNED NOT NULL,
  `covDomDestino` int(10) UNSIGNED NOT NULL,
  `covFechaCompra` datetime NOT NULL DEFAULT current_timestamp(),
  `covTipoMedioPago` char(2) NOT NULL,
  `covFechaBaja` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONES PARA LA TABLA `compraventa`:
--   `covDomDestino`
--       `domicilio` -> `domId`
--   `covTipoMedioPago`
--       `tipomediopago` -> `tmpTipoMedioPago`
--   `covUsrComprador`
--       `usuario` -> `usrId`
--

--
-- Volcado de datos para la tabla `compraventa`
--

INSERT INTO `compraventa` (`covId`, `covUsrComprador`, `covDomDestino`, `covFechaCompra`, `covTipoMedioPago`, `covFechaBaja`) VALUES
(1, 5, 1, '2025-09-11 18:23:58', 'MP', NULL),
(3, 5, 1, '2025-11-02 18:05:58', 'TC', NULL),
(4, 2, 2, '2025-11-03 12:04:16', 'MP', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compraventadetalle`
--

DROP TABLE IF EXISTS `compraventadetalle`;
CREATE TABLE `compraventadetalle` (
  `cvdId` int(10) UNSIGNED NOT NULL,
  `cvdCovId` int(10) UNSIGNED NOT NULL,
  `cvdAavId` int(10) UNSIGNED NOT NULL,
  `cvdFechaEntregaPrevista` date NOT NULL,
  `cvdFechaEntregaReal` date DEFAULT NULL,
  `cvdFechaBaja` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONES PARA LA TABLA `compraventadetalle`:
--   `cvdAavId`
--       `antiguedadalaventa` -> `aavId`
--   `cvdCovId`
--       `compraventa` -> `covId`
--

--
-- Volcado de datos para la tabla `compraventadetalle`
--

INSERT INTO `compraventadetalle` (`cvdId`, `cvdCovId`, `cvdAavId`, `cvdFechaEntregaPrevista`, `cvdFechaEntregaReal`, `cvdFechaBaja`) VALUES
(1, 1, 1, '2025-09-16', '2025-09-11', NULL),
(4, 3, 5, '2025-11-12', NULL, NULL),
(5, 3, 3, '2025-11-03', '2025-11-03', NULL),
(6, 4, 4, '2025-11-04', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `domicilio`
--

DROP TABLE IF EXISTS `domicilio`;
CREATE TABLE `domicilio` (
  `domId` int(10) UNSIGNED NOT NULL,
  `domLocId` int(10) UNSIGNED NOT NULL,
  `domCPA` char(8) NOT NULL,
  `domCalleRuta` varchar(50) NOT NULL,
  `domNroKm` int(10) UNSIGNED NOT NULL,
  `domPiso` varchar(10) DEFAULT NULL,
  `domDepto` varchar(10) DEFAULT NULL,
  `domFechaInsert` datetime NOT NULL DEFAULT current_timestamp(),
  `domFechaBaja` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONES PARA LA TABLA `domicilio`:
--   `domLocId`
--       `localidad` -> `locId`
--

--
-- Volcado de datos para la tabla `domicilio`
--

INSERT INTO `domicilio` (`domId`, `domLocId`, `domCPA`, `domCalleRuta`, `domNroKm`, `domPiso`, `domDepto`, `domFechaInsert`, `domFechaBaja`) VALUES
(1, 30, 'C1406DEH', 'Dávila', 926, '12', '175', '2025-05-11 18:00:31', NULL),
(2, 27, 'C1425DUR', 'Sánchez de Bustamante', 2173, '1', 'G', '2025-05-11 18:00:31', NULL),
(3, 1, 'B1900ALB', 'Calle 47', 1234, NULL, NULL, '2025-05-11 18:00:31', NULL),
(4, 29, 'C1066AAW', 'Bolivar', 1131, NULL, NULL, '2025-05-11 18:00:31', NULL),
(5, 17, 'M5602BAG', 'Juan José Castelli', 353, '1', 'A', '2025-05-11 21:37:57', NULL),
(7, 17, 'M5602BAG', 'Juan José Castelli', 353, NULL, NULL, '2025-06-01 21:35:28', NULL),
(8, 10, 'M1234KKK', 'Siempre Viva', 999, '5', 'G', '2025-10-14 00:04:39', NULL),
(13, 1, 'C1406DEH', '4', 12, '9', 'A', '2025-10-20 01:01:43', NULL),
(14, 33, 'E2822FZH', 'Sarmiento', 310, '1', 'E', '2025-11-03 00:17:29', NULL),
(15, 17, 'M5602CLJ', 'Perú', 788, NULL, NULL, '2025-11-03 00:42:28', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `imagenantiguedad`
--

DROP TABLE IF EXISTS `imagenantiguedad`;
CREATE TABLE `imagenantiguedad` (
  `imaId` int(10) UNSIGNED NOT NULL,
  `imaAntId` int(10) UNSIGNED NOT NULL,
  `imaUrl` varchar(350) NOT NULL,
  `imaNombreArchivo` varchar(50) NOT NULL,
  `imaFechaInsert` datetime NOT NULL DEFAULT current_timestamp(),
  `imaOrden` tinyint(3) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONES PARA LA TABLA `imagenantiguedad`:
--   `imaAntId`
--       `antiguedad` -> `antId`
--

--
-- Volcado de datos para la tabla `imagenantiguedad`
--

INSERT INTO `imagenantiguedad` (`imaId`, `imaAntId`, `imaUrl`, `imaNombreArchivo`, `imaFechaInsert`, `imaOrden`) VALUES
(1, 3, '/storage/imagenesAntiguedad/antId3_1749426389_detalle-mesa-redonda-medieval.jpeg', 'detalle-mesa-redonda-medieval.jpg', '2025-06-08 20:46:29', 2),
(2, 3, '/storage/imagenesAntiguedad/antId3_1749426389_tablaredonda.jpeg', 'tablaredonda.jpg', '2025-06-08 20:46:29', 1),
(3, 4, '/storage/imagenesAntiguedad/antId4_1750099643_ArmarioBarroco.jpeg', 'ArmarioBarroco.jpg', '2025-06-16 15:47:23', 1),
(6, 5, '/storage/imagenesAntiguedad/antId5_1761275174_mesa_frente.jpeg', 'mesa_frente.jpg', '2025-10-24 00:06:14', 1),
(14, 5, '/storage/imagenesAntiguedad/antId5_1761503766_mesa_en_angulo.jpeg', 'mesa_en_angulo.jpg', '2025-10-26 15:36:06', 3),
(15, 5, '/storage/imagenesAntiguedad/antId5_1761503766_mesa_arriba.jpeg', 'mesa_arriba.jpg', '2025-10-26 15:36:06', 2),
(18, 1, '/storage/imagenesAntiguedad/antId1_1761570676_vitrina.jpeg', 'vitrina.jpg', '2025-10-27 10:11:16', 1),
(19, 6, '/storage/imagenesAntiguedad/antId6_1761590350_machuPichuPopArt.jpeg', 'machuPichuPopArt.jpg', '2025-10-27 15:39:10', 2),
(20, 6, '/storage/imagenesAntiguedad/antId6_1761590350_rizky-irawan-macchu-picchu.jpeg', 'rizky-irawan-macchu-picchu.jpg', '2025-10-27 15:39:10', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `localidad`
--

DROP TABLE IF EXISTS `localidad`;
CREATE TABLE `localidad` (
  `locId` int(10) UNSIGNED NOT NULL,
  `locProvId` smallint(6) NOT NULL,
  `locDescripcion` varchar(50) NOT NULL,
  `locFechaInsert` datetime NOT NULL DEFAULT current_timestamp(),
  `locFechaBaja` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONES PARA LA TABLA `localidad`:
--   `locProvId`
--       `provincia` -> `provId`
--

--
-- Volcado de datos para la tabla `localidad`
--

INSERT INTO `localidad` (`locId`, `locProvId`, `locDescripcion`, `locFechaInsert`, `locFechaBaja`) VALUES
(1, 2, 'La Plata', '2025-05-07 22:48:02', NULL),
(2, 2, 'Mar del Plata', '2025-05-07 22:48:02', NULL),
(3, 2, 'Bahía Blanca', '2025-05-07 22:48:02', NULL),
(4, 2, 'San Nicolás de los Arroyos', '2025-05-07 22:48:02', NULL),
(5, 2, 'Pergamino', '2025-05-07 22:48:02', NULL),
(6, 6, 'Córdoba (capital provincial)', '2025-05-07 22:48:02', NULL),
(7, 6, 'Villa Carlos Paz', '2025-05-07 22:48:02', NULL),
(8, 6, 'Río Cuarto', '2025-05-07 22:48:02', NULL),
(9, 6, 'Alta Gracia', '2025-05-07 22:48:02', NULL),
(10, 6, 'Jesús María', '2025-05-07 22:48:02', NULL),
(11, 21, 'Rosario', '2025-05-07 22:48:02', NULL),
(12, 21, 'Santa Fe (capital provincial)', '2025-05-07 22:48:02', NULL),
(13, 21, 'Rafaela', '2025-05-07 22:48:02', NULL),
(14, 21, 'Venado Tuerto', '2025-05-07 22:48:02', NULL),
(15, 21, 'Reconquista', '2025-05-07 22:48:02', NULL),
(16, 13, 'Mendoza (capital provincial)', '2025-05-07 22:48:02', NULL),
(17, 13, 'San Rafael', '2025-05-07 22:48:02', NULL),
(18, 13, 'Godoy Cruz', '2025-05-07 22:48:02', NULL),
(19, 13, 'Maipú', '2025-05-07 22:48:02', NULL),
(20, 13, 'Luján de Cuyo', '2025-05-07 22:48:02', NULL),
(21, 24, 'San Miguel de Tucumán (capital provincial)', '2025-05-07 22:48:02', NULL),
(22, 24, 'Yerba Buena', '2025-05-07 22:48:02', NULL),
(23, 24, 'Tafí Viejo', '2025-05-07 22:48:02', NULL),
(24, 24, 'Concepción', '2025-05-07 22:48:02', NULL),
(25, 24, 'Banda del Río Salí', '2025-05-07 22:48:02', NULL),
(26, 1, 'Palermo', '2025-05-07 22:48:02', NULL),
(27, 1, 'Recoleta', '2025-05-07 22:48:02', NULL),
(28, 1, 'Belgrano', '2025-05-07 22:48:02', NULL),
(29, 1, 'San Telmo', '2025-05-07 22:48:02', NULL),
(30, 1, 'Parque Chacabuco', '2025-05-07 22:48:02', NULL),
(31, 8, 'Paraná', '2025-05-07 22:48:02', NULL),
(32, 8, 'Concordia', '2025-05-07 22:48:02', NULL),
(33, 8, 'Gualeguaychú', '2025-05-07 22:48:02', NULL),
(34, 8, 'Gualeguay', '2025-05-07 22:48:02', NULL),
(35, 8, 'Villa Paranacito', '2025-05-07 22:48:02', NULL),
(36, 1, 'Caballito', '2025-05-10 23:19:45', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `periodo`
--

DROP TABLE IF EXISTS `periodo`;
CREATE TABLE `periodo` (
  `perId` int(10) UNSIGNED NOT NULL,
  `perDescripcion` varchar(50) NOT NULL,
  `perFechaBaja` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONES PARA LA TABLA `periodo`:
--

--
-- Volcado de datos para la tabla `periodo`
--

INSERT INTO `periodo` (`perId`, `perDescripcion`, `perFechaBaja`) VALUES
(1, 'Renacentista', NULL),
(2, 'Barroco', NULL),
(3, 'Pop Art', NULL),
(4, 'Medieval', NULL),
(5, 'Colonial', NULL),
(6, 'Postindustrial', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `provincia`
--

DROP TABLE IF EXISTS `provincia`;
CREATE TABLE `provincia` (
  `provId` smallint(6) NOT NULL,
  `provDescripcion` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONES PARA LA TABLA `provincia`:
--

--
-- Volcado de datos para la tabla `provincia`
--

INSERT INTO `provincia` (`provId`, `provDescripcion`) VALUES
(1, 'Ciudad Autónoma de Buenos Aires'),
(2, 'Buenos Aires'),
(3, 'Catamarca'),
(4, 'Chaco'),
(5, 'Chubut'),
(6, 'Córdoba'),
(7, 'Corrientes'),
(8, 'Entre Ríos'),
(9, 'Formosa'),
(10, 'Jujuy'),
(11, 'La Pampa'),
(12, 'La Rioja'),
(13, 'Mendoza'),
(14, 'Misiones'),
(15, 'Neuquén'),
(16, 'Río Negro'),
(17, 'Salta'),
(18, 'San Juan'),
(19, 'San Luis'),
(20, 'Santa Cruz'),
(21, 'Santa Fe'),
(22, 'Santiago del Estero'),
(23, 'Tierra del Fuego'),
(24, 'Tucumán');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `subcategoria`
--

DROP TABLE IF EXISTS `subcategoria`;
CREATE TABLE `subcategoria` (
  `scatId` int(10) UNSIGNED NOT NULL,
  `scatCatId` int(10) UNSIGNED NOT NULL,
  `scatDescripcion` varchar(50) NOT NULL,
  `scatFechaBaja` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONES PARA LA TABLA `subcategoria`:
--   `scatCatId`
--       `categoria` -> `catId`
--

--
-- Volcado de datos para la tabla `subcategoria`
--

INSERT INTO `subcategoria` (`scatId`, `scatCatId`, `scatDescripcion`, `scatFechaBaja`) VALUES
(5, 1, 'Paisajes', NULL),
(4, 2, 'Animales', NULL),
(3, 2, 'Personas', NULL),
(6, 3, 'Armarios', NULL),
(2, 3, 'Escritorios', NULL),
(8, 3, 'Mesas', NULL),
(1, 3, 'Sillas', NULL),
(7, 3, 'Vitrina', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tasaciondigital`
--

DROP TABLE IF EXISTS `tasaciondigital`;
CREATE TABLE `tasaciondigital` (
  `tadId` int(10) UNSIGNED NOT NULL,
  `tadUsrTasId` int(10) UNSIGNED NOT NULL,
  `tadUsrPropId` int(10) UNSIGNED NOT NULL,
  `tadAntId` int(10) UNSIGNED NOT NULL,
  `tadFechaSolicitud` date NOT NULL DEFAULT current_timestamp(),
  `tadFechaTasDigitalRealizada` date DEFAULT NULL,
  `tadFechaTasDigitalRechazada` date DEFAULT NULL,
  `tadObservacionesDigital` varchar(500) DEFAULT NULL,
  `tadPrecioDigital` decimal(15,2) UNSIGNED DEFAULT NULL,
  `tadFechaBaja` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONES PARA LA TABLA `tasaciondigital`:
--   `tadUsrPropId`
--       `usuario` -> `usrId`
--   `tadAntId`
--       `antiguedad` -> `antId`
--   `tadUsrTasId`
--       `usuario` -> `usrId`
--

--
-- Volcado de datos para la tabla `tasaciondigital`
--

INSERT INTO `tasaciondigital` (`tadId`, `tadUsrTasId`, `tadUsrPropId`, `tadAntId`, `tadFechaSolicitud`, `tadFechaTasDigitalRealizada`, `tadFechaTasDigitalRechazada`, `tadObservacionesDigital`, `tadPrecioDigital`, `tadFechaBaja`) VALUES
(1, 3, 2, 3, '2025-06-16', '2025-06-16', NULL, 'Ta buena la mesa', 590000000.95, '2025-09-11 18:23:58'),
(2, 4, 5, 4, '2025-06-16', '2025-06-16', NULL, 'Muy lindo armario Barroco', 659000.00, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tasacioninsitu`
--

DROP TABLE IF EXISTS `tasacioninsitu`;
CREATE TABLE `tasacioninsitu` (
  `tisId` int(10) UNSIGNED NOT NULL,
  `tisTadId` int(10) UNSIGNED NOT NULL,
  `tisDomTasId` int(10) UNSIGNED NOT NULL,
  `tisFechaTasInSituSolicitada` date NOT NULL DEFAULT current_timestamp(),
  `tisFechaTasInSituProvisoria` date NOT NULL,
  `tisFechaTasInSituRealizada` date DEFAULT NULL,
  `tisFechaTasInSituRechazada` date DEFAULT NULL,
  `tisObservacionesInSitu` varchar(500) DEFAULT NULL,
  `tisPrecioInSitu` decimal(15,2) DEFAULT NULL,
  `tisFechaBaja` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONES PARA LA TABLA `tasacioninsitu`:
--   `tisDomTasId`
--       `domicilio` -> `domId`
--   `tisTadId`
--       `tasaciondigital` -> `tadId`
--

--
-- Volcado de datos para la tabla `tasacioninsitu`
--

INSERT INTO `tasacioninsitu` (`tisId`, `tisTadId`, `tisDomTasId`, `tisFechaTasInSituSolicitada`, `tisFechaTasInSituProvisoria`, `tisFechaTasInSituRealizada`, `tisFechaTasInSituRechazada`, `tisObservacionesInSitu`, `tisPrecioInSitu`, `tisFechaBaja`) VALUES
(1, 1, 2, '2025-06-16', '2025-06-20', '2025-06-16', NULL, 'Posta, es la mesa del Rey Arturo!!', 780000000.13, '2025-09-11 18:23:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipoestado`
--

DROP TABLE IF EXISTS `tipoestado`;
CREATE TABLE `tipoestado` (
  `tteTipoEstado` char(2) NOT NULL,
  `tteTipoEstadoDescripcion` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONES PARA LA TABLA `tipoestado`:
--

--
-- Volcado de datos para la tabla `tipoestado`
--

INSERT INTO `tipoestado` (`tteTipoEstado`, `tteTipoEstadoDescripcion`) VALUES
('CO', 'Comprado'),
('RD', 'Retirado Disponible'),
('RN', 'Retirado No Disponible'),
('TD', 'Tasado Digital'),
('TI', 'Tasado In Situ'),
('VE', 'A la Venta');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipomediopago`
--

DROP TABLE IF EXISTS `tipomediopago`;
CREATE TABLE `tipomediopago` (
  `tmpTipoMedioPago` char(2) NOT NULL,
  `tmpDescripcion` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONES PARA LA TABLA `tipomediopago`:
--

--
-- Volcado de datos para la tabla `tipomediopago`
--

INSERT INTO `tipomediopago` (`tmpTipoMedioPago`, `tmpDescripcion`) VALUES
('MP', 'Mercado Pago'),
('TB', 'Transferencia Bancaria'),
('TC', 'Tarjeta de Crédito');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipousuario`
--

DROP TABLE IF EXISTS `tipousuario`;
CREATE TABLE `tipousuario` (
  `ttuTipoUsuario` char(2) NOT NULL,
  `ttuDescripcion` varchar(25) NOT NULL,
  `ttuRequiereMatricula` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONES PARA LA TABLA `tipousuario`:
--

--
-- Volcado de datos para la tabla `tipousuario`
--

INSERT INTO `tipousuario` (`ttuTipoUsuario`, `ttuDescripcion`, `ttuRequiereMatricula`) VALUES
('SI', 'Seguridad Informática', 0),
('ST', 'Soporte Técnico', 0),
('UA', 'Usuario Anticuario', 1),
('UG', 'Usuario General', 0),
('UT', 'Usuario Tasador', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tokens`
--

DROP TABLE IF EXISTS `tokens`;
CREATE TABLE `tokens` (
  `tokToken` varchar(500) NOT NULL,
  `tokFechaInsert` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONES PARA LA TABLA `tokens`:
--

--
-- Volcado de datos para la tabla `tokens`
--

INSERT INTO `tokens` (`tokToken`, `tokFechaInsert`) VALUES
('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJ1c3JJZCI6MSwidXNyTm9tYnJlIjoiTmljb2xcdTAwZTFzIEFsZWphbmRybyIsInVzclRpcG9Vc3VhcmlvIjoiU1QiLCJleHAiOjE3NjI3MzI5NzZ9.x4Rrz115v86EXJeDMgJmS9pt5981e1pxgKRvQVYZN8dezVEoPcbzYhvhO1T0_vQFELL1gbsITvUFShxvkUU3Ug', '2025-11-09 20:02:56');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

DROP TABLE IF EXISTS `usuario`;
CREATE TABLE `usuario` (
  `usrId` int(10) UNSIGNED NOT NULL,
  `usrDni` char(8) NOT NULL,
  `usrApellido` varchar(50) NOT NULL,
  `usrNombre` varchar(50) NOT NULL,
  `usrRazonSocialFantasia` varchar(100) DEFAULT NULL,
  `usrCuitCuil` char(11) DEFAULT NULL,
  `usrTipoUsuario` char(2) NOT NULL,
  `usrMatricula` varchar(20) DEFAULT NULL,
  `usrDomicilio` int(10) UNSIGNED NOT NULL,
  `usrFechaNacimiento` date NOT NULL,
  `usrDescripcion` varchar(500) DEFAULT NULL,
  `usrScoring` int(10) NOT NULL DEFAULT 0,
  `usrEmail` varchar(100) NOT NULL,
  `usrPassword` varchar(255) NOT NULL,
  `usrFechaInsert` datetime NOT NULL DEFAULT current_timestamp(),
  `usrFechaBaja` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONES PARA LA TABLA `usuario`:
--   `usrDomicilio`
--       `domicilio` -> `domId`
--   `usrTipoUsuario`
--       `tipousuario` -> `ttuTipoUsuario`
--

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`usrId`, `usrDni`, `usrApellido`, `usrNombre`, `usrRazonSocialFantasia`, `usrCuitCuil`, `usrTipoUsuario`, `usrMatricula`, `usrDomicilio`, `usrFechaNacimiento`, `usrDescripcion`, `usrScoring`, `usrEmail`, `usrPassword`, `usrFechaInsert`, `usrFechaBaja`) VALUES
(1, '33698895', 'Gómez Ivaldi', 'Nicolás Alejandro', NULL, NULL, 'ST', NULL, 1, '1988-03-29', 'Soy uno de los creadores de esta WebApi.', 0, 'nicoivaldi@agora.com', '$2y$10$jA79h6pSsRRhYnxts.57ru4ILsQwMYyXt5pEbuDyHoOyw0/OHR.Yi', '2024-07-07 18:38:07', NULL),
(2, '33286958', 'Sosa Leonetti', 'Cristian Javier', NULL, NULL, 'UG', NULL, 2, '1988-02-08', NULL, 0, 'sleonetti@gmail.com', '$2y$10$HjR2rlPfne0GyNXGJ41jU.EiCfvVpMpQ5cOvRbitoynkYeMaEGnM.', '2024-07-08 14:42:43', NULL),
(3, '29741295', 'Galíndez', 'Gustavo', 'Tasaciones Galíndez Jumbo SH', '30708772964', 'UT', '123456', 3, '1984-01-01', 'Tasamos el valor de sus afectos al mejor precio de Mercado.', 50, 'gusgalindez@tasgalindez.com', '$2y$10$U9YS.OMtjMhnOzAUWovHv.uQo38bb3dva9qDUmq48w5fBZW0NVsyq', '2024-07-10 16:18:01', NULL),
(4, '27965368', 'Rolón', 'Karina', 'Paraíso Antigüedades SA', '30123456781', 'UA', '95874L', 4, '1982-06-12', 'Compra y Venta de antigüedades. Tasamos.', 60, 'krolon@paraiso.com', '$2y$10$Os4S45NKUqrBnLYDqALpYewr8CBMbKx8n4dNm9FTLhg7ySVxgcTx2', '2024-07-10 16:18:01', NULL),
(5, '13355922', 'Recondo', 'Adriana Mariel', NULL, NULL, 'UG', NULL, 1, '1959-07-09', NULL, 0, 'recondomariel@uol.com', '$2y$10$n6.BF3HUDCMABqY7iRHOOuhPw..8nlE.cws7vSjmyIRNuYee1iXZu', '2025-06-01 21:32:15', NULL),
(7, '40526987', 'Casado', 'Manuel', NULL, NULL, 'UG', NULL, 3, '2002-02-14', 'Prueba QA', 0, 'mcasado@gmail.com', '$2y$10$sFyDIh548JJGZo9HS7c8pOTEOPatGXr6s6we7GiYQfstuQQNIJjv6', '2025-10-14 00:00:58', NULL),
(8, '54808315', 'Cualquiera', 'Tomás', 'SoyCoto', '30548083156', 'UT', 'lalalalalalalallawqw', 8, '1997-04-25', 'Tasador QA', 0, 'cualquiera@gmail.com', '$2y$10$m84itGDq78nLDedWrQ5ziOcZzWx4owEfYHcK/tKI9DPGgL.Xxu/g.', '2025-10-14 00:06:56', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuariodomicilio`
--

DROP TABLE IF EXISTS `usuariodomicilio`;
CREATE TABLE `usuariodomicilio` (
  `udomId` int(10) UNSIGNED NOT NULL,
  `udomUsr` int(10) UNSIGNED NOT NULL,
  `udomDom` int(10) UNSIGNED NOT NULL,
  `udomFechaInsert` datetime NOT NULL DEFAULT current_timestamp(),
  `udomFechaBaja` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONES PARA LA TABLA `usuariodomicilio`:
--   `udomDom`
--       `domicilio` -> `domId`
--   `udomUsr`
--       `usuario` -> `usrId`
--

--
-- Volcado de datos para la tabla `usuariodomicilio`
--

INSERT INTO `usuariodomicilio` (`udomId`, `udomUsr`, `udomDom`, `udomFechaInsert`, `udomFechaBaja`) VALUES
(1, 1, 1, '2025-07-27 14:49:46', NULL),
(2, 5, 1, '2025-07-27 14:49:46', NULL),
(3, 2, 2, '2025-07-27 14:49:46', NULL),
(4, 3, 3, '2025-07-27 14:49:46', NULL),
(5, 4, 4, '2025-07-27 14:49:46', NULL),
(7, 4, 5, '2025-07-28 20:16:24', NULL),
(8, 1, 13, '2025-10-20 01:01:43', NULL),
(9, 1, 14, '2025-11-03 00:17:29', NULL),
(10, 1, 15, '2025-11-03 00:42:28', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuariotasadorhabilidad`
--

DROP TABLE IF EXISTS `usuariotasadorhabilidad`;
CREATE TABLE `usuariotasadorhabilidad` (
  `utsId` int(10) UNSIGNED NOT NULL,
  `utsUsrId` int(10) UNSIGNED NOT NULL,
  `utsScatId` int(10) UNSIGNED NOT NULL,
  `utsPerId` int(10) UNSIGNED NOT NULL,
  `utsFechaInsert` datetime NOT NULL DEFAULT current_timestamp(),
  `utsFechaBaja` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONES PARA LA TABLA `usuariotasadorhabilidad`:
--   `utsPerId`
--       `periodo` -> `perId`
--   `utsScatId`
--       `subcategoria` -> `scatId`
--   `utsUsrId`
--       `usuario` -> `usrId`
--

--
-- Volcado de datos para la tabla `usuariotasadorhabilidad`
--

INSERT INTO `usuariotasadorhabilidad` (`utsId`, `utsUsrId`, `utsScatId`, `utsPerId`, `utsFechaInsert`, `utsFechaBaja`) VALUES
(1, 3, 5, 1, '2025-05-14 22:32:43', NULL),
(2, 3, 4, 2, '2025-05-14 22:32:43', NULL),
(3, 4, 1, 2, '2025-05-14 22:32:43', NULL),
(4, 4, 2, 1, '2025-05-14 22:32:43', NULL),
(5, 3, 8, 3, '2025-06-01 21:35:46', NULL),
(6, 3, 8, 4, '2025-06-14 21:40:54', NULL),
(7, 4, 6, 2, '2025-06-16 16:04:13', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `antiguedad`
--
ALTER TABLE `antiguedad`
  ADD PRIMARY KEY (`antId`),
  ADD KEY `FK_antiguedadSubcategoria` (`antScatId`),
  ADD KEY `FK_antiguedadPeriodo` (`antPerId`),
  ADD KEY `FK_antiguedadUsuario` (`antUsrId`),
  ADD KEY `FK_antiguedadTipoEstado` (`antTipoEstado`);

--
-- Indices de la tabla `antiguedadalaventa`
--
ALTER TABLE `antiguedadalaventa`
  ADD PRIMARY KEY (`aavId`),
  ADD KEY `FK_AlaVenta_Antiguedad` (`aavAntId`),
  ADD KEY `FK_AlaVenta_Tasacion` (`aavTadId`),
  ADD KEY `FK_AlaVenta_Domicilio` (`aavDomOrigen`),
  ADD KEY `FK_UsrVendedor` (`aavUsrIdVendedor`);

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`catId`);

--
-- Indices de la tabla `compraventa`
--
ALTER TABLE `compraventa`
  ADD PRIMARY KEY (`covId`),
  ADD KEY `FK_UsrComprador` (`covUsrComprador`),
  ADD KEY `FK_TipoMedioPago` (`covTipoMedioPago`),
  ADD KEY `FK_DomDestino` (`covDomDestino`);

--
-- Indices de la tabla `compraventadetalle`
--
ALTER TABLE `compraventadetalle`
  ADD PRIMARY KEY (`cvdId`),
  ADD KEY `FK_CompraVenta` (`cvdCovId`),
  ADD KEY `FK_AntiguedadaLaVenta` (`cvdAavId`);

--
-- Indices de la tabla `domicilio`
--
ALTER TABLE `domicilio`
  ADD PRIMARY KEY (`domId`),
  ADD KEY `FK_domicilioLocalidad` (`domLocId`);

--
-- Indices de la tabla `imagenantiguedad`
--
ALTER TABLE `imagenantiguedad`
  ADD PRIMARY KEY (`imaId`),
  ADD KEY `FK_imagenAntiguedad` (`imaAntId`);

--
-- Indices de la tabla `localidad`
--
ALTER TABLE `localidad`
  ADD PRIMARY KEY (`locId`),
  ADD KEY `FK_localidadProv` (`locProvId`);

--
-- Indices de la tabla `periodo`
--
ALTER TABLE `periodo`
  ADD PRIMARY KEY (`perId`);

--
-- Indices de la tabla `provincia`
--
ALTER TABLE `provincia`
  ADD PRIMARY KEY (`provId`);

--
-- Indices de la tabla `subcategoria`
--
ALTER TABLE `subcategoria`
  ADD PRIMARY KEY (`scatCatId`,`scatDescripcion`),
  ADD UNIQUE KEY `scatId` (`scatId`);

--
-- Indices de la tabla `tasaciondigital`
--
ALTER TABLE `tasaciondigital`
  ADD PRIMARY KEY (`tadId`),
  ADD KEY `FK_TasadorUsuario` (`tadUsrTasId`),
  ADD KEY `FK_TasAntAntiguedad` (`tadAntId`),
  ADD KEY `FK_PropietarioUsuario` (`tadUsrPropId`);

--
-- Indices de la tabla `tasacioninsitu`
--
ALTER TABLE `tasacioninsitu`
  ADD PRIMARY KEY (`tisId`),
  ADD KEY `FK_InSituDomicilio` (`tisDomTasId`),
  ADD KEY `FK_InSituTasDigital` (`tisTadId`);

--
-- Indices de la tabla `tipoestado`
--
ALTER TABLE `tipoestado`
  ADD PRIMARY KEY (`tteTipoEstado`);

--
-- Indices de la tabla `tipomediopago`
--
ALTER TABLE `tipomediopago`
  ADD PRIMARY KEY (`tmpTipoMedioPago`);

--
-- Indices de la tabla `tipousuario`
--
ALTER TABLE `tipousuario`
  ADD PRIMARY KEY (`ttuTipoUsuario`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`usrId`),
  ADD KEY `FK_usuarioTipoUsuario` (`usrTipoUsuario`),
  ADD KEY `FK_usuarioDomicilio` (`usrDomicilio`);

--
-- Indices de la tabla `usuariodomicilio`
--
ALTER TABLE `usuariodomicilio`
  ADD PRIMARY KEY (`udomId`),
  ADD KEY `UdomUsuario` (`udomUsr`),
  ADD KEY `UdomDomicilio` (`udomDom`);

--
-- Indices de la tabla `usuariotasadorhabilidad`
--
ALTER TABLE `usuariotasadorhabilidad`
  ADD PRIMARY KEY (`utsId`,`utsUsrId`,`utsScatId`,`utsPerId`),
  ADD KEY `FK_UsrTasHab_Usuario` (`utsUsrId`),
  ADD KEY `FK_UsrTasHab_SubCat` (`utsScatId`),
  ADD KEY `FK_UsrTasHab_Periodo` (`utsPerId`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `antiguedad`
--
ALTER TABLE `antiguedad`
  MODIFY `antId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `antiguedadalaventa`
--
ALTER TABLE `antiguedadalaventa`
  MODIFY `aavId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `catId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `compraventa`
--
ALTER TABLE `compraventa`
  MODIFY `covId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `compraventadetalle`
--
ALTER TABLE `compraventadetalle`
  MODIFY `cvdId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `domicilio`
--
ALTER TABLE `domicilio`
  MODIFY `domId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `imagenantiguedad`
--
ALTER TABLE `imagenantiguedad`
  MODIFY `imaId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT de la tabla `localidad`
--
ALTER TABLE `localidad`
  MODIFY `locId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de la tabla `periodo`
--
ALTER TABLE `periodo`
  MODIFY `perId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `provincia`
--
ALTER TABLE `provincia`
  MODIFY `provId` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `subcategoria`
--
ALTER TABLE `subcategoria`
  MODIFY `scatId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `tasaciondigital`
--
ALTER TABLE `tasaciondigital`
  MODIFY `tadId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tasacioninsitu`
--
ALTER TABLE `tasacioninsitu`
  MODIFY `tisId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `usrId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `usuariodomicilio`
--
ALTER TABLE `usuariodomicilio`
  MODIFY `udomId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `usuariotasadorhabilidad`
--
ALTER TABLE `usuariotasadorhabilidad`
  MODIFY `utsId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `antiguedad`
--
ALTER TABLE `antiguedad`
  ADD CONSTRAINT `FK_antiguedadPeriodo` FOREIGN KEY (`antPerId`) REFERENCES `periodo` (`perId`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_antiguedadSubcategoria` FOREIGN KEY (`antScatId`) REFERENCES `subcategoria` (`scatId`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_antiguedadTipoEstado` FOREIGN KEY (`antTipoEstado`) REFERENCES `tipoestado` (`tteTipoEstado`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_antiguedadUsuario` FOREIGN KEY (`antUsrId`) REFERENCES `usuario` (`usrId`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `antiguedadalaventa`
--
ALTER TABLE `antiguedadalaventa`
  ADD CONSTRAINT `FK_AlaVenta_Antiguedad` FOREIGN KEY (`aavAntId`) REFERENCES `antiguedad` (`antId`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_AlaVenta_Domicilio` FOREIGN KEY (`aavDomOrigen`) REFERENCES `domicilio` (`domId`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_AlaVenta_Tasacion` FOREIGN KEY (`aavTadId`) REFERENCES `tasaciondigital` (`tadId`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_UsrVendedor` FOREIGN KEY (`aavUsrIdVendedor`) REFERENCES `usuario` (`usrId`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `compraventa`
--
ALTER TABLE `compraventa`
  ADD CONSTRAINT `FK_DomDestino` FOREIGN KEY (`covDomDestino`) REFERENCES `domicilio` (`domId`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_TipoMedioPago` FOREIGN KEY (`covTipoMedioPago`) REFERENCES `tipomediopago` (`tmpTipoMedioPago`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_UsrComprador` FOREIGN KEY (`covUsrComprador`) REFERENCES `usuario` (`usrId`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `compraventadetalle`
--
ALTER TABLE `compraventadetalle`
  ADD CONSTRAINT `FK_AntiguedadaLaVenta` FOREIGN KEY (`cvdAavId`) REFERENCES `antiguedadalaventa` (`aavId`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_CompraVenta` FOREIGN KEY (`cvdCovId`) REFERENCES `compraventa` (`covId`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `domicilio`
--
ALTER TABLE `domicilio`
  ADD CONSTRAINT `FK_domicilioLocalidad` FOREIGN KEY (`domLocId`) REFERENCES `localidad` (`locId`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `imagenantiguedad`
--
ALTER TABLE `imagenantiguedad`
  ADD CONSTRAINT `FK_imagenAntiguedad` FOREIGN KEY (`imaAntId`) REFERENCES `antiguedad` (`antId`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `localidad`
--
ALTER TABLE `localidad`
  ADD CONSTRAINT `FK_localidadProv` FOREIGN KEY (`locProvId`) REFERENCES `provincia` (`provId`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `subcategoria`
--
ALTER TABLE `subcategoria`
  ADD CONSTRAINT `FK_SubcatCat` FOREIGN KEY (`scatCatId`) REFERENCES `categoria` (`catId`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `tasaciondigital`
--
ALTER TABLE `tasaciondigital`
  ADD CONSTRAINT `FK_PropietarioUsuario` FOREIGN KEY (`tadUsrPropId`) REFERENCES `usuario` (`usrId`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_TasAntAntiguedad` FOREIGN KEY (`tadAntId`) REFERENCES `antiguedad` (`antId`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_TasadorUsuario` FOREIGN KEY (`tadUsrTasId`) REFERENCES `usuario` (`usrId`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `tasacioninsitu`
--
ALTER TABLE `tasacioninsitu`
  ADD CONSTRAINT `FK_InSituDomicilio` FOREIGN KEY (`tisDomTasId`) REFERENCES `domicilio` (`domId`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_InSituTasDigital` FOREIGN KEY (`tisTadId`) REFERENCES `tasaciondigital` (`tadId`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `FK_usuarioDomicilio` FOREIGN KEY (`usrDomicilio`) REFERENCES `domicilio` (`domId`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_usuarioTipoUsuario` FOREIGN KEY (`usrTipoUsuario`) REFERENCES `tipousuario` (`ttuTipoUsuario`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuariodomicilio`
--
ALTER TABLE `usuariodomicilio`
  ADD CONSTRAINT `UdomDomicilio` FOREIGN KEY (`udomDom`) REFERENCES `domicilio` (`domId`) ON UPDATE CASCADE,
  ADD CONSTRAINT `UdomUsuario` FOREIGN KEY (`udomUsr`) REFERENCES `usuario` (`usrId`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuariotasadorhabilidad`
--
ALTER TABLE `usuariotasadorhabilidad`
  ADD CONSTRAINT `FK_UsrTasHab_Periodo` FOREIGN KEY (`utsPerId`) REFERENCES `periodo` (`perId`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_UsrTasHab_SubCat` FOREIGN KEY (`utsScatId`) REFERENCES `subcategoria` (`scatId`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_UsrTasHab_Usuario` FOREIGN KEY (`utsUsrId`) REFERENCES `usuario` (`usrId`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
