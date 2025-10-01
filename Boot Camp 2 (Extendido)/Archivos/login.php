<?php
// Incluir el archivo de configuración (donde se define la conexión a la base de datos)
require 'config.php';

// Iniciar la sesión para manejar la autenticación del usuario
session_start();

// Verificar si la solicitud HTTP es de tipo POST (se asegura de que los datos vengan del formulario)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Obtener los datos enviados desde el formulario de inicio de sesión
    $username = $_POST['username']; // Captura el nombre de usuario
    $password = $_POST['password']; // Captura la contraseña ingresada por el usuario

    // Consulta SQL para buscar al usuario en la base de datos
    $sql = "SELECT * FROM users WHERE username = ?"; // Se usa un marcador `?` para evitar inyección SQL
    $stmt = $pdo->prepare($sql); // Prepara la consulta SQL para su ejecución segura
    $stmt->execute([$username]); // Ejecuta la consulta con el nombre de usuario ingresado
    $user = $stmt->fetch(PDO::FETCH_ASSOC); // Obtiene los datos del usuario como un array asociativo

    // Verificar si el usuario existe y si la contraseña ingresada coincide con la almacenada
    if ($user && password_verify($password, $user['password'])) { 
        // Si la contraseña es correcta, se guarda el nombre de usuario en la sesión
        $_SESSION['user'] = $user['username']; 

        // Redirigir al usuario a la intranet después de iniciar sesión
        header("Location: intranet.php");
        exit; // Se detiene la ejecución del script después de la redirección
    } else {
        // Si la autenticación falla, se muestra un mensaje de error con una alerta en JavaScript
        echo "<script>alert('Usuario o contraseña incorrectos'); window.location.href='login.html';</script>";
    }
}
?>
