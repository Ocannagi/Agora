-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 08-05-2025 a las 04:31:42
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
-- Estructura de tabla para la tabla `categoria`
--

DROP TABLE IF EXISTS `categoria`;
CREATE TABLE IF NOT EXISTS `categoria` (
  `catId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `catDescripcion` varchar(50) NOT NULL,
  `catFechaBaja` datetime DEFAULT NULL,
  PRIMARY KEY (`catId`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`catId`, `catDescripcion`, `catFechaBaja`) VALUES
(1, 'Pinturas', NULL),
(2, 'Esculturas', NULL),
(3, 'Muebles', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `domicilio`
--

DROP TABLE IF EXISTS `domicilio`;
CREATE TABLE IF NOT EXISTS `domicilio` (
  `domID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `domLocalidad` int(10) UNSIGNED NOT NULL,
  `domCPA` char(8) NOT NULL,
  `domCalleRuta` varchar(50) NOT NULL,
  `domNroKm` int(10) UNSIGNED NOT NULL,
  `domPiso` varchar(10) DEFAULT NULL,
  `domDepto` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`domID`),
  KEY `FK_domicilioLocalidad` (`domLocalidad`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `domicilio`
--

INSERT INTO `domicilio` (`domID`, `domLocalidad`, `domCPA`, `domCalleRuta`, `domNroKm`, `domPiso`, `domDepto`) VALUES
(1, 30, 'C1406DEH', 'Dávila', 926, '12', '175'),
(2, 27, 'C1425DUR', 'Sánchez de Bustamante', 2173, '1', 'G'),
(3, 1, 'B1900ALB', 'Calle 47', 1234, NULL, NULL),
(4, 29, 'C1066AAW', 'Bolivar', 1131, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `localidad`
--

DROP TABLE IF EXISTS `localidad`;
CREATE TABLE IF NOT EXISTS `localidad` (
  `locId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `locProvID` smallint(6) NOT NULL,
  `locDescripcion` varchar(50) NOT NULL,
  `locFechaInsert` datetime NOT NULL DEFAULT current_timestamp(),
  `locFechaBaja` datetime DEFAULT NULL,
  PRIMARY KEY (`locId`),
  KEY `FK_localidadProv` (`locProvID`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `localidad`
--

INSERT INTO `localidad` (`locId`, `locProvID`, `locDescripcion`, `locFechaInsert`, `locFechaBaja`) VALUES
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
(35, 8, 'Villa Paranacito', '2025-05-07 22:48:02', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `periodo`
--

DROP TABLE IF EXISTS `periodo`;
CREATE TABLE IF NOT EXISTS `periodo` (
  `perId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `perDescripcion` varchar(50) NOT NULL,
  `perFechaBaja` datetime DEFAULT NULL,
  PRIMARY KEY (`perId`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `periodo`
--

INSERT INTO `periodo` (`perId`, `perDescripcion`, `perFechaBaja`) VALUES
(1, 'Renacentista', NULL),
(2, 'Barroco', NULL),
(3, 'Pop Art', NULL),
(5, 'Colonial', NULL),
(6, 'Postindustrial', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `provincia`
--

DROP TABLE IF EXISTS `provincia`;
CREATE TABLE IF NOT EXISTS `provincia` (
  `provId` smallint(6) NOT NULL AUTO_INCREMENT,
  `provDescripcion` varchar(40) NOT NULL,
  PRIMARY KEY (`provId`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
CREATE TABLE IF NOT EXISTS `subcategoria` (
  `scatId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `scatCatId` int(10) UNSIGNED NOT NULL,
  `scatDescripcion` varchar(50) NOT NULL,
  `scatFechaBaja` datetime DEFAULT NULL,
  PRIMARY KEY (`scatCatId`,`scatDescripcion`),
  UNIQUE KEY `scatId` (`scatId`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `subcategoria`
--

INSERT INTO `subcategoria` (`scatId`, `scatCatId`, `scatDescripcion`, `scatFechaBaja`) VALUES
(5, 1, 'Paisajes', NULL),
(4, 2, 'Animales', NULL),
(3, 2, 'Personas', NULL),
(6, 3, 'Armarios', NULL),
(2, 3, 'Escritorios', NULL),
(1, 3, 'Sillas', NULL),
(7, 3, 'Vitrina', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipousuario`
--

DROP TABLE IF EXISTS `tipousuario`;
CREATE TABLE IF NOT EXISTS `tipousuario` (
  `ttuTipoUsuario` char(2) NOT NULL,
  `ttuDescripcion` varchar(25) NOT NULL,
  `ttuRequiereMatricula` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`ttuTipoUsuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
CREATE TABLE IF NOT EXISTS `tokens` (
  `tokToken` varchar(500) NOT NULL,
  `tokFechaInsert` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tokens`
--

INSERT INTO `tokens` (`tokToken`, `tokFechaInsert`) VALUES
('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJ1c3JJZCI6MSwidXNyTm9tYnJlIjoiTmljb2xcdTAwZTFzIEFsZWphbmRybyIsInVzclRpcG9Vc3VhcmlvIjoiU1QiLCJleHAiOjE3NDY2NjgzMTd9.RgSotidU2KzNLvNiBbjqPEfQ0Oy-U9awftZjxvcRX371sMWR3Y-NTNQNH7R-R-zVVbzk3NSv0BIrajUXa5h73w', '2025-05-07 21:38:37'),
('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJ1c3JJZCI6MSwidXNyTm9tYnJlIjoiTmljb2xcdTAwZTFzIEFsZWphbmRybyIsInVzclRpcG9Vc3VhcmlvIjoiU1QiLCJleHAiOjE3NDY2Njk0OTd9.PoqamrV3pbChx1FYg7PtLO3xrXgRUMYb6UZdJK8vKEVhXwB_rU_aeN8p0XojZUxsa80byc0H8ZQC6OU-KkKr8Q', '2025-05-07 21:58:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

DROP TABLE IF EXISTS `usuario`;
CREATE TABLE IF NOT EXISTS `usuario` (
  `usrId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
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
  `usrFechaBaja` datetime DEFAULT NULL,
  PRIMARY KEY (`usrId`),
  KEY `FK_usuarioTipoUsuario` (`usrTipoUsuario`),
  KEY `FK_usuarioDomicilio` (`usrDomicilio`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`usrId`, `usrDni`, `usrApellido`, `usrNombre`, `usrRazonSocialFantasia`, `usrCuitCuil`, `usrTipoUsuario`, `usrMatricula`, `usrDomicilio`, `usrFechaNacimiento`, `usrDescripcion`, `usrScoring`, `usrEmail`, `usrPassword`, `usrFechaInsert`, `usrFechaBaja`) VALUES
(1, '33698895', 'Gómez Ivaldi', 'Nicolás Alejandro', NULL, NULL, 'ST', NULL, 1, '1988-03-29', 'Soy uno de los creadores de esta WebApi AAABX.', 10, 'nicoivaldi@agora.com', '$2y$10$s7qgNXF4GgL7hItTQ6NyXuom1w9BHxv362pql/YJeYmN8qYwzEF0m', '2024-07-07 18:38:07', NULL),
(2, '33286958', 'Sosa Leonetti', 'Cristian Javier', NULL, NULL, 'UG', NULL, 2, '1988-02-08', NULL, 0, 'sleonetti@gmail.com', '$2y$10$HjR2rlPfne0GyNXGJ41jU.EiCfvVpMpQ5cOvRbitoynkYeMaEGnM.', '2024-07-08 14:42:43', NULL),
(3, '29741295', 'Galíndez', 'Gustavo', 'Tasaciones Galíndez Jumbo SH', '30708772964', 'UT', '123456', 3, '1984-01-01', 'Tasamos el valor de sus afectos al mejor precio de Mercado.', 50, 'gusgalindez@tasgalindez.com', '$2y$10$U9YS.OMtjMhnOzAUWovHv.uQo38bb3dva9qDUmq48w5fBZW0NVsyq', '2024-07-10 16:18:01', NULL),
(4, '27965368', 'Rolón', 'Karina', 'Paraíso Antigüedades SA', '30123456781', 'UA', '95874L', 4, '1982-06-12', 'Compra y Venta de antigüedades. Tasamos.', 60, 'krolon@paraiso.com', '$2y$10$Os4S45NKUqrBnLYDqALpYewr8CBMbKx8n4dNm9FTLhg7ySVxgcTx2', '2024-07-10 16:18:01', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuariotasadorhabilidad`
--

DROP TABLE IF EXISTS `usuariotasadorhabilidad`;
CREATE TABLE IF NOT EXISTS `usuariotasadorhabilidad` (
  `utsUsrId` int(10) UNSIGNED NOT NULL,
  `utsScatId` int(10) UNSIGNED NOT NULL,
  `utsPerId` int(10) UNSIGNED NOT NULL,
  `utsFechaInsert` datetime NOT NULL DEFAULT current_timestamp(),
  `utsFechaBaja` datetime DEFAULT NULL,
  PRIMARY KEY (`utsUsrId`,`utsScatId`,`utsPerId`),
  KEY `FK_UsrTasHab_Periodo` (`utsPerId`),
  KEY `FK_UsrTasHab_SubCat` (`utsScatId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuariotasadorhabilidad`
--

INSERT INTO `usuariotasadorhabilidad` (`utsUsrId`, `utsScatId`, `utsPerId`, `utsFechaInsert`, `utsFechaBaja`) VALUES
(3, 4, 2, '2024-07-10 17:26:24', NULL),
(3, 5, 1, '2024-07-10 17:26:24', NULL),
(4, 1, 2, '2024-07-10 17:28:11', NULL),
(4, 2, 1, '2024-07-10 17:28:11', NULL);

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `domicilio`
--
ALTER TABLE `domicilio`
  ADD CONSTRAINT `FK_domicilioLocalidad` FOREIGN KEY (`domLocalidad`) REFERENCES `localidad` (`locId`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `localidad`
--
ALTER TABLE `localidad`
  ADD CONSTRAINT `FK_localidadProv` FOREIGN KEY (`locProvID`) REFERENCES `provincia` (`provId`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `subcategoria`
--
ALTER TABLE `subcategoria`
  ADD CONSTRAINT `FK_SubcatCat` FOREIGN KEY (`scatCatId`) REFERENCES `categoria` (`catId`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `FK_usuarioDomicilio` FOREIGN KEY (`usrDomicilio`) REFERENCES `domicilio` (`domID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_usuarioTipoUsuario` FOREIGN KEY (`usrTipoUsuario`) REFERENCES `tipousuario` (`ttuTipoUsuario`) ON UPDATE CASCADE;

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
