<?php
/**
 * register.php - Formulario y lógica de registro de nuevos usuarios
 *
 * Muestra el formulario de registro, valida datos, crea el usuario
 * en la base de datos sin envío de email ni verificación por token.
 *
 * @author    Hector Arciniega
 * @copyright 2026 Hector - Código Activo
 * @license   MIT
 * @version   1.0.1
 * @package   CodigoActivo
 * @category  Autenticación
 * @since     Febrero 2026
 */

declare(strict_types=1);

$page_title = "Registro de Usuario";
require_once 'includes/header.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre     = trim($_POST['nombre'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $email2     = trim($_POST['email2'] ?? '');
    $password   = $_POST['password'] ?? '';
    $password2  = $_POST['password2'] ?? '';

    // 1. Validaciones básicas
    if (empty($nombre) || empty($email) || empty($email2) || empty($password) || empty($password2)) {
        $error = 'Todos los campos son obligatorios.';
    } elseif (strlen($nombre) < 3 || strlen($nombre) > 100) {
        $error = 'El nombre debe tener entre 3 y 100 caracteres.';
    } elseif (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\-]+$/u', $nombre)) {
        $error = 'El nombre solo puede contener letras, espacios y guiones (sin números ni símbolos).';
    } elseif ($email !== $email2) {
        $error = 'Los correos electrónicos no coinciden.';
    } elseif ($password !== $password2) {
        $error = 'Las contraseñas no coinciden.';
    } elseif (strlen($password) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El correo electrónico no es válido.';
    } elseif (strlen($email) > 120) {
        $error = 'El correo es demasiado largo (máximo 120 caracteres).';
    } else {
        // Normalizar correo
        $email = strtolower(trim($email));

        // Validación estricta del dominio CBTis 52
        if (substr($email, -15) !== '@cbtis52.edu.mx') {
            $error = 'Solo se permiten correos institucionales del CBTis 52 (@cbtis52.edu.mx).';
        } else {
            try {
                $db = conectarDB();

                // Verificar duplicado de correo
                $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $error = 'Este correo electrónico ya está registrado.';
                } else {
                    // Capitalizar nombre (queda más profesional)
                    $nombre = ucwords(strtolower($nombre));

                    // Hashear contraseña
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // Registro directo: sin token, sin envío de email y con cuenta verificada.
                    // Esto permite que el alumno pueda iniciar sesión inmediatamente después del registro.
                    $stmt = $db->prepare("
                        INSERT INTO usuarios 
                        (nombre, email, password, rol, activo, verificado, token_verificacion, fecha_token)
                        VALUES (?, ?, ?, 'alumno', 1, 1, '', NULL)
                    ");
                    $stmt->execute([$nombre, $email, $hashed_password]);

                    $success = '¡Registro exitoso!<br>El usuario <strong>' . htmlspecialchars($nombre) . '</strong> fue agregado correctamente a la base de datos.<br>Ya puede iniciar sesión con su correo institucional.';
                }
            } catch (PDOException $e) {
                $error = ($e->getCode() == 23000) ? 'Este correo ya está registrado.' : 'Error en la base de datos: ' . $e->getMessage();
            } catch (Exception $e) {
                $error = 'Error inesperado: ' . $e->getMessage();
            }
        }
    }
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h4 class="mb-0">Registro de Alumnos CBTis 52</h4>
                    <small>Portal privado - Solo personal autorizado</small>
                </div>

                <div class="card-body p-4 p-md-5">
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

                    <form method="POST" novalidate>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Nombre completo</label>
                            <input type="text" name="nombre" class="form-control form-control-lg"
                                   value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>"
                                   required maxlength="100"
                                   pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\-]+"
                                   title="Solo letras, espacios y guiones permitidos (sin números ni símbolos)"
                                   placeholder="Ejemplo: Juan Pérez López">
                            <div class="form-text text-muted mt-1">
                                <i class="fas fa-info-circle me-1"></i>
                                Solo letras, espacios y guiones (sin números ni símbolos especiales)
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Correo institucional</label>
                            <input type="email" name="email" class="form-control form-control-lg"
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                   required placeholder="tu.matricula@cbtis52.edu.mx">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Confirmar correo institucional</label>
                            <input type="email" name="email2" class="form-control form-control-lg"
                                   value="<?php echo htmlspecialchars($_POST['email2'] ?? ''); ?>"
                                   required placeholder="Repite tu correo @cbtis52.edu.mx">
                            <div class="form-text text-muted mt-1">
                                <i class="fas fa-info-circle me-1"></i>
                                Debe coincidir exactamente con el correo anterior
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold">Contraseña</label>
                                <input type="password" name="password" class="form-control form-control-lg" required minlength="8">
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold">Confirmar contraseña</label>
                                <input type="password" name="password2" class="form-control form-control-lg" required minlength="8">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-user-plus me-2"></i> Registrarme
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <p class="text-muted">¿Ya tienes cuenta?</p>
                        <a href="login.php" class="btn btn-outline-primary">Iniciar sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
