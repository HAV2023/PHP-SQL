```sql
-- =========================================================
-- Archivo: alumnos.sql
-- Propósito: Definir la tabla `alumnos` con columnas básicas
--            para identificación, contacto y trazabilidad.
-- Motor/Charset: InnoDB + utf8mb4 para integridad referencial
--                y soporte completo de Unicode (incl. emojis).
-- Notas operativas:
--   - Ejecute antes: USE <base_de_datos>;
--   - Si despliegas múltiples veces, considera:
--       CREATE TABLE IF NOT EXISTS alumnos ( ... );
--   - Para cargas masivas, valida correos en la app/capa ETL.
-- =========================================================

CREATE TABLE alumnos (                           -- Inicio de la definición de tabla `alumnos`.
  id INT AUTO_INCREMENT PRIMARY KEY,             -- Clave primaria autoincremental. INT es suficiente para cientos de millones; usa BIGINT si prevés más.
  nombre VARCHAR(120) NOT NULL,                  -- Nombre completo del alumno (máx. 120 chars). NOT NULL evita registros “vacíos”.
  correo VARCHAR(160) NOT NULL UNIQUE,           -- Correo electrónico único (índice/constraint UNIQUE evita duplicados).
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP  -- Marca de tiempo de creación; asignada automáticamente por MySQL al insertar.
) ENGINE=InnoDB                                  -- InnoDB permite FK, transacciones y bloqueos a nivel de fila.
  DEFAULT CHARSET=utf8mb4;                       -- utf8mb4 garantiza Unicode pleno (acentos, símbolos, emojis).

