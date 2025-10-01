<?php
// Definir el nombre del servidor y el puerto donde se ejecuta MySQL
$host = 'localhost:3306'; // Puerto predeterminado 3306

// Nombre de la base de datos a la que se va a conectar
$dbname = 'mi_base_datos'; // Cambiar según la base de datos que se desea usar

// Usuario de la base de datos (por defecto en entornos locales suele ser 'root')
$user = 'root';

// Contraseña del usuario (en este caso, '1234' o nada según el caso, pero debe cambiarse en producción por seguridad)
$pass = '1234';

try {
    // Crear una nueva instancia de la clase PDO para la conexión con MySQL
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);

    // Establecer el modo de error de PDO para lanzar excepciones en caso de error
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Si la conexión es exitosa, no se muestra ningún mensaje, pero la conexión está establecida
} catch (PDOException $e) {
    // Captura cualquier error de conexión y detiene la ejecución del script mostrando el mensaje de error
    die("Error de conexión: " . $e->getMessage());
}
?>
