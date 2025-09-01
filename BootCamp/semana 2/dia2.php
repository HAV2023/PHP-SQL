<?php
// Función para calcular el área de un triángulo.
// base y altura son parámetros de entrada.
function areaTriangulo($base, $altura) {
    return ($base * $altura) / 2;
}

// Mostramos el área calculada.
echo "Área del triángulo: " . areaTriangulo(10, 5);
?>