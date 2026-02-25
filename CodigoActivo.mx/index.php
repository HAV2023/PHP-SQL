<?php
$page_title = "Código Activo - Programación Bachillerato";
require_once 'includes/header.php';
?>

<!-- Hero Section -->
<section class="bg-dark text-white text-center py-5" 
         style="background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%); min-height: 80vh; display: flex; align-items: center;">
    <div class="container py-5">
        <!-- Logo principal -->
        <div class="mb-4 animate__animated animate__fadeInDown animate__slow">
            <img src="assets/img/logo.webp"
                 alt="Código Activo Logo"
                 class="img-fluid mx-auto d-block"
                 style="max-height: 220px; filter: drop-shadow(0 10px 20px rgba(0,0,0,0.5)); border-radius: 12px;"
                 loading="lazy">
        </div>

        <h1 class="display-3 fw-bold mb-3 animate__animated animate__fadeInDown">
            Código Activo
        </h1>
        <p class="lead fs-4 mb-4 animate__animated animate__fadeInUp animate__delay-1s">
            Tu portal privado para aprender programación web de forma activa y estructurada
        </p>
        <p class="mb-5 fs-5 animate__animated animate__fadeInUp animate__delay-2s">
            Recursos exclusivos, anuncios importantes, retos y materiales solo para alumnos del CBTis 52
        </p>

        <div class="d-flex flex-wrap justify-content-center gap-3">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php" class="btn btn-light btn-lg px-5 py-3 fw-bold animate__animated animate__pulse animate__infinite">
                    <i class="fas fa-door-open me-2"></i> Ir al Dashboard
                </a>
            <?php else: ?>
                <a href="login.php" class="btn btn-light btn-lg px-5 py-3 fw-bold shadow-lg animate__animated animate__pulse animate__infinite">
                    <i class="fas fa-sign-in-alt me-2"></i> Iniciar Sesión
                </a>

                <a href="register.php" class="btn btn-outline-light btn-lg px-5 py-3 fw-bold">
                    <i class="fas fa-user-plus me-2"></i> Registrarme como alumno
                </a>
            <?php endif; ?>
        </div>

        <?php if (!isset($_SESSION['user_id'])): ?>
            <p class="mt-4 text-white-75 animate__animated animate__fadeIn animate__delay-3s">
                Acceso exclusivo para alumnos del CBTis 52 con correo @cbtis52.edu.mx
            </p>
        <?php endif; ?>
    </div>
</section>

<!-- Sección ¿Qué encontrarás? -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">¿Qué encontrarás dentro del portal?</h2>
        <div class="row g-4 text-center">
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body py-5">
                        <i class="fas fa-bullhorn fa-4x text-primary mb-4"></i>
                        <h4>Anuncios importantes</h4>
                        <p class="text-muted">
                            Fechas de entrega, cambios de horario, tips y recordatorios del profesor.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body py-5">
                        <i class="fas fa-lock fa-4x text-success mb-4"></i>
                        <h4>Recursos exclusivos</h4>
                        <p class="text-muted">
                            Videos no listados, guías PDF, cheatsheets y enlaces Drive solo para alumnos.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body py-5">
                        <i class="fas fa-tasks fa-4x text-warning mb-4"></i>
                        <h4>Retos y proyectos</h4>
                        <p class="text-muted">
                            Actividades semanales, ejemplos resueltos y seguimiento de tu progreso en programación.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action final -->
<section class="bg-primary text-white text-center py-5">
    <div class="container py-4">
        <h3 class="mb-4">¿Listo para activar tu código y empezar a programar?</h3>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <div class="d-flex flex-wrap justify-content-center gap-3">
                <a href="login.php" class="btn btn-light btn-lg px-5 py-3 fw-bold">
                    <i class="fas fa-key me-2"></i> Iniciar Sesión
                </a>
                <a href="register.php" class="btn btn-outline-light btn-lg px-5 py-3 fw-bold">
                    <i class="fas fa-user-plus me-2"></i> Registrarme (solo CBTis 52)
                </a>
            </div>
        <?php else: ?>
            <a href="dashboard.php" class="btn btn-light btn-lg px-5 py-3 fw-bold">
                <i class="fas fa-tachometer-alt me-2"></i> Entrar al Dashboard
            </a>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>