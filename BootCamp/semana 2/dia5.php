<?php
// Arreglo de alumnos con claves asociativas (diccionario).
$alumnos = [
    ["nombre" => "Ana", "matricula" => "001", "promedio" => 9],
    ["nombre" => "Luis", "matricula" => "002", "promedio" => 7],
    ["nombre" => "Pedro", "matricula" => "003", "promedio" => 6]
];

// Comenzamos una tabla HTML con borde simple.
echo "<table border='1'>";
echo "<tr><th>Nombre</th><th>Matrícula</th><th>Promedio</th></tr>";

// Recorremos cada alumno y generamos una fila por cada uno.
foreach ($alumnos as $alumno) {
    echo "<tr>";
    echo "<td>{$alumno['nombre']}</td>";
    echo "<td>{$alumno['matricula']}</td>";
    echo "<td>{$alumno['promedio']}</td>";
    echo "</tr>";
}
echo "</table>";
?>