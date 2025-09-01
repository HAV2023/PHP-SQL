<?php
// Guardamos tres calificaciones.
$matematicas = 8;
$ingles = 6;
$programacion = 9;

// Calculamos el promedio sumando y dividiendo entre 3.
$promedio = ($matematicas + $ingles + $programacion) / 3;

// Mostramos el promedio.
echo "Promedio: $promedio <br>";

// Si el promedio es >= 7, aprobó; si no, reprobó.
if ($promedio >= 7) {
    echo "Resultado: APROBADO";
} else {
    echo "Resultado: REPROBADO";
}
?>