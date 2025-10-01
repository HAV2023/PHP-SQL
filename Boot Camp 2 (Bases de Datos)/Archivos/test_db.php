<?php
// Incluir el archivo de configuración para conectar a la base de datos
require 'config.php';

try {
    // Ejecutar una consulta SQL para contar el número de usuarios en la base de datos
    $stmt = $pdo->query("SELECT COUNT(*) FROM users"); // Consulta para contar los registros en la tabla 'users'
    
    // Obtener el número total de usuarios
    $count = $stmt->fetchColumn(); // fetchColumn() devuelve el primer valor de la primera fila del resultado

    // Mostrar un mensaje de éxito con la cantidad de usuarios en la base de datos
    echo "✅ Conexión exitosa. Usuarios en la base de datos: " . $count;
} catch (PDOException $e) {
    // Capturar y mostrar un mensaje de error si la conexión a la base de datos falla
    echo "❌ Error de conexión: " . $e->getMessage();
}
?>

