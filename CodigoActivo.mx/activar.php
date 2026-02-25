<?php
$page_title = "Activar Cuenta";
require_once 'includes/header.php';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    $message = '<div class="alert alert-danger alert-dismissible fade show text-center" role="alert">
                    <strong>Enlace inválido</strong><br>No se proporcionó un token de activación.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
} else {
    try {
        $db = conectarDB();

        // Consulta: token existe, no expirado (48 horas), y cuenta no verificada aún
        $stmt = $db->prepare("
            SELECT id 
            FROM usuarios
            WHERE token_verificacion = ?
              AND fecha_token > DATE_SUB(NOW(), INTERVAL 2 DAY)
              AND verificado = 0
        ");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Activar cuenta y limpiar token
            $stmt = $db->prepare("
                UPDATE usuarios
                SET verificado = 1,
                    token_verificacion = NULL,
                    fecha_token = NULL
                WHERE id = ?
            ");
            $stmt->execute([$user['id']]);

            $message = '<div class="alert alert-success alert-dismissible fade show text-center py-5" role="alert">
                            <h3><i class="fas fa-check-circle me-2"></i>¡Cuenta activada con éxito!</h3>
                            <p>Tu cuenta ya está lista. Ahora puedes iniciar sesión y acceder al portal.</p>
                            <a href="login.php" class="btn btn-primary btn-lg mt-3">
                                <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                            </a>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>';
        } else {
            // Podríamos diferenciar expirado vs inválido, pero por simplicidad un mensaje genérico
            $message = '<div class="alert alert-danger alert-dismissible fade show text-center py-5" role="alert">
                            <h4><i class="fas fa-exclamation-triangle me-2"></i>Enlace expirado o inválido</h4>
                            <p>El enlace ha caducado (válido por 48 horas) o no es correcto.<br>
                               Regístrate de nuevo o contacta al profesor para ayuda.</p>
                            <a href="register.php" class="btn btn-outline-primary mt-3">Volver a registrarme</a>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>';
        }
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Error en el sistema:</strong> ' . htmlspecialchars($e->getMessage()) . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
        error_log("Error en activar.php: " . $e->getMessage());
    }
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h4 class="mb-0">Activación de Cuenta - Código Activo</h4>
                </div>
                <div class="card-body p-5 text-center">
                    <?php echo $message ?? '<div class="alert alert-info">Procesando...</div>'; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>