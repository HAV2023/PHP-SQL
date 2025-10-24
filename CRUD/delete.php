<?php
// ==========================================================================
// Archivo: delete.php
// Propósito: Eliminar un registro específico de la tabla `alumnos`
//             utilizando consultas preparadas seguras con PDO.
// Dependencia: requiere `config.php` para obtener la conexión PDO ($pdo).
// ==========================================================================

// --------------------------------------------------------------------------
// 1. Cargar la configuración de conexión PDO
// --------------------------------------------------------------------------
// __DIR__ obtiene el directorio actual del archivo en ejecución, garantizando
// que la ruta sea correcta incluso si se invoca desde otro punto del sistema.
require __DIR__ . '/config.php';

// --------------------------------------------------------------------------
// 2. Capturar el parámetro `id` recibido por GET y validarlo
// --------------------------------------------------------------------------
// - $_GET['id'] obtiene el valor pasado en la URL (ejemplo: delete.php?id=3).
// - El operador de fusión nula (??) devuelve 0 si 'id' no está definido.
// - (int) convierte el valor a entero para evitar inyección SQL por tipo.
// Importante: se usa cast explícito a int, pero también se valida en PDO.
$id = (int)($_GET['id'] ?? 0);

// --------------------------------------------------------------------------
// 3. Preparar la sentencia SQL segura
// --------------------------------------------------------------------------
// - Se usa marcador posicional (?) para insertar valores en consulta.
// - PDO::prepare previene inyección SQL.
// - La consulta borra solo el registro con el id exacto.
// - No se borra si $id = 0, salvo que exista un alumno con id=0 (raro pero posible).
$stmt = $pdo->prepare("DELETE FROM alumnos WHERE id=?");

// --------------------------------------------------------------------------
// 4. Ejecutar la sentencia con el parámetro
// --------------------------------------------------------------------------
// - El método execute() recibe un array de valores que sustituye los marcadores.
// - En este caso, solo hay un marcador (?) correspondiente a $id.
// - Si no existe el id, la ejecución no lanza error, simplemente no borra filas.
$stmt->execute([$id]);

// --------------------------------------------------------------------------
// 5. Redirigir al usuario al índice principal
// --------------------------------------------------------------------------
// - Una vez completado el borrado, se envía una cabecera HTTP Location
//   que redirige a `index.php`.
// - exit; detiene inmediatamente el script para evitar ejecución adicional.
header("Location: index.php");
exit;
