<?php
require_once "config.php";

// Validar ID
if (!isset($_GET['id'])) {
  die("ID de alumno no especificado.");
}

$id = intval($_GET['id']);

// Obtener datos actuales
$query = $conn->prepare("SELECT a.id, a.nombre, a.correo, g.nombre_grupo, g.semestre
                         FROM alumnos a
                         INNER JOIN grupos g ON a.id = g.id_alumno
                         WHERE a.id = ?");
$query->bind_param("i", $id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
  die("Alumno no encontrado.");
}

$alumno = $result->fetch_assoc();

// Actualizar si se envía el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $nombre = trim($_POST['nombre']);
  $correo = trim($_POST['correo']);
  $grupo = trim($_POST['nombre_grupo']);
  $semestre = trim($_POST['semestre']);

  $stmtA = $conn->prepare("UPDATE alumnos SET nombre=?, correo=? WHERE id=?");
  $stmtA->bind_param("ssi", $nombre, $correo, $id);
  $stmtA->execute();

  $stmtG = $conn->prepare("UPDATE grupos SET nombre_grupo=?, semestre=? WHERE id_alumno=?");
  $stmtG->bind_param("ssi", $grupo, $semestre, $id);
  $stmtG->execute();

  header("Location: index.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editar alumno</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>

  <main class="uc-wrapper">
    <section class="uc-card">
      <h2 class="uc-title">Editar Alumno</h2>

      <form class="uc-form" method="POST">
        <label>Nombre:</label>
        <input type="text" name="nombre" value="<?= htmlspecialchars($alumno['nombre']) ?>" required>

        <label>Correo:</label>
        <input type="email" name="correo" value="<?= htmlspecialchars($alumno['correo']) ?>" required>

        <label>Grupo:</label>
        <input type="text" name="nombre_grupo" value="<?= htmlspecialchars($alumno['nombre_grupo']) ?>" required>

        <label>Semestre:</label>
        <input type="text" name="semestre" value="<?= htmlspecialchars($alumno['semestre']) ?>" required>

        <button type="submit" class="uc-btn uc-btn--full">Actualizar</button>
      </form>

      <div class="uc-actions">
        <a href="index.php" class="uc-btn uc-btn--secondary">← Volver a la lista</a>
      </div>
    </section>
  </main>

</body>
</html>
