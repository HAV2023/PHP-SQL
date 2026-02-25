<?php
/**
 * dashboard.php - Panel principal del usuario autenticado
 *
 * Muestra el dashboard personalizado del usuario después de iniciar sesión.
 * Incluye información del perfil, estadísticas, accesos rápidos, etc.
 *
 * @author    Hector Arciniega
 * @copyright 2026 Hector - Código Activo
 * @license   MIT
 * @version   1.0.0
 * @package   CodigoActivo
 * @category  Interfaz de usuario
 * @since     Febrero 2026
 */

declare(strict_types=1);

$page_title = "Dashboard";
require_once 'includes/header.php';

// Protección básica
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Zona horaria de Zamora, Michoacán
date_default_timezone_set('America/Mexico_City');

// Capturar hora exacta de ingreso (solo al cargar la página)
$horaIngreso = date('d \d\e F \d\e Y - H:i:s');

// Meses en español
$meses = [
    'January' => 'enero', 'February' => 'febrero', 'March' => 'marzo', 'April' => 'abril',
    'May' => 'mayo', 'June' => 'junio', 'July' => 'julio', 'August' => 'agosto',
    'September' => 'septiembre', 'October' => 'octubre', 'November' => 'noviembre', 'December' => 'diciembre'
];
$horaIngresoBonita = str_replace(array_keys($meses), array_values($meses), $horaIngreso);

try {
    $db = conectarDB();

    // Datos del usuario
    $stmt = $db->prepare("SELECT nombre, rol FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    $nombre = $user['nombre'] ?? 'Alumno';
    $rol    = $user['rol'] ?? 'alumno';

    $success = '';
    $error   = '';

    // Procesar nuevo anuncio (solo admin)
    if ($rol === 'admin' && $_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['agregar_recurso']) && !isset($_POST['editar_anuncio']) && !isset($_POST['editar_recurso'])) {
        $titulo     = trim($_POST['titulo'] ?? '');
        $contenido  = trim($_POST['contenido'] ?? '');
        $importante = isset($_POST['importante']) ? 1 : 0;

        if (empty($titulo) || empty($contenido)) {
            $error = 'Título y contenido son obligatorios.';
        } else {
            $stmt = $db->prepare("
                INSERT INTO anuncios (titulo, contenido, publicado_por, importante)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$titulo, $contenido, $_SESSION['user_id'], $importante]);
            $success = 'Anuncio publicado correctamente.';
        }
    }

    // Procesar nuevo recurso (solo admin)
    if ($rol === 'admin' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_recurso'])) {
        $titulo       = trim($_POST['recurso_titulo'] ?? '');
        $descripcion  = trim($_POST['recurso_descripcion'] ?? '');
        $url          = trim($_POST['recurso_url'] ?? '');
        $categoria    = $_POST['recurso_categoria'] ?? 'general';
        $nivel        = $_POST['recurso_nivel'] ?? 'principiante';
        $orden        = (int)($_POST['recurso_orden'] ?? 0);

        if (empty($titulo) || empty($url)) {
            $error = 'Título y URL son obligatorios para el recurso.';
        } elseif (!filter_var($url, FILTER_VALIDATE_URL)) {
            $error = 'La URL no parece válida.';
        } else {
            $stmt = $db->prepare("
                INSERT INTO recursos 
                (titulo, descripcion, url, categoria, nivel, orden, agregado_por)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$titulo, $descripcion, $url, $categoria, $nivel, $orden, $_SESSION['user_id']]);
            $success = 'Recurso agregado correctamente.';
        }
    }

    // Procesar eliminación (anuncio o recurso)
    if ($rol === 'admin' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar'])) {
        $tipo = $_POST['tipo'] ?? '';
        $id   = (int)($_POST['id'] ?? 0);

        if ($id > 0 && in_array($tipo, ['anuncio', 'recurso'])) {
            $tabla  = ($tipo === 'anuncio') ? 'anuncios' : 'recursos';
            $col_id = 'id';

            $stmt = $db->prepare("DELETE FROM $tabla WHERE $col_id = ?");
            $stmt->execute([$id]);
            $success = ucfirst($tipo) . ' eliminado correctamente.';
        } else {
            $error = 'Datos inválidos para eliminación.';
        }
    }

    // Procesar edición de anuncio
    if ($rol === 'admin' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_anuncio'])) {
        $id         = (int)($_POST['id'] ?? 0);
        $titulo     = trim($_POST['titulo'] ?? '');
        $contenido  = trim($_POST['contenido'] ?? '');
        $importante = isset($_POST['importante']) ? 1 : 0;

        if ($id > 0 && !empty($titulo) && !empty($contenido)) {
            $stmt = $db->prepare("
                UPDATE anuncios 
                SET titulo = ?, contenido = ?, importante = ?, fecha_publicacion = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$titulo, $contenido, $importante, $id]);
            $success = 'Anuncio actualizado correctamente.';
        } else {
            $error = 'Datos incompletos para actualizar anuncio.';
        }
    }

    // Procesar edición de recurso
    if ($rol === 'admin' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_recurso'])) {
        $id          = (int)($_POST['id'] ?? 0);
        $titulo      = trim($_POST['recurso_titulo'] ?? '');
        $descripcion = trim($_POST['recurso_descripcion'] ?? '');
        $url         = trim($_POST['recurso_url'] ?? '');
        $categoria   = $_POST['recurso_categoria'] ?? 'general';
        $nivel       = $_POST['recurso_nivel'] ?? 'principiante';
        $orden       = (int)($_POST['recurso_orden'] ?? 0);

        if ($id > 0 && !empty($titulo) && !empty($url) && filter_var($url, FILTER_VALIDATE_URL)) {
            $stmt = $db->prepare("
                UPDATE recursos 
                SET titulo = ?, descripcion = ?, url = ?, categoria = ?, nivel = ?, orden = ?, fecha_agregado = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$titulo, $descripcion, $url, $categoria, $nivel, $orden, $id]);
            $success = 'Recurso actualizado correctamente.';
        } else {
            $error = 'Datos incompletos o URL inválida para actualizar recurso.';
        }
    }

    // Cargar anuncios (últimos 5)
    $anuncios_stmt = $db->prepare("
        SELECT a.id, a.titulo, a.contenido, a.fecha_publicacion, a.importante,
               u.nombre AS autor
        FROM anuncios a
        LEFT JOIN usuarios u ON a.publicado_por = u.id
        ORDER BY a.fecha_publicacion DESC
        LIMIT 5
    ");
    $anuncios_stmt->execute();
    $anuncios = $anuncios_stmt->fetchAll();

    // Cargar recursos (hasta 20)
    $recursos_stmt = $db->prepare("
        SELECT r.id, r.titulo, r.descripcion, r.url, r.categoria, r.nivel, r.fecha_agregado, r.orden,
               u.nombre AS autor
        FROM recursos r
        LEFT JOIN usuarios u ON r.agregado_por = u.id
        ORDER BY r.orden ASC, r.fecha_agregado DESC
        LIMIT 20
    ");
    $recursos_stmt->execute();
    $recursos = $recursos_stmt->fetchAll();

} catch (Exception $e) {
    $error_db = "Error al cargar datos: " . $e->getMessage();
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-lg-8">

            <div class="alert alert-info">
                <h2 class="mb-0">Bienvenido, <?php echo htmlspecialchars($nombre); ?></h2>
                <p class="lead mt-2">
                    <?php if ($rol === 'admin'): ?>
                        Estás en modo profesor. Puedes publicar, editar y eliminar anuncios y recursos.
                    <?php else: ?>
                        Portal privado para alumnos de programación.
                    <?php endif; ?>
                </p>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($error_db)): ?>
                <div class="alert alert-danger"><?php echo $error_db; ?></div>
            <?php endif; ?>

            <!-- Formulario nuevo recurso (solo admin) -->
            <?php if ($rol === 'admin'): ?>
                <div class="card mb-5 border-primary shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Agregar nuevo recurso</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-9">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Título del recurso</label>
                                        <input type="text" name="recurso_titulo" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Orden</label>
                                        <input type="number" name="recurso_orden" class="form-control" min="0" value="0">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Descripción (opcional)</label>
                                <textarea name="recurso_descripcion" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">URL / Enlace directo</label>
                                <input type="url" name="recurso_url" class="form-control" required placeholder="https://...">
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Categoría</label>
                                        <select name="recurso_categoria" class="form-select">
                                            <option value="general" selected>General</option>
                                            <option value="html">HTML</option>
                                            <option value="css">CSS</option>
                                            <option value="js">JavaScript</option>
                                            <option value="php">PHP</option>
                                            <option value="sql">SQL</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Nivel</label>
                                        <select name="recurso_nivel" class="form-select">
                                            <option value="principiante" selected>Principiante</option>
                                            <option value="intermedio">Intermedio</option>
                                            <option value="avanzado">Avanzado</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" name="agregar_recurso" class="btn btn-primary">Agregar recurso</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Formulario nuevo anuncio (solo admin) -->
            <?php if ($rol === 'admin'): ?>
                <div class="card mb-5 shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Publicar nuevo anuncio</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Título del anuncio</label>
                                <input type="text" name="titulo" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Contenido</label>
                                <textarea name="contenido" class="form-control" rows="4" required></textarea>
                            </div>
                            <div class="form-check mb-4">
                                <input type="checkbox" name="importante" class="form-check-input" id="importante">
                                <label class="form-check-label" for="importante">Marcar como importante</label>
                            </div>
                            <button type="submit" class="btn btn-primary">Publicar anuncio</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Recursos exclusivos -->
            <h4 class="mb-4">Recursos exclusivos</h4>

            <?php if (empty($recursos)): ?>
                <div class="alert alert-light text-center py-4 border">
                    Aún no hay recursos agregados. <?php if ($rol === 'admin'): ?>¡Agrega el primero arriba!<?php endif; ?>
                </div>
            <?php else: ?>
                <?php foreach ($recursos as $recurso): ?>
                    <div class="card mb-3 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <h5 class="card-title text-primary mb-0">
                                    <?php echo htmlspecialchars($recurso['titulo']); ?>
                                </h5>
                                <?php if ($rol === 'admin'): ?>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-secondary" 
                                                data-bs-toggle="modal" data-bs-target="#editRecursoModal<?php echo $recurso['id']; ?>">
                                            <i class="fas fa-edit"></i> Editar
                                        </button>
                                        <form method="POST" style="display:inline;" 
                                              onsubmit="return confirm('¿Realmente quieres eliminar este recurso?');">
                                            <input type="hidden" name="tipo" value="recurso">
                                            <input type="hidden" name="id" value="<?php echo $recurso['id']; ?>">
                                            <button type="submit" name="eliminar" class="btn btn-outline-danger">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-2 mt-2">
                                <span class="badge bg-secondary me-1"><?php echo strtoupper($recurso['categoria']); ?></span>
                                <span class="badge bg-info text-dark"><?php echo ucfirst($recurso['nivel']); ?></span>
                            </div>

                            <?php if (!empty($recurso['descripcion'])): ?>
                                <p class="card-text text-muted mb-3"><?php echo nl2br(htmlspecialchars($recurso['descripcion'])); ?></p>
                            <?php endif; ?>

                            <a href="<?php echo htmlspecialchars($recurso['url']); ?>" 
                               class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener noreferrer">
                                Abrir recurso <i class="fas fa-external-link-alt ms-1"></i>
                            </a>

                            <div class="mt-3 small text-muted">
                                Agregado por <?php echo htmlspecialchars($recurso['autor'] ?? 'Profesor'); ?> • 
                                <?php echo date('d/m/Y H:i', strtotime($recurso['fecha_agregado'])); ?>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Editar Recurso -->
                    <?php if ($rol === 'admin'): ?>
                    <div class="modal fade" id="editRecursoModal<?php echo $recurso['id']; ?>" tabindex="-1" aria-labelledby="editRecursoLabel<?php echo $recurso['id']; ?>" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editRecursoLabel<?php echo $recurso['id']; ?>">Editar Recurso</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form method="POST">
                                    <div class="modal-body">
                                        <input type="hidden" name="id" value="<?php echo $recurso['id']; ?>">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Título</label>
                                            <input type="text" name="recurso_titulo" class="form-control" value="<?php echo htmlspecialchars($recurso['titulo']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Descripción</label>
                                            <textarea name="recurso_descripcion" class="form-control" rows="3"><?php echo htmlspecialchars($recurso['descripcion'] ?? ''); ?></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">URL</label>
                                            <input type="url" name="recurso_url" class="form-control" value="<?php echo htmlspecialchars($recurso['url']); ?>" required>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Categoría</label>
                                                    <select name="recurso_categoria" class="form-select">
                                                        <option value="general" <?php echo $recurso['categoria'] === 'general' ? 'selected' : ''; ?>>General</option>
                                                        <option value="html" <?php echo $recurso['categoria'] === 'html' ? 'selected' : ''; ?>>HTML</option>
                                                        <option value="css" <?php echo $recurso['categoria'] === 'css' ? 'selected' : ''; ?>>CSS</option>
                                                        <option value="js" <?php echo $recurso['categoria'] === 'js' ? 'selected' : ''; ?>>JavaScript</option>
                                                        <option value="php" <?php echo $recurso['categoria'] === 'php' ? 'selected' : ''; ?>>PHP</option>
                                                        <option value="sql" <?php echo $recurso['categoria'] === 'sql' ? 'selected' : ''; ?>>SQL</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Nivel</label>
                                                    <select name="recurso_nivel" class="form-select">
                                                        <option value="principiante" <?php echo $recurso['nivel'] === 'principiante' ? 'selected' : ''; ?>>Principiante</option>
                                                        <option value="intermedio" <?php echo $recurso['nivel'] === 'intermedio' ? 'selected' : ''; ?>>Intermedio</option>
                                                        <option value="avanzado" <?php echo $recurso['nivel'] === 'avanzado' ? 'selected' : ''; ?>>Avanzado</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Orden</label>
                                            <input type="number" name="recurso_orden" class="form-control" value="<?php echo $recurso['orden']; ?>" min="0">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" name="editar_recurso" class="btn btn-primary">Guardar cambios</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Anuncios recientes -->
            <h4 class="mb-3 mt-5">Anuncios recientes</h4>

            <?php if (empty($anuncios)): ?>
                <div class="alert alert-light text-center py-4">
                    Aún no hay anuncios publicados.
                </div>
            <?php else: ?>
                <?php foreach ($anuncios as $anuncio): ?>
                    <div class="card mb-3 <?php echo $anuncio['importante'] ? 'border-warning' : ''; ?>">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <h5 class="card-title">
                                    <?php echo htmlspecialchars($anuncio['titulo']); ?>
                                    <?php if ($anuncio['importante']): ?>
                                        <span class="badge bg-warning text-dark ms-2">Importante</span>
                                    <?php endif; ?>
                                </h5>
                                <?php if ($rol === 'admin'): ?>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-secondary" 
                                                data-bs-toggle="modal" data-bs-target="#editAnuncioModal<?php echo $anuncio['id']; ?>">
                                            <i class="fas fa-edit"></i> Editar
                                        </button>
                                        <form method="POST" style="display:inline;" 
                                              onsubmit="return confirm('¿Realmente quieres eliminar este anuncio?');">
                                            <input type="hidden" name="tipo" value="anuncio">
                                            <input type="hidden" name="id" value="<?php echo $anuncio['id']; ?>">
                                            <button type="submit" name="eliminar" class="btn btn-outline-danger">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <p class="card-text"><?php echo nl2br(htmlspecialchars($anuncio['contenido'])); ?></p>
                            <small class="text-muted">
                                Por <?php echo htmlspecialchars($anuncio['autor'] ?? 'Profesor'); ?> • 
                                <?php echo date('d/m/Y H:i', strtotime($anuncio['fecha_publicacion'])); ?>
                            </small>
                        </div>
                    </div>

                    <!-- Modal Editar Anuncio -->
                    <?php if ($rol === 'admin'): ?>
                    <div class="modal fade" id="editAnuncioModal<?php echo $anuncio['id']; ?>" tabindex="-1" aria-labelledby="editAnuncioLabel<?php echo $anuncio['id']; ?>" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editAnuncioLabel<?php echo $anuncio['id']; ?>">Editar Anuncio</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form method="POST">
                                    <div class="modal-body">
                                        <input type="hidden" name="id" value="<?php echo $anuncio['id']; ?>">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Título</label>
                                            <input type="text" name="titulo" class="form-control" value="<?php echo htmlspecialchars($anuncio['titulo']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Contenido</label>
                                            <textarea name="contenido" class="form-control" rows="5" required><?php echo htmlspecialchars($anuncio['contenido']); ?></textarea>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" name="importante" class="form-check-input" id="importanteEdit<?php echo $anuncio['id']; ?>" <?php echo $anuncio['importante'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="importanteEdit<?php echo $anuncio['id']; ?>">Marcar como importante</label>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" name="editar_anuncio" class="btn btn-primary">Guardar cambios</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5>Sesión actual</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><strong>Nombre:</strong> <?php echo htmlspecialchars($nombre); ?></li>
                        <li class="list-group-item"><strong>Rol:</strong> <?php echo ucfirst($rol); ?></li>
                        <li class="list-group-item">
                            <strong>Fecha y Hora de ingreso:</strong><br>
                            <span class="fw-bold text-danger">
                                <?php echo ucfirst($horaIngresoBonita); ?>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
