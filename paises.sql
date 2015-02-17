-- phpMyAdmin SQL Dump
-- version 4.1.12
-- http://www.phpmyadmin.net
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 18-01-2015 a las 19:24:16
-- Versión del servidor: 5.5.36
-- Versión de PHP: 5.4.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de datos: `paises`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ciudades`
--

CREATE TABLE IF NOT EXISTS `ciudades` (
  `id` int(2) NOT NULL,
  `ciudad` varchar(20) COLLATE utf8_spanish2_ci NOT NULL,
  `pais` varchar(20) COLLATE utf8_spanish2_ci NOT NULL,
  `habitantes` int(10) NOT NULL,
  `superficie` decimal(10,2) NOT NULL,
  `tieneMetro` tinyint(1) NOT NULL,
  `paproba` varchar(10) COLLATE utf8_spanish2_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `ciudades`
--

INSERT INTO `ciudades` (`id`, `ciudad`, `pais`, `habitantes`, `superficie`, `tieneMetro`, `paproba`) VALUES
(1, 'Valencia', 'España', 40000000, '504645.00', 1, ''),
(2, 'Mexico D.F.', 'Mexico', 555666, '234567.89', 1, ''),
(3, 'Marsala', 'Italia', 37563, '2456.36', 0, ''),
(4, 'Daca', 'Banglades', 6540000, '4563.21', 0, ''),
(5, 'New York', 'E.E.U.U', 8400000, '1204.40', 1, ''),
(6, 'Bolonia', 'Italia', 150000, '2456.32', 0, ''),
(7, 'Plodiv', 'Bulgaria', 338000, '102.00', 0, ''),
(8, 'Hong Kong ', 'China', 7188000, '1108.00', 1, ''),
(9, 'Valencia', 'Venezuela', 2044833, '1528.00', 0, '');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
