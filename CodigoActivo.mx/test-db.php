<?php
require_once 'includes/config.php';

header('Content-Type: text/html; charset=utf-8');
echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Prueba de Conexión BD</title>
    <style>body {font-family: sans-serif; padding: 40px; max-width: 800px; margin: auto;}</style>
</head>
<body>";

try {
    $db = conectarDB();
    echo "<h1 style='color: #2e7d32;'>¡Conexión exitosa!</h1>";
    echo "<p>Base de datos: <strong>" . htmlspecialchars(DB_NAME) . "</strong></p>";
    echo "<p>Usuario: <strong>" . htmlspecialchars(DB_USER) . "</strong></p>";
    echo "<p><small>Todo listo → podemos crear las tablas ahora.</small></p>";
} catch (Exception $e) {
    echo "<h1 style='color: #c62828;'>Error de conexión</h1>";
    echo "<pre style='background:#ffebee; padding:15px; border-radius:4px;'>" 
         . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<p>Posibles causas:</p><ul>";
    echo "<li>Contraseña incorrecta</li>";
    echo "<li>Usuario sin permisos en esa BD</li>";
    echo "<li>Nombre de BD mal escrito (revisa mayúsculas/minúsculas)</li>";
    echo "</ul>";
}

echo "</body></html>";