CREATE TABLE recursos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    descripcion TEXT,
    url TEXT NOT NULL,
    categoria ENUM('html','css','js','php','sql','general') NOT NULL,
    nivel ENUM('principiante','intermedio','avanzado') DEFAULT 'principiante',
    fecha_agregado DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    agregado_por INT UNSIGNED NOT NULL,
    orden INT DEFAULT 0,
    KEY idx_agregado_por (agregado_por)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
