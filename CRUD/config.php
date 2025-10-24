<?php
// ==========================================================================
// Archivo: config.php
// Propósito: Establecer conexión PDO segura y moderna a MySQL bajo entorno MAMP.
// Contexto: usado en entornos de desarrollo locales macOS con MAMP (puerto 8889).
// ==========================================================================

// --------------------------------------------------------------------------
// Configuración base de conexión
// --------------------------------------------------------------------------
$DB_NAME = 'crud';   // Nombre de la base de datos que contiene las tablas de la app (ej. alumnos).
$DB_USER = 'root';   // Usuario predeterminado de MySQL en MAMP (por defecto: root).
$DB_PASS = 'root';   // Contraseña por defecto de MySQL en MAMP (también root).
                     // ⚠️ En entornos productivos, JAMÁS usar credenciales por defecto.

// --------------------------------------------------------------------------
// Opción A: Conexión mediante host y puerto
// --------------------------------------------------------------------------
// Esta es la forma más simple y directa de conectar en MAMP (usa TCP/IP).
// Puerto 8889 es el estándar en MAMP; si cambias la configuración en el panel,
// ajusta este valor.
$dsn = "mysql:host=127.0.0.1;port=8889;dbname={$DB_NAME};charset=utf8mb4";

// --------------------------------------------------------------------------
// Opción B: Conexión mediante socket UNIX
// --------------------------------------------------------------------------
// Esta variante suele ser más rápida (no pasa por TCP/IP), pero depende
// del path del socket. En MAMP, normalmente se encuentra en:
//   /Applications/MAMP/tmp/mysql/mysql.sock
// Se deja comentada como alternativa avanzada.
/*
$dsn = "mysql:unix_socket=/Applications/MAMP/tmp/mysql/mysql.sock;dbname={$DB_NAME};charset=utf8mb4";
*/

// --------------------------------------------------------------------------
// Opciones de PDO
// --------------------------------------------------------------------------
// Estas opciones fortalecen seguridad y consistencia de comportamiento.
// - ERRMODE_EXCEPTION: lanza excepciones ante errores SQL.
// - FETCH_ASSOC: devuelve los resultados como arrays asociativos (clave => valor).
// - EMULATE_PREPARES: false desactiva emulación de prepared statements,
//   obligando al uso real de consultas preparadas por MySQL (más seguras).
$options = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Manejo robusto de errores.
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // Resultados más legibles.
  PDO::ATTR_EMULATE_PREPARES   => false,                   // Previene inyección SQL.
];

// --------------------------------------------------------------------------
// Creación de la instancia PDO
// --------------------------------------------------------------------------
// Se encapsula en un bloque try/catch para capturar excepciones de conexión.
// Si falla, se interrumpe la ejecución con un mensaje controlado.
try {
  $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options); // Instancia del objeto PDO.
} catch (PDOException $e) {
  // Si hay error, se detiene la app mostrando la causa.
  // En producción conviene registrar el error en log y mostrar un mensaje genérico.
  exit("Error de conexión: " . $e->getMessage());
}

