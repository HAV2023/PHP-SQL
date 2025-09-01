<?php
$c1 = $_POST['c1'];
$c2 = $_POST['c2'];
$c3 = $_POST['c3'];

if (!is_numeric($c1) || !is_numeric($c2) || !is_numeric($c3)) {
    echo "Error: todas las calificaciones deben ser números.";
    exit;
}

$c1 = (float)$c1;
$c2 = (float)$c2;
$c3 = (float)$c3;

$promedio = ($c1 + $c2 + $c3) / 3;

echo "Promedio: " . number_format($promedio, 2) . "<br>";

if ($promedio >= 7) {
    echo "Resultado: APROBADO";
} else {
    echo "Resultado: REPROBADO";
}
?>