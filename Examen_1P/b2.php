<?php
/**
 * Nivel B – Ejercicio 2 (Ciclo)
 * Suma de múltiplos de 3 o 5 entre 1 y N (inclusive).
 *
 * Entrada: N (entero ≥ 1)
 * Salida: lista 1..N marcando múltiplos con '*', y Suma final.
 */

declare(strict_types=1);

function validar_N(?string $raw): array {
    if ($raw === null || $raw === '') return ['ok'=>false, 'msg'=>'Dato vacío.'];
    if (!preg_match('/^\d+$/', trim($raw))) return ['ok'=>false, 'msg'=>'Debe ser entero ≥ 1.'];
    $val = (int)$raw;
    if ($val < 1) return ['ok'=>false, 'msg'=>'Debe ser ≥ 1.'];
    return ['ok'=>true, 'val'=>$val];
}

$N = null;
$lista = [];
$suma = 0;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $res = validar_N($_POST['N'] ?? null);
    if (!$res['ok']) {
        $error = $res['msg'];
    } else {
        $N = (int)$res['val'];
        // Recorrer de 1 a N y acumular múltiplos de 3 o 5
        for ($i=1; $i <= $N; $i++) {
            $esMultiplo = ($i % 3 === 0) || ($i % 5 === 0);
            $lista[] = [$i, $esMultiplo];
            if ($esMultiplo) {
                $suma += $i;
            }
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>NB-B2 Suma de múltiplos 3 o 5</title>
  <style>
    body{font-family:system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin:24px}
    fieldset{border:1px solid #ccc;padding:12px;border-radius:8px;max-width:520px}
    label{display:block;margin:8px 0 4px}
    input[type=number]{width:100%;padding:8px;border:1px solid #bbb;border-radius:6px}
    button{padding:8px 14px;border:0;border-radius:8px;background:#07a;color:#fff;cursor:pointer;margin-top:10px}
    .error{color:#b00020;font-weight:700}
    .result{margin-top:12px;padding:8px;border-left:4px solid #07a;background:#eef7ff}
    .nums{display:flex;flex-wrap:wrap;gap:8px;margin-top:8px}
    .box{padding:4px 8px;border:1px solid #ddd;border-radius:6px}
    .hit{background:#fffbcc;border-color:#ffe58f}
    code{background:#f7f7f7;padding:2px 4px;border-radius:4px}
  </style>
</head>
<body>
  <h1>NB-B2 – Suma de múltiplos de 3 o 5</h1>
  <form method="post" novalidate>
    <fieldset>
      <legend>Entrada</legend>
      <label for="N">N (entero ≥ 1)</label>
      <input type="number" id="N" name="N" min="1" step="1" value="<?php echo htmlspecialchars($_POST['N'] ?? ''); ?>" />
      <button type="submit">Procesar</button>
      <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    </fieldset>
  </form>

  <?php if ($N !== null): ?>
  <div class="result">
    <strong>Salida:</strong>
    <div class="nums">
      <?php foreach ($lista as [$num, $hit]): ?>
        <div class="box <?php echo $hit ? 'hit' : ''; ?>">
          <?php echo $num; ?><?php echo $hit ? '*' : ''; ?>
        </div>
      <?php endforeach; ?>
    </div>
    <div style="margin-top:10px;">Suma = <code><?php echo $suma; ?></code></div>
  </div>
  <?php endif; ?>
</body>
</html>
