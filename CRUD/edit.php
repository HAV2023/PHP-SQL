<?php require __DIR__ . '/config.php';

$id = (int)($_GET['id'] ?? 0);
$alumno = $pdo->prepare("SELECT * FROM alumnos WHERE id=?");
$alumno->execute([$id]);
$alumno = $alumno->fetch();
if (!$alumno) exit("No existe.");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $stmt = $pdo->prepare("UPDATE alumnos SET nombre=?, correo=? WHERE id=?");
  $stmt->execute([$_POST['nombre'], $_POST['correo'], $id]);
  header("Location: index.php"); exit;
}
?>
<!doctype html>
<html lang="es">
<meta charset="utf-8">
<title>Editar alumno</title>
<body>
  <h1>Editar alumno #<?= htmlspecialchars($alumno['id']) ?></h1>
  <form method="post">
    <input name="nombre" value="<?= htmlspecialchars($alumno['nombre']) ?>" required>
    <input name="correo" type="email" value="<?= htmlspecialchars($alumno['correo']) ?>" required>
    <button>Guardar</button>
    <a href="index.php">Cancelar</a>
  </form>
</body>
</html>
