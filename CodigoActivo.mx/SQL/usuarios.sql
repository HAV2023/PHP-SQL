CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('alumno','admin') NOT NULL DEFAULT 'alumno',
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp(),
  `ultimo_acceso` datetime DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `verificado` tinyint(1) NOT NULL DEFAULT 0,
  `token_verificacion` varchar(255) DEFAULT NULL,
  `fecha_token` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB 
DEFAULT CHARSET=utf8mb4 
COLLATE=utf8mb4_general_ci;