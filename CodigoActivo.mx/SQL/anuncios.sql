CREATE TABLE anuncios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    contenido TEXT NOT NULL,
    fecha_publicacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    publicado_por INT UNSIGNED NOT NULL,
    importante TINYINT(1) NOT NULL DEFAULT 0,
    KEY idx_publicado_por (publicado_por)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
