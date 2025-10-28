<?php
include 'config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Primero eliminamos el grupo (por relación)
    mysqli_query($conn, "DELETE FROM grupos WHERE id_alumno=$id");
    // Luego eliminamos el alumno
    mysqli_query($conn, "DELETE FROM alumnos WHERE id=$id");

    echo "<p style='color:red; text-align:center;'>🗑️ Registro eliminado correctamente.</p>";
    echo "<p style='text-align:center;'><a href='index.php'>← Volver a la lista</a></p>";
}
?>
