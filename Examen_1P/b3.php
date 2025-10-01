<?php
/**
 * Nivel B – Ejercicio 3 (Función)
 * ¿Número capicúa?
 *
 * Entrada: num (entero ≥ 0)
 * Función: esCapicua(int $n): bool
 * Salida: "SI" si es capicúa, "NO" en caso contrario.
 */

declare(strict_types=1);

function validar_num(?string $raw): array {
    if ($raw === null || $raw === '') return ['ok'=>false,'msg'=>'Dato vacío.'];
    if (!preg_match('/^\d+$/', trim($raw))) return ['ok'=>false,'msg'=>'Debe ser entero ≥ 0.'];
    // El patrón anterior ya garantiza no-negativo
    return ['ok'=>true,'val'=>(int)$raw];
}

/**
 * Determina si un entero no negativo es capicúa (palíndromo).
 * Implementación usando string: compara con su reverso.
 * @param int $n
 * @return bool
 */
function esCapicua(int $n): bool {
    $s = (string)$n;
    $rev = strrev($s);
    return $s === $rev;
}

$num = null;
$resp = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $res = validar_num($_POST['num'] ?? null);
    if (!$res['ok']) {
        $error = $res['msg'];
    } else {
        $num = (int)$res['val'];
        $resp = esCapicua($num) ? 'SI' : 'NO';
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>NB-B3 Número capicúa</title>
  <style>
    body{font-family:system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin:24px}
    fieldset{border:1px solid #ccc;padding:12px;border-radius:8px;max-width:520px}
    label{display:block;margin:8px 0 4px}
    input[type=number]{width:100%;padding:8px;border:1px solid #bbb;border-radius:6px}
    button{padding:8px 14px;border:0;border-radius:8px;background:#07a;color:#fff;cursor:pointer;margin-top:10px}
    .error{color:#b00020;font-weight:700}
    .result{margin-top:12px;padding:8px;border-left:4px solid #07a;background:#eef7ff}
    code{background:#f7f7f7;padding:2px 4px;border-radius:4px}
  </style>
</head>
<body>
  <h1>NB-B3 – ¿Número capicúa?</h1>
  <form method="post" novalidate>
    <fieldset>
      <legend>Entrada</legend>
      <label for="num">Número entero ≥ 0</label>
      <input type="number" id="num" name="num" min="0" step="1" value="<?php echo htmlspecialchars($_POST['num'] ?? ''); ?>" />
      <button type="submit">Evaluar</button>
      <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    </fieldset>
  </form>

  <?php if ($resp !== null): ?>
  <div class="result">
    <strong>Salida:</strong>
    <div>¿Capicúa? <code><?php echo $resp; ?></code></div>
  </div>
  <?php endif; ?>
</body>
</html>
