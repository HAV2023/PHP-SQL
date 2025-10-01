<?php
/**
 * Nivel B – Ejercicio 1 (Condicional)
 * Tarifa eléctrica simple por tramos
 *
 * Entrada: kwh (entero ≥ 0)
 * Tarifas:
 *  - 0..150  → $0.80/kWh
 *  - 151..300→ $1.00/kWh
 *  - >300    → $1.20/kWh
 * Salida: kwh, tarifa_aplicada y costo_total (2 decimales).
 */

declare(strict_types=1);

function validar_kwh(?string $raw): array {
    // 1) Verificar que haya dato
    if ($raw === null || $raw === '') {
        return ['ok'=>false, 'msg'=>'Dato vacío.'];
    }
    // 2) Debe ser entero no negativo (sin decimales)
    if (!preg_match('/^\d+$/', trim($raw))) {
        return ['ok'=>false, 'msg'=>'Debe ser entero no negativo.'];
    }
    $val = (int)$raw;
    // 3) Rango válido
    if ($val < 0) {
        return ['ok'=>false, 'msg'=>'No puede ser negativo.'];
    }
    return ['ok'=>true, 'val'=>$val];
}

$kwh = null;
$tarifa = null;
$costo = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $res = validar_kwh($_POST['kwh'] ?? null);
    if (!$res['ok']) {
        $error = $res['msg'];
    } else {
        $kwh = (int)$res['val'];

        // Determinar tarifa según tramo (bordes 150 y 300 incluidos donde corresponde).
        if ($kwh <= 150) {
            $tarifa = 0.80;
        } elseif ($kwh <= 300) {
            $tarifa = 1.00;
        } else {
            $tarifa = 1.20;
        }

        // Costo total = kwh * tarifa
        $costo = $kwh * $tarifa;
    }
}

function fmt($n){ return number_format((float)$n, 2, '.', ','); }
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>NB-B1 Tarifa eléctrica</title>
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
  <h1>NB-B1 – Tarifa eléctrica simple</h1>
  <form method="post" novalidate>
    <fieldset>
      <legend>Entrada</legend>
      <label for="kwh">kWh consumidos (entero ≥ 0)</label>
      <input type="number" id="kwh" name="kwh" min="0" step="1" value="<?php echo htmlspecialchars($_POST['kwh'] ?? ''); ?>" />
      <button type="submit">Calcular</button>
      <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    </fieldset>
  </form>

  <?php if ($kwh !== null && $tarifa !== null): ?>
  <div class="result">
    <strong>Salida:</strong>
    <div>kWh: <code><?php echo $kwh; ?></code></div>
    <div>Tarifa aplicada: $<code><?php echo fmt($tarifa); ?></code> por kWh</div>
    <div>Costo total: $<code><?php echo fmt($costo); ?></code></div>
  </div>
  <?php endif; ?>
</body>
</html>

