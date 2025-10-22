-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 02-10-2025 a las 22:45:02
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
-- Base de datos: `señapp`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ejercicio`
--

CREATE TABLE `ejercicio` (
  `id_ej` int(255) NOT NULL,
  `nivel` int(255) NOT NULL,
  `rtaAcorrect` varchar(155) NOT NULL,
  `rtaB` varchar(155) NOT NULL,
  `rtaC` varchar(155) NOT NULL,
  `rtaD` varchar(155) NOT NULL,
  `video` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `estado_completado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ejercicio`
--

INSERT INTO `ejercicio` (`id_ej`, `nivel`, `rtaAcorrect`, `rtaB`, `rtaC`, `rtaD`, `video`, `type`, `estado_completado`) VALUES
(1, 1, 'A', 'B', 'C', 'D', 'SeñaA.gif', 'Elegir', 0),
(17, 1, 'hola', '', '', '', 'SeñaHola.gif', 'Escribir', 0),
(18, 2, 'Cómo estás', 'Bien', 'Hola', '', '', 'Elegir', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `User_ID` int(11) NOT NULL,
  `User_Mail` varchar(100) NOT NULL,
  `User_Name` varchar(50) NOT NULL,
  `User_Pass` varchar(255) NOT NULL,
  `User_Lvl` int(11) DEFAULT 1,
  `User_Points` int(11) DEFAULT 0,
  `User_Progress` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`User_ID`, `User_Mail`, `User_Name`, `User_Pass`, `User_Lvl`, `User_Points`, `User_Progress`) VALUES
(1, 'KevinMc@gmail.com', 'Lolo', '$2y$10$bE9BB6mD3meOBOVIxaAQ2OhqstEdaBLgeNknsrXKpFNfCDsllIpDS', 1, 0, NULL),
(3, 'Grasita@gmail.com', 'Grasa', '$2y$10$.oZFFEEStGui6omclwLcR.Y.UdsSuV0n/abNdrachjkN/N7EegciS', 1, 0, NULL),
(4, 'Lololala@gmail.com', 'PEPE', '$2y$10$iQEL7nSJfM3qw9GRPKqVk.sZ0SSKExbzYo4587nGLpmr8rc.yQD2e', 1, 0, NULL),
(5, 'banana@gmail.com', 'Banana', '$2y$10$nyIRQDR4JhYIeD2NDjIFt.kydJ6k96q3qX4KwZ3jY9gXibv8S2QJa', 1, 0, NULL),
(6, 'gus@g.com', 'Gusta', '$2y$10$pRYWu0.WUyKc4d0V8jOeJ.EbvfNG004mFKNUk0Ul3TdsMKd/aRqp6', 1, 0, NULL),
(7, 'KevinMc@d', 'Lolo', '$2y$10$4WouJfZlpsODf7vD363/T.LbiFmAA8lxmfqEYUEcHTP86333Wk9Ci', 1, 0, NULL),
(9, 'test@test.com', 'Test User', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 0, NULL),
(10, 'KevinMc@dd', 'Banana', '$2y$10$PdFa5jioeuWiBKnfwa5AXuOJNFkl0FJTuz3OdIVdz2b3AfdZg5KCK', 1, 0, NULL),
(11, 'gus2@g.com', 'Barcos', '$2y$10$WOmMchguVic/h2JIBiZ.e.i96I8vK75LXB8ciY6MPA7W/81kpdQUO', 1, 0, NULL),
(13, 'test@test.co', 'Test User', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 0, NULL),
(14, 'gus@b.com', 'Gustavo', '$2y$10$v1uw0QUEvNaF/dZVnQPpk.XF54cCRxXs.pWx1QWTalCzVp6N8Xx72', 1, 0, NULL),
(15, 'lolo@lala.com', 'Lolo', '$2y$10$YPQbOtCUAXlG6Drwz0HRG.clWOQL9qk8Gnhh1t0Bxp7xvLxYx0PY.', 2, 0, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `ejercicio`
--
ALTER TABLE `ejercicio`
  ADD PRIMARY KEY (`id_ej`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`User_ID`),
  ADD UNIQUE KEY `User_Mail` (`User_Mail`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `ejercicio`
--
ALTER TABLE `ejercicio`
  MODIFY `id_ej` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `User_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
