CREATE TABLE `recursos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_general_ci DEFAULT NULL,
  `url` text COLLATE utf8mb4_general_ci NOT NULL,
  `categoria` enum('html','css','js','php','sql','general') COLLATE utf8mb4_general_ci NOT NULL,
  `nivel` enum('principiante','intermedio','avanzado') COLLATE utf8mb4_general_ci DEFAULT 'principiante',
  `fecha_agregado` datetime NOT NULL DEFAULT current_timestamp(),
  `agregado_por` int(11) NOT NULL,
  `orden` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `agregado_por` (`agregado_por`)
) ENGINE=InnoDB 
DEFAULT CHARSET=utf8mb4 
COLLATE=utf8mb4_general_ci;