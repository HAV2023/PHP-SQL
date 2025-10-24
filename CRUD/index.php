<?php
// ==========================================================================
// Archivo: index.php
// Propósito: Página principal del CRUD de alumnos. Permite crear y listar.
// Flujo:
//   1. Procesa inserciones (CREATE).
//   2. Lista todos los registros (READ).
// Dependencias: `config.php`, `edit.php`, `delete.php`.
// Seguridad: consultas preparadas + sanitización HTML + confirmación JS.
// ==========================================================================

// --------------------------------------------------------------------------
// 1. Incluir configuración de conexión PDO
// --------------------------------------------------------------------------
// Se carga el archivo de conexión a la base de datos (ver config.php).
// __DIR__ garantiza ruta absoluta, evitando errores por ubicaciones relativas.
require __DIR__ . '/config.php';

// --------------------------------------------------------------------------
// 2. Bloque CREATE – Inserción de nuevos alumnos
// --------------------------------------------------------------------------
// - Verifica si la solicitud proviene de un formulario POST.
// - Inserta los campos nombre y correo en la tabla `alumnos`.
// - Usa prepared statements para evitar inyección SQL.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $stmt = $pdo->prepare("INSERT INTO alumnos (nombre, correo) VALUES (?, ?)");
  $stmt->execute([$_POST['nombre'], $_POST['correo']]);
  
  // Redirige para evitar reenvío del formulario (PRG pattern).
  header("Location: index.php");
  exit;
}

// --------------------------------------------------------------------------
// 3. Bloque READ – Listado de registros
// --------------------------------------------------------------------------
// - Obtiene todos los alumnos ordenados del más reciente al más antiguo.
// - query() ejecuta directamente la consulta (sin parámetros).
// - fetchAll() devuelve un array de arrays asociativos.
$alumnos = $pdo->query("SELECT * FROM alumnos ORDER BY id DESC")->fetchAll();
?>
<!-- ==========================================================================
4. Interfaz HTML de la aplicación
=========================================================================== -->
<!doctype html>
<html lang="es">
<meta charset="utf-8">
<title>Alumnos – CRUD</title>
<body>
  <!-- ---------------------------------------------------------------------- -->
  <!-- 5. Encabezado principal -->
  <!-- ---------------------------------------------------------------------- -->
  <h1>Alumnos</h1>

  <!-- ---------------------------------------------------------------------- -->
  <!-- 6. Formulario de creación de nuevos registros -->
  <!-- ---------------------------------------------------------------------- -->
  <!-- method="post" activa el bloque CREATE del script PHP superior -->
  <form method="post" style="margin-bottom:1rem">
    <!-- Campo nombre -->
    <input name="nombre" placeholder="Nombre" required>
    <!-- Campo correo -->
    <input name="correo" type="email" placeholder="Correo" required>
    <!-- Botón de envío -->
    <button>Agregar</button>
  </form>

  <!-- ---------------------------------------------------------------------- -->
  <!-- 7. Tabla de listado de alumnos -->
  <!-- ---------------------------------------------------------------------- -->
  <!-- border y cellpadding solo a modo demostrativo; ideal usar CSS -->
  <table border="1" cellpadding="6">
    <tr>
      <th>ID</th>
      <th>Nombre</th>
      <th>Correo</th>
      <th>Acciones</th>
    </tr>

    <!-- -------------------------------------------------------------------- -->
    <!-- 8. Recorrido del array $alumnos -->
    <!-- -------------------------------------------------------------------- -->
    <?php foreach ($alumnos as $a): ?>
      <tr>
        <!-- htmlspecialchars() evita vulnerabilidades XSS -->
        <td><?= htmlspecialchars($a['id']) ?></td>
        <td><?= htmlspecialchars($a['nombre']) ?></td>
        <td><?= htmlspecialchars($a['correo']) ?></td>
        <td>
          <!-- Enlaces de acción: editar y eliminar -->
          <a href="edit.php?id=<?= $a['id'] ?>">Editar</a> |
          <!-- confirm() pide validación del usuario antes de borrar -->
          <a href="delete.php?id=<?= $a['id'] ?>" onclick="return confirm('¿Eliminar?')">Eliminar</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</body>
</html>

