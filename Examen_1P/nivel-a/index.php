<?php
/**
 * Examen Parcial – Nivel A (Regular)
 * Mini‑sistema: Registro de pedidos con totales y filtro
 * Versión 100% en español (identificadores y comentarios).
 *
 * Flujo general:
 *  ┌────────────────────────────────────────────────────────────────────┐
 *  │ 1) Al cargar la página, se inicia sesión y se prepara el estado.  │
 *  │ 2) Si llega POST, se validan entradas, se calculan totales y      │
 *  │    se inserta el pedido en la sesión (máx. 10).                    │
 *  │ 3) Se lee el filtro GET (total mínimo) y se renderiza la tabla.    │
 *  └────────────────────────────────────────────────────────────────────┘
 */

declare(strict_types=1);         // Activa tipado estricto en PHP 7+
session_start();                 // Crea/continúa una sesión para usar $_SESSION

/* ──────────────────────────────────────────────────────────────────────
   Funciones de saneamiento y validación
   ────────────────────────────────────────────────────────────────────── */

/**
 * depurar_texto
 *  - Recorta espacios al inicio/fin y elimina caracteres de control no imprimibles.
 * @param string $texto   Cadena de entrada cruda del usuario.
 * @return string         Cadena saneada.
 */
function depurar_texto(string $texto): string {
    $texto = trim($texto);
    // Regex: rangos de caracteres de control ASCII, excepto \n y \t.
    // Reemplaza por vacío cualquier carácter no deseado.
    return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $texto) ?? '';
}

/**
 * a_flotante
 *  - Convierte una cadena a número flotante permitiendo coma decimal.
 * @param string $texto_numerico   Cadena que representa un número.
 * @return array{ok:bool,val?:float,msg?:string}
 *         ok=true  → conversión exitosa, val contiene el número.
 *         ok=false → msg explica la causa.
 */
function a_flotante(string $texto_numerico): array {
    $s = str_replace(',', '.', trim($texto_numerico));  // admite "12,5"
    if ($s === '') return ['ok'=>false,'msg'=>'Vacío'];
    if (!is_numeric($s)) return ['ok'=>false,'msg'=>'No numérico'];
    return ['ok'=>true,'val'=>(float)$s];
}

/**
 * a_entero
 *  - Convierte una cadena a entero con validación estricta.
 * @param string $texto_numerico
 * @return array{ok:bool,val?:int,msg?:string}
 */
function a_entero(string $texto_numerico): array {
    $s = trim($texto_numerico);
    // Acepta solo dígitos opcionalmente con signo negativo. Aquí requerimos no negativo en validación de negocio.
    if ($s === '' || !preg_match('/^-?\d+$/', $s)) {
        return ['ok'=>false,'msg'=>'No entero'];
    }
    return ['ok'=>true,'val'=>(int)$s];
}

/**
 * calcular_totales
 *  - Aplica fórmulas de negocio del pedido.
 * @param float $precio_unitario     Precio unitario (>0)
 * @param int   $cantidad            Cantidad (>0)
 * @param float $descuento_porcentaje  Descuento en % (0..100)
 * @param bool  $aplica_iva          Si aplica 16% de IVA
 * @return array{subtotal:float,descuento_aplicado:float,base:float,iva_monto:float,total:float}
 */
function calcular_totales(float $precio_unitario, int $cantidad, float $descuento_porcentaje, bool $aplica_iva): array {
    $subtotal = $precio_unitario * $cantidad;
    $descuento_aplicado = $subtotal * ($descuento_porcentaje / 100.0);
    $base = $subtotal - $descuento_aplicado;
    $iva_monto = $aplica_iva ? ($base * 0.16) : 0.0;
    $total = $base + $iva_monto;
    return compact('subtotal','descuento_aplicado','base','iva_monto','total');
}

/* ──────────────────────────────────────────────────────────────────────
   Estado en sesión
   ────────────────────────────────────────────────────────────────────── */

if (!isset($_SESSION['pedidos']) || !is_array($_SESSION['pedidos'])) {
    $_SESSION['pedidos'] = [];   // Arreglo de pedidos recientes (máx. 10)
}

