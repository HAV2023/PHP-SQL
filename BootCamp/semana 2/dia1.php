<?php
// Definimos una función que convierte °C a °F.
// Fórmula: F = C * 9/5 + 32
function celsiusAFahrenheit($celsius) {
    return ($celsius * 9/5) + 32;
}

// Llamamos a la función y mostramos el resultado.
echo "30°C = " . celsiusAFahrenheit(30) . "°F";
?>