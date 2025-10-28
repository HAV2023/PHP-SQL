/* ==========================================================
   BASE DE DATOS: registro_alumnos
   Estructura SQL para las tablas del sistema CRUD educativo.
   ========================================================== */

/* ----------------------------------------------------------
   Crear la base de datos y seleccionarla.
---------------------------------------------------------- */

CREATE DATABASE IF NOT EXISTS registro_alumnos
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
/* utf8mb4 garantiza que los acentos, emojis y símbolos se guarden bien. */

USE registro_alumnos;


/* ----------------------------------------------------------
   Crear la tabla principal: ALUMNOS
   Esta tabla guarda los datos personales básicos.
---------------------------------------------------------- */

CREATE TABLE alumnos (
  id INT AUTO_INCREMENT PRIMARY KEY,  /* Identificador único autoincremental. */
  nombre VARCHAR(100) NOT NULL,        /* Nombre completo del alumno. */
  correo VARCHAR(100) NOT NULL UNIQUE  /* Correo institucional. */
);
/* UNIQUE evita que dos alumnos tengan el mismo correo. */


/* ----------------------------------------------------------
   Crear la tabla secundaria: GRUPOS
   Esta tabla se relaciona 1:1 con ALUMNOS.
---------------------------------------------------------- */

CREATE TABLE grupos (
  id INT AUTO_INCREMENT PRIMARY KEY,    /* Identificador interno del grupo. */
  id_alumno INT NOT NULL,               /* Relación con el alumno. */
  nombre_grupo VARCHAR(50) NOT NULL,    /* Nombre del grupo (ej. A1, B2, etc.). */
  semestre VARCHAR(10) NOT NULL,        /* Semestre actual (ej. 3, 4, 5, etc.). */

  /* 🔗 Clave foránea: conecta grupo con alumno. */
  FOREIGN KEY (id_alumno) REFERENCES alumnos(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);
/*
ON DELETE CASCADE → si se borra un alumno, también se borra su grupo.
ON UPDATE CASCADE → si el ID del alumno cambia, también se actualiza en grupos.
*/


/* ----------------------------------------------------------
   Verificar estructura (consulta de ejemplo)
---------------------------------------------------------- */

SHOW TABLES;           /* Muestra las tablas creadas. */
DESCRIBE alumnos;      /* Muestra columnas y tipos de la tabla alumnos. */
DESCRIBE grupos;       /* Muestra columnas y tipos de la tabla grupos. */


/* ----------------------------------------------------------
   Insertar ejemplos para probar el sistema
---------------------------------------------------------- */

INSERT INTO alumnos (nombre, correo)
VALUES 
  ('Héctor Arciniega', 'hector@cbtis52.edu.mx'),
  ('María López', 'maria.lopez@cbtis52.edu.mx'),
  ('Juan Torres', 'juan.torres@cbtis52.edu.mx');

INSERT INTO grupos (id_alumno, nombre_grupo, semestre)
VALUES
  (1, 'A1', '3'),
  (2, 'B2', '4'),
  (3, 'A2', '2');


/* ----------------------------------------------------------
   Ejemplo de consulta INNER JOIN (la misma que usa join_view.php)
---------------------------------------------------------- */

SELECT 
  a.id,
  a.nombre,
  a.correo,
  g.nombre_grupo,
  g.semestre
FROM alumnos a
INNER JOIN grupos g ON a.id = g.id_alumno
ORDER BY a.id DESC;
