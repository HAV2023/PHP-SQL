<?php
function dividir($a, $b) {
    if ($b == 0) {
        return "Error: división por cero.";
    }
    return $a / $b;
}
echo dividir(10, 0);
?>