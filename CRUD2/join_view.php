<?php
require_once "config.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Vista combinada: Alumnos y Grupos</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>

  <main class="uc-wrapper">
    <section class="uc-card">
      <h2 class="uc-title">Vista combinada – INNER JOIN (Alumnos + Grupos)</h2>
    </section>

    <section class="uc-tablebox">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Alumno</th>
            <th>Correo</th>
            <th>Grupo</th>
            <th>Semestre</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $query = "SELECT a.id, a.nombre, a.correo, g.nombre_grupo, g.semestre
                    FROM alumnos a
                    INNER JOIN grupos g ON a.id = g.id_alumno
                    ORDER BY a.id DESC";
          $result = $conn->query($query);

          if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
              echo '<tr>
                      <td>' . htmlspecialchars($row['id']) . '</td>
                      <td>' . htmlspecialchars($row['nombre']) . '</td>
                      <td>' . htmlspecialchars($row['correo']) . '</td>
                      <td>' . htmlspecialchars($row['nombre_grupo']) . '</td>
                      <td>' . htmlspecialchars($row['semestre']) . '</td>
                    </tr>';
            }
          } else {
            echo "<tr><td colspan='5'>No hay registros disponibles.</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </section>

    <div class="uc-actions">
      <a href="index.php" class="uc-btn">← Volver al registro</a>
    </div>
  </main>

</body>
</html>
