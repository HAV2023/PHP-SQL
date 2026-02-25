<?php
$page_title = "Registro de Usuario";
require_once 'includes/header.php';

// Cargar PHPMailer (vía Composer en vendor/)
require_once 'vendor/phpmailer/src/Exception.php';
require_once 'vendor/phpmailer/src/PHPMailer.php';
require_once 'vendor/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

                    // Generar token único para verificación (32 bytes → 64 caracteres hex)
                    $token = bin2hex(random_bytes(32));

                    // Insertar usuario con verificado = 0 y fecha_token = NOW()
                    $stmt = $db->prepare("
                        INSERT INTO usuarios 
                        (nombre, email, password, rol, activo, verificado, token_verificacion, fecha_token)
                        VALUES (?, ?, ?, 'alumno', 1, 0, ?, NOW())
                    ");
                    $stmt->execute([$nombre, $email, $hashed_password, $token]);

                    // Enviar correo de activación con IONOS
                    $mail = new PHPMailer(true);
                    try {
                        // Configuración SMTP IONOS México
                        $mail->isSMTP();
                        $mail->Host       = 'smtp.ionos.mx';
                        $mail->SMTPAuth   = true;
                        $mail->Username   = 'no-reply@codigoactivo.mx';
                        $mail->Password   = 'foRwur-divmum-4jomxa';  // ¡Cambia esto a config seguro en producción!
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port       = 587;
                        $mail->CharSet    = 'UTF-8';
                        $mail->setLanguage('es');  // Mensajes de error en español

                        // Para depurar temporalmente (cambia a 0 después de probar)
                        // $mail->SMTPDebug = 2;  // Muestra logs SMTP en pantalla (solo pruebas)

                        // Remitente y destinatario
                        $mail->setFrom('no-reply@codigoactivo.mx', 'Código Activo - Portal Educativo');
                        $mail->addAddress($email, $nombre);

                        $mail->isHTML(true);
                        $mail->Subject = 'Activa tu cuenta en Código Activo';

                        // Plantilla HTML mejorada
                        $activation_link = 'https://codigoactivo.mx/activar.php?token=' . urlencode($token);
                        $mail->Body = '
                        <!DOCTYPE html>
                        <html lang="es">
                        <head>
                            <meta charset="UTF-8">
                            <title>Activación de Cuenta</title>
                        </head>
                        <body style="font-family: Arial, Helvetica, sans-serif; margin:0; padding:0; background:#f4f4f4; color:#333;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f4f4f4; padding:20px;">
                                <tr>
                                    <td align="center">
                                        <table width="600" cellpadding="0" cellspacing="0" border="0" style="background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.1);">
                                            <tr>
                                                <td style="background:#007bff; color:white; padding:30px; text-align:center;">
                                                    <h1 style="margin:0; font-size:28px;">¡Bienvenido a Código Activo!</h1>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:40px 30px; text-align:center;">
                                                    <h2 style="margin-top:0;">Hola, ' . htmlspecialchars($nombre) . '</h2>
                                                    <p style="font-size:16px; line-height:1.6;">
                                                        Gracias por registrarte en el portal privado para alumnos del CBTis 52.<br>
                                                        Para activar tu cuenta y acceder al dashboard, haz clic en el botón abajo:
                                                    </p>
                                                    <p style="margin:40px 0;">
                                                        <a href="' . $activation_link . '" 
                                                           style="display:inline-block; background:#007bff; color:white; padding:16px 40px; text-decoration:none; border-radius:6px; font-size:18px; font-weight:bold;">
                                                            Activar mi cuenta ahora
                                                        </a>
                                                    </p>
                                                    <p style="font-size:14px; color:#555;">
                                                        Si el botón no funciona, copia y pega este enlace:<br>
                                                        <a href="' . $activation_link . '">' . $activation_link . '</a>
                                                    </p>
                                                    <p style="font-size:14px; color:#777; margin-top:30px;">
                                                        Este enlace expira en 48 horas por seguridad.
                                                    </p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="background:#f8f9fa; padding:20px; text-align:center; font-size:12px; color:#666;">
                                                    © ' . date('Y') . ' Código Activo - Portal Privado CBTis 52<br>
                                                    Si no solicitaste este registro, ignora este correo.
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </body>
                        </html>';

                        $mail->AltBody = "¡Hola, $nombre!\n\nActiva tu cuenta aquí: $activation_link\n\nEnlace válido por 48 horas.\n\nSi no solicitaste esto, ignora el mensaje.";

                        $mail->send();
                        $success = '¡Registro exitoso!<br>Te enviamos un correo de activación a <strong>' . htmlspecialchars($email) . '</strong>.<br>Revisa tu bandeja de entrada (y carpeta de spam) y haz clic en el enlace para activar tu cuenta. ¡Ya casi estás dentro!';
                    } catch (Exception $e) {
                        $error = "Registro realizado, pero no pudimos enviar el correo de activación.<br>Error: " . $mail->ErrorInfo . "<br>Contacta al profesor para ayuda.";
                        // Opcional: loggear el error
                        error_log("Error envío correo activación: " . $mail->ErrorInfo);
                    }
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