/**
 * insertar_pedido
 *  - Inserta al inicio del arreglo de sesión y limita a 10 elementos.
 * @param array $pedido  Estructura con datos del pedido + totales.
 * @return void
 */
function insertar_pedido(array $pedido): void {
    array_unshift($_SESSION['pedidos'], $pedido);   // Inserta al frente
    if (count($_SESSION['pedidos']) > 10) {         // Mantener 10 recientes
        array_pop($_SESSION['pedidos']);            // Elimina el más viejo
    }
}

/* ──────────────────────────────────────────────────────────────────────
   Proceso de formulario (POST)
   ────────────────────────────────────────────────────────────────────── */

$errores = [];                 // Mapa campo → mensaje
$anteriores = [                // Valores previos para re‑mostrar el formulario
    'producto'  => '',
    'precio'    => '',
    'cantidad'  => '',
    'descuento' => '0',
    'iva'       => ''          // 'on' si checkbox marcado
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Extraer entradas crudas
    $anteriores['producto']  = depurar_texto($_POST['producto']  ?? '');
    $anteriores['precio']    = trim((string)($_POST['precio']    ?? ''));
    $anteriores['cantidad']  = trim((string)($_POST['cantidad']  ?? ''));
    $anteriores['descuento'] = trim((string)($_POST['descuento'] ?? '0'));
    $anteriores['iva']       = isset($_POST['iva']) ? 'on' : '';

    // 2) Validar cada campo
    if ($anteriores['producto'] === '') {
        $errores['producto'] = 'Producto obligatorio.';
    } elseif (mb_strlen($anteriores['producto']) > 100) {
        $errores['producto'] = 'Máximo 100 caracteres.';
    }

    $v_precio = a_flotante($anteriores['precio']);
    if (!$v_precio['ok']) {
        $errores['precio'] = 'Precio inválido.';
    } elseif ($v_precio['val'] <= 0) {
        $errores['precio'] = 'Precio debe ser > 0.';
    }

    $v_cantidad = a_entero($anteriores['cantidad']);
    if (!$v_cantidad['ok']) {
        $errores['cantidad'] = 'Cantidad inválida.';
    } elseif ($v_cantidad['val'] <= 0) {
        $errores['cantidad'] = 'Cantidad debe ser > 0.';
    }

    $v_desc = a_flotante($anteriores['descuento']);
    if (!$v_desc['ok']) {
        $errores['descuento'] = 'Descuento inválido.';
    } elseif ($v_desc['val'] < 0 || $v_desc['val'] > 100) {
        $errores['descuento'] = 'Descuento debe estar entre 0 y 100.';
    }

    $aplica_iva = ($anteriores['iva'] === 'on');

    // 3) Si no hay errores → calcular totales, guardar y aplicar PRG
    if (empty($errores)) {
        $totales = calcular_totales((float)$v_precio['val'], (int)$v_cantidad['val'], (float)$v_desc['val'], $aplica_iva);
        $pedido = [
            'fecha_hora' => date('Y-m-d H:i:s'),
            'producto'   => $anteriores['producto'],
            'precio'     => (float)$v_precio['val'],
            'cantidad'   => (int)$v_cantidad['val'],
            'descuento'  => (float)$v_desc['val'],
            'iva'        => $aplica_iva,
            'totales'    => $totales
        ];
        insertar_pedido($pedido);

        // Patrón PRG (Post/Redirect/Get): evita reenvíos al refrescar
        $mensaje = urlencode('Pedido agregado correctamente.');
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?mensaje=' . $mensaje);
        exit;
    }
}

/* ──────────────────────────────────────────────────────────────────────
   Filtro de listado (GET)
   ────────────────────────────────────────────────────────────────────── */

$minimo_total = 0.0;
if (isset($_GET['minimo_total']) && $_GET['minimo_total'] !== '') {
    $v_min = a_flotante((string)$_GET['minimo_total']);
    if ($v_min['ok'] && $v_min['val'] >= 0) {
        $minimo_total = (float)$v_min['val'];
    }
}

/* ──────────────────────────────────────────────────────────────────────
   Preparación de datos para la vista
   ────────────────────────────────────────────────────────────────────── */

$pedidos = $_SESSION['pedidos'];        // copia local para iterar
$pedidos_filtrados = [];
$suma_totales = 0.0;

foreach ($pedidos as $p) {
    $total_pedido = (float)($p['totales']['total'] ?? 0.0);
    if ($total_pedido >= $minimo_total) {
        $pedidos_filtrados[] = $p;
        $suma_totales += $total_pedido;
    }
}
$conteo_pedidos = count($pedidos_filtrados);
$promedio_tickets = $conteo_pedidos > 0 ? $suma_totales / $conteo_pedidos : 0.0;

/**
 * formatear_numero
 *  - Imprime un flotante con 2 decimales, punto decimal y coma de miles.
 */
function formatear_numero(float $n): string { return number_format($n, 2, '.', ','); }

?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Nivel A – Registro de pedidos (versión español)</title>
  <style>
    /* Estilos mínimos embebidos para no depender de archivo externo */
    *{box-sizing:border-box}
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Arial,sans-serif;margin:24px;line-height:1.35}
    h1,h2{margin:0 0 10px 0}
    fieldset{border:1px solid #ccc;padding:12px;border-radius:8px;margin-bottom:16px}
    label{display:block;margin:6px 0 4px;font-weight:600}
    input[type=text],input[type=number]{width:100%;padding:8px;border:1px solid #bbb;border-radius:6px}
    button{padding:8px 14px;border:0;border-radius:8px;cursor:pointer}
    button.primario{background:#0a7;color:white}
    button.secundario{background:#07a;color:white}
    table{border-collapse:collapse;width:100%;margin-top:12px}
    th,td{border:1px solid #ddd;padding:8px;text-align:left}
    th{background:#f4f4f4}
    tr:nth-child(even){background:#fafafa}
    .nota{font-size:0.92rem;color:#333;background:#f9f9ff;border:1px dashed #99f;padding:8px;border-radius:6px;margin:8px 0}
    .error{color:#b00020;font-weight:700}
    .exito{color:#0a7;font-weight:700}
    small.mono{font-family:ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;color:#555}
    .resumen{margin-top:8px;padding:8px;border-left:4px solid #07a;background:#eef7ff}
    hr{border:0;border-top:1px solid #e5e5e5;margin:16px 0}
  </style>
</head>
<body>
  <h1>Nivel A – Registro de pedidos con totales y filtro (100% español)</h1>
  <p class="nota">
    Este mini‑sistema usa <strong>PHP puro</strong> y <strong>sesiones</strong> como almacén temporal.
    La tabla refleja como máximo los <strong>10 pedidos</strong> más recientes.
  </p>

  <!-- Formulario de captura -->
  <form method="post" novalidate>
    <fieldset>
      <legend>Captura de pedido</legend>

      <label for="producto">Producto</label>
      <input type="text" id="producto" name="producto" maxlength="100"
             value="<?php echo htmlspecialchars($anteriores['producto']); ?>" />
      <?php if(isset($errores['producto'])): ?>
        <div class="error"><?php echo $errores['producto']; ?></div>
      <?php endif; ?>

      <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:12px; margin-top:8px;">
        <div>
          <label for="precio">Precio unitario</label>
          <input type="number" id="precio" name="precio" step="0.01" min="0.01"
                 value="<?php echo htmlspecialchars($anteriores['precio']); ?>" />
          <?php if(isset($errores['precio'])): ?>
            <div class="error"><?php echo $errores['precio']; ?></div>
          <?php endif; ?>
        </div>
        <div>
          <label for="cantidad">Cantidad</label>
          <input type="number" id="cantidad" name="cantidad" step="1" min="1"
                 value="<?php echo htmlspecialchars($anteriores['cantidad']); ?>" />
          <?php if(isset($errores['cantidad'])): ?>
            <div class="error"><?php echo $errores['cantidad']; ?></div>
          <?php endif; ?>
        </div>
        <div>
          <label for="descuento">Descuento (%)</label>
          <input type="number" id="descuento" name="descuento" step="0.01" min="0" max="100"
                 value="<?php echo htmlspecialchars($anteriores['descuento']); ?>" />
          <?php if(isset($errores['descuento'])): ?>
            <div class="error"><?php echo $errores['descuento']; ?></div>
          <?php endif; ?>
        </div>
      </div>

      <label style="margin-top:8px">
        <input type="checkbox" name="iva" <?php echo $anteriores['iva']==='on' ? 'checked' : ''; ?> />
        Aplicar IVA 16%
      </label>

      <div style="margin-top:10px; display:flex; gap:8px;">
        <button class="primario" type="submit">Agregar pedido</button>
        <span><small class="mono">PRG activo: tras agregar, redirige para evitar reenvíos.</small></span>
      </div>
      <?php if(isset($_GET['mensaje'])): ?>
        <div class="exito" style="margin-top:8px;"><?php echo htmlspecialchars($_GET['mensaje']); ?></div>
      <?php endif; ?>
    </fieldset>
  </form>

  <!-- Filtro por total mínimo -->
  <form method="get">
    <fieldset>
      <legend>Filtro por total mínimo</legend>
      <label for="minimo_total">Mostrar pedidos con total ≥</label>
      <input type="number" id="minimo_total" name="minimo_total" step="0.01" min="0"
             value="<?php echo htmlspecialchars((string)$minimo_total); ?>" />
      <div style="margin-top:10px; display:flex; gap:8px;">
        <button class="secundario" type="submit">Aplicar filtro</button>
        <a href="<?php echo strtok($_SERVER['REQUEST_URI'],'?'); ?>">
          <button type="button">Limpiar filtro</button>
        </a>
      </div>
    </fieldset>
  </form>

  <!-- Tabla de pedidos -->
  <h2>Pedidos recientes</h2>
  <table>
    <thead>
      <tr>
        <th>Fecha</th>
        <th>Producto</th>
        <th>Precio</th>
        <th>Cantidad</th>
        <th>Desc. %</th>
        <th>IVA</th>
        <th>Subtotal</th>
        <th>Base</th>
        <th>IVA $</th>
        <th>Total</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($pedidos_filtrados)): ?>
        <tr><td colspan="10">Sin pedidos para mostrar.</td></tr>
      <?php else: ?>
        <?php foreach ($pedidos_filtrados as $p): ?>
          <tr>
            <td><?php echo htmlspecialchars($p['fecha_hora']); ?></td>
            <td><?php echo htmlspecialchars($p['producto']); ?></td>
            <td>$<?php echo formatear_numero((float)$p['precio']); ?></td>
            <td><?php echo (int)$p['cantidad']; ?></td>
            <td><?php echo formatear_numero((float)$p['descuento']); ?>%</td>
            <td><?php echo $p['iva'] ? 'Sí' : 'No'; ?></td>
            <td>$<?php echo formatear_numero((float)$p['totales']['subtotal']); ?></td>
            <td>$<?php echo formatear_numero((float)$p['totales']['base']); ?></td>
            <td>$<?php echo formatear_numero((float)$p['totales']['iva_monto']); ?></td>
            <td><strong>$<?php echo formatear_numero((float)$p['totales']['total']); ?></strong></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <div class="resumen">
    <strong>Resumen del listado:</strong>
    <div>Pedidos mostrados: <?php echo $conteo_pedidos; ?></div>
    <div>Suma de totales: $<?php echo formatear_numero($suma_totales); ?></div>
    <div>Ticket promedio: $<?php echo formatear_numero($promedio_tickets); ?></div>
  </div>

  <hr />
  <div class="nota">
    <strong>Pruebas rápidas sugeridas:</strong>
    <ul>
      <li>P1: precio 120.50, cantidad 3, desc 10, IVA ✓ → verifica total.</li>
      <li>P2: precio 99.99, cantidad 1, desc 0, IVA ✗ → total=99.99.</li>
      <li>Filtro total ≥ 300 → debería ocultar P2 si queda por debajo.</li>
    </ul>
  </div>
</body>
</html>
