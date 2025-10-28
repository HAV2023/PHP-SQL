<?php
// Datos de conexión
$servidor = "localhost";
$usuario = "root";
$contrasena = "root"; // En MAMP suele ser "root"
$basedatos = "crud";

// Crear conexión
$conn = mysqli_connect($servidor, $usuario, $contrasena, $basedatos);

// Verificar conexión
if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}
?>
