<?php
require_once "config.php";

$msg = null;      // texto del mensaje
$msg_type = null; // "ok" | "error"

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // 1) Limpiar entradas
  $nombre        = trim($_POST['nombre'] ?? '');
  $correo        = trim($_POST['correo'] ?? '');
  $nombre_grupo  = trim($_POST['nombre_grupo'] ?? '');
  $semestre      = trim($_POST['semestre'] ?? '');

  // 2) Validaciones básicas
  if ($nombre === '' || $correo === '' || $nombre_grupo === '' || $semestre === '') {
    $msg = "Completa todos los campos.";
    $msg_type = "error";
  } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $msg = "El correo no tiene un formato válido.";
    $msg_type = "error";
  } else {
    // 3) Verificar duplicado de correo
    $stmt = $conn->prepare("SELECT id FROM alumnos WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
      $msg = "Error: el correo ya está registrado.";
      $msg_type = "error";
    } else {
      // 4) Insertar en alumnos
      $stmtIns = $conn->prepare("INSERT INTO alumnos (nombre, correo) VALUES (?, ?)");
      $stmtIns->bind_param("ss", $nombre, $correo);
      if ($stmtIns->execute()) {
        $id_alumno = $stmtIns->insert_id;

        // 5) Insertar grupo; si falla, revertimos
        $stmtGrp = $conn->prepare("INSERT INTO grupos (id_alumno, nombre_grupo, semestre) VALUES (?, ?, ?)");
        $stmtGrp->bind_param("iss", $id_alumno, $nombre_grupo, $semestre);
        if ($stmtGrp->execute()) {
          $msg = "Registro agregado correctamente.";
          $msg_type = "ok";
        } else {
          // rollback mínimo
          $conn->query("DELETE FROM alumnos WHERE id = " . intval($id_alumno));
          $msg = "Ocurrió un problema al guardar el grupo.";
          $msg_type = "error";
        }
        $stmtGrp->close();
      } else {
        $msg = "No fue posible guardar el alumno.";
        $msg_type = "error";
      }
      $stmtIns->close();
    }
    $stmt->close();
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Registro de Alumnos</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>

  <main class="uc-wrapper">
    <!-- Tarjeta del formulario -->
    <section class="uc-card">
      <h2 class="uc-title">Registro de Alumnos</h2>

      <?php if ($msg): ?>
        <div class="msg <?= $msg_type === 'ok' ? 'ok' : 'error' ?>">
          <?= htmlspecialchars($msg) ?>
        </div>
      <?php endif; ?>

      <form class="uc-form" method="post" action="">
        <input type="text" name="nombre" placeholder="Nombre del alumno" required />
        <input type="email" name="correo" placeholder="Correo institucional" required />
        <input type="text" name="nombre_grupo" placeholder="Nombre del grupo (p. ej. A1)" required />
        <input type="text" name="semestre" placeholder="Semestre (p. ej. 3)" required />
        <button type="submit" class="uc-btn uc-btn--full">Registrar</button>
      </form>

      <div class="actions">
        <a href="join_view.php" class="uc-link">📄 Ver lista combinada (JOIN)</a>
      </div>
    </section>

    <!-- Tabla de registros -->
    <section class="uc-tablebox">
      <h3 class="uc-subtitle">Registros actuales</h3>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Alumno</th>
            <th>Correo</th>
            <th>Grupo</th>
            <th>Semestre</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $sql = "SELECT a.id, a.nombre, a.correo, g.nombre_grupo, g.semestre
                  FROM alumnos a
                  INNER JOIN grupos g ON a.id = g.id_alumno
                  ORDER BY a.id DESC";
          $rs = $conn->query($sql);
          if ($rs && $rs->num_rows > 0):
            while ($r = $rs->fetch_assoc()):
          ?>
            <tr>
              <td><?= htmlspecialchars($r['id']) ?></td>
              <td><?= htmlspecialchars($r['nombre']) ?></td>
              <td><?= htmlspecialchars($r['correo']) ?></td>
              <td><?= htmlspecialchars($r['nombre_grupo']) ?></td>
              <td><?= htmlspecialchars($r['semestre']) ?></td>
              <td>
                <a class="btn-edit" href="edit.php?id=<?= intval($r['id']) ?>">✏️ Editar</a>
                <a class="btn-delete" href="delete.php?id=<?= intval($r['id']) ?>" onclick="return confirm('¿Borrar este registro?');">🗑️ Borrar</a>
              </td>
            </tr>
          <?php
            endwhile;
          else:
            echo "<tr><td colspan='6'>No hay registros aún.</td></tr>";
          endif;
          ?>
        </tbody>
      </table>
    </section>
  </main>

</body>
</html>
