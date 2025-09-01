<?php
// Cadena de texto a analizar.
$palabra = "programacion";

// strlen: longitud de la cadena.
// strtoupper: convierte a mayúsculas.
// rand(a,b): número aleatorio entre a y b.
echo "Longitud: " . strlen($palabra) . "<br>";
echo "Mayúsculas: " . strtoupper($palabra) . "<br>";
echo "Aleatorio entre 1 y 10: " . rand(1,10);
?>