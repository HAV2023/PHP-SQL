<?php
// Iniciar la sesión para poder acceder a las variables de sesión existentes
session_start();

// Destruir la sesión actual, eliminando todas las variables de sesión almacenadas
session_destroy();

// Redirigir al usuario a la página de inicio (index.html) después de cerrar sesión
header("Location: index.html");

// Finalizar la ejecución del script para evitar que se ejecute código adicional después de la redirección
exit;
?>
