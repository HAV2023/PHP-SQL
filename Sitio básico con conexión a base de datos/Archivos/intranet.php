<?php
session_start();
<?php
// Iniciar la sesión para acceder a las variables de sesión
session_start();

// Verificar si la sesión 'user' no está definida (el usuario no ha iniciado sesión)
if (!isset($_SESSION['user'])) {
    // Si el usuario no está autenticado, redirigirlo a la página de inicio de sesión
    header("Location: login.html");
    exit; // Se usa exit para detener la ejecución del script después de la redirección
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Definir el tipo de documento como HTML5 -->
    <meta charset="UTF-8"> <!-- Establece la codificación para admitir caracteres especiales -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Hace que la página sea adaptable a distintos dispositivos -->
    <title>Zona Restringida - Intranet</title> <!-- Título de la página -->
    <link rel="stylesheet" href="styles.css"> <!-- Enlace a la hoja de estilos externa -->
</head>
<body>
    <!-- Barra de navegación -->
    <nav class="menu">
        <ul class="menu-links">
            <li><a href="../intranet/index.html">Inicio</a></li> <!-- Enlace a la página de inicio -->
            <li><a href="../intranet/logout.php">Cerrar Sesión</a></li> <!-- Enlace para cerrar sesión -->
        </ul>
    </nav>

    <!-- Contenido principal -->
    <div class="container">
        <h2>Bienvenido a la Intranet</h2> <!-- Encabezado de bienvenida -->
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque ac lacus at risus feugiat fermentum.
        Sed sit amet dolor sed elit feugiat interdum nec nec arcu.</p> <!-- Texto de ejemplo -->
    </div>
</body>
</html>
