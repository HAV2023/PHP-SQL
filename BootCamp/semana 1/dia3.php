<?php
// Verificamos si un número es par o impar.
$numero = 7;

// El operador % obtiene el residuo de una división.
// Si el residuo de dividir entre 2 es 0, el número es par.
if ($numero % 2 == 0) {
    echo "El número $numero es PAR";
} else {
    echo "El número $numero es IMPAR";
}
?>