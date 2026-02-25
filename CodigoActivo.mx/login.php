<?php
$page_title = "Iniciar Sesión";
require_once 'includes/header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Todos los campos son obligatorios.';
    } else {
        try {
            $db = conectarDB();

            // Incluimos 'verificado' en la consulta
            $stmt = $db->prepare("
                SELECT id, nombre, password, rol, activo, verificado 
                FROM usuarios 
                WHERE email = ?
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                if ($user['activo'] != 1) {
                    $error = 'Tu cuenta está inactiva. Contacta al profesor.';
                } elseif ($user['verificado'] != 1) {
                    $error = 'Tu cuenta aún no está activada.<br>Revisa tu correo institucional (' . htmlspecialchars($email) . ') y haz clic en el enlace de activación.<br>(Revisa también la carpeta de spam o correo no deseado).';
                } elseif (password_verify($password, $user['password'])) {
                    // Login exitoso
                    session_regenerate_id(true); // Seguridad extra: regenera ID de sesión

                    $_SESSION['user_id']   = $user['id'];
                    $_SESSION['user_name'] = $user['nombre'];
                    $_SESSION['user_role'] = $user['rol'];

                    // Actualizar último acceso
                    $db->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?")
                       ->execute([$user['id']]);

                    header("Location: " . BASE_URL . "dashboard.php");
                    exit;
                } else {
                    $error = 'Contraseña incorrecta.';
                }
            } else {
                $error = 'Correo no encontrado.';
            }
        } catch (Exception $e) {
            $error = 'Error en el sistema. Intenta más tarde.';
            error_log("Error login: " . $e->getMessage());
        }
    }
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h4 class="mb-0"><i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión</h4>
                </div>
                <div class="card-body p-4 p-md-5">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" novalidate>
                        <div class="mb-4">
                            <label for="email" class="form-label fw-bold">Correo electrónico</label>
                            <input type="email" class="form-control form-control-lg" id="email" name="email" required
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                   placeholder="tu.matricula@cbtis52.edu.mx">
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label fw-bold">Contraseña</label>
                            <input type="password" class="form-control form-control-lg" id="password" name="password" required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i> Entrar al Portal
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <small class="text-muted">Solo para alumnos y profesor autorizados del CBTis 52</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>