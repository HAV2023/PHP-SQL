<?php
// ==========================================================================
// Archivo: edit.php
// Propósito: Permitir la edición de un registro existente en la tabla `alumnos`.
// Flujo:
//   1. Carga de alumno por ID (GET).
//   2. Visualización del formulario con datos actuales.
//   3. Recepción de datos actualizados (POST) y actualización en BD.
// Dependencia: requiere `config.php` para la conexión PDO.
// Seguridad: uso de consultas preparadas + sanitización con htmlspecialchars().
// ==========================================================================

// --------------------------------------------------------------------------
// 1. Incluir configuración de conexión a la base de datos
// --------------------------------------------------------------------------
// __DIR__ asegura que se use la ruta absoluta del archivo actual, evitando errores
// al llamar este script desde otros directorios.
require __DIR__ . '/config.php';

// --------------------------------------------------------------------------
// 2. Capturar el parámetro ID y validar existencia del registro
// --------------------------------------------------------------------------
// - Se obtiene el ID desde la URL: edit.php?id=3.
// - Se usa (int) para forzar conversión a entero, evitando inyección por tipo.
// - Se prepara una consulta SELECT segura con marcador posicional (?).
$id = (int)($_GET['id'] ?? 0);
$alumno = $pdo->prepare("SELECT * FROM alumnos WHERE id=?");
$alumno->execute([$id]);
$alumno = $alumno->fetch(); // Devuelve array asociativo del registro.

// Si no existe el alumno, se corta la ejecución mostrando un mensaje controlado.
if (!$alumno) exit("No existe.");

// --------------------------------------------------------------------------
// 3. Procesar envío del formulario (POST)
// --------------------------------------------------------------------------
// - La variable global $_SERVER['REQUEST_METHOD'] indica el tipo de solicitud.
// - Si es POST, significa que el usuario envió el formulario.
// - Se ejecuta una actualización (UPDATE) segura con placeholders.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Preparar consulta de actualización de los campos `nombre` y `correo`.
  $stmt = $pdo->prepare("UPDATE alumnos SET nombre=?, correo=? WHERE id=?");
  // Ejecutar con los valores provenientes del formulario.
  $stmt->execute([$_POST['nombre'], $_POST['correo'], $id]);
  
  // Redirigir nuevamente a index.php una vez completada la actualización.
  header("Location: index.php");
  exit;
}
?>
<!-- ==========================================================================
4. Interfaz HTML para edición de datos
=========================================================================== -->
<!doctype html>
<html lang="es">
<meta charset="utf-8">
<title>Editar alumno</title>
<body>
  <!-- ---------------------------------------------------------------------- -->
  <!-- 5. Encabezado del formulario -->
  <!-- ---------------------------------------------------------------------- -->
  <h1>Editar alumno #<?= htmlspecialchars($alumno['id']) ?></h1>

  <!-- ---------------------------------------------------------------------- -->
  <!-- 6. Formulario de edición -->
  <!-- ---------------------------------------------------------------------- -->
  <!-- method="post" define el modo de envío. -->
  <form method="post">
    <!-- Campo: Nombre -->
    <!-- htmlspecialchars() evita inyección XSS mostrando texto plano. -->
    <input name="nombre" value="<?= htmlspecialchars($alumno['nombre']) ?>" required>

    <!-- Campo: Correo -->
    <!-- type="email" fuerza validación básica en el navegador. -->
    <input name="correo" type="email" value="<?= htmlspecialchars($alumno['correo']) ?>" required>

    <!-- Botón para guardar cambios -->
    <button>Guardar</button>
    <!-- Enlace para cancelar y volver a la lista -->
    <a href="index.php">Cancelar</a>
  </form>
</body>
</html>
