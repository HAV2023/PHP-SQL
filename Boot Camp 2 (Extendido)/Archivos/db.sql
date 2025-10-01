-- Crear una base de datos llamada 'mi_base_datos'
CREATE DATABASE mi_base_datos;

-- Seleccionar la base de datos para trabajar con ella
USE mi_base_datos;

-- Crear la tabla 'users' para almacenar los usuarios registrados
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY, -- Identificador único para cada usuario (autoincremental)
    username VARCHAR(50) UNIQUE NOT NULL, -- Nombre de usuario, único y obligatorio (hasta 50 caracteres)
    password VARCHAR(255) NOT NULL -- Contraseña encriptada, obligatoria (hasta 255 caracteres para almacenar hash)
);
