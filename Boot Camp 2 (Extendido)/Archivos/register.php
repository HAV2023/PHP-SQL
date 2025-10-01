<?php
// Incluir el archivo de configuración para conectar a la base de datos
require 'config.php';

// Verificar si la solicitud HTTP es de tipo POST (es decir, si el formulario fue enviado)
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Obtener y limpiar el nombre de usuario ingresado en el formulario
    $username = trim($_POST['username']); // Se usa trim() para eliminar espacios en blanco innecesarios

    // Codificar la contraseña ingresada usando PASSWORD_DEFAULT para mayor seguridad
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        // Verificar si el usuario ya existe en la base de datos
        $sql = "SELECT id FROM users WHERE username = ?"; // Consulta para buscar el usuario por su nombre
        $stmt = $pdo->prepare($sql); // Preparar la consulta SQL
        $stmt->execute([$username]); // Ejecutar la consulta con el nombre de usuario ingresado
        $user = $stmt->fetch(PDO::FETCH_ASSOC); // Obtener el resultado como un array asociativo

        // Si el usuario ya existe, mostrar un mensaje de error y redirigir al formulario de registro
        if ($user) {
            echo "<script>alert('⚠️ El nombre de usuario ya está en uso. Prueba con otro.'); window.location.href='register.html';</script>";
            exit; // Detiene la ejecución del script
        }

        // Insertar el nuevo usuario en la base de datos si no existe
        $sql = "INSERT INTO users (username, password) VALUES (?, ?)"; // Consulta SQL para insertar un nuevo usuario
        $stmt = $pdo->prepare($sql); // Preparar la consulta

        // Ejecutar la consulta con los datos ingresados por el usuario
        if ($stmt->execute([$username, $password])) {
            // Redirigir al usuario a una página de registro exitoso
            header("Location: success_register.html");
            exit; // Detener la ejecución del script después de la redirección
        } else {
            // Mostrar un mensaje de error si la inserción falla
            echo "<script>alert('❌ Error al registrar usuario. Intenta de nuevo.'); window.location.href='register.html';</script>";
        }
    } catch (PDOException $e) {
        // Capturar errores de la base de datos y mostrar un mensaje de alerta
        echo "<script>alert('❌ Error de base de datos: " . $e->getMessage() . "'); window.location.href='register.html';</script>";
    }
}
?>
