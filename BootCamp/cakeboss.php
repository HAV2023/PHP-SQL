<?php
// ======================================================================================
// constructor_pasteles_basico.php
// ======================================================================================
// PROPÓSITO DEL PROGRAMA
// ----------------------
// Mostrar, de forma clara y didáctica, cómo un formulario HTML se comunica con PHP
// para producir un resultado a partir de opciones predefinidas (catálogos).
//
// CARACTERÍSTICAS CLAVE (diseñadas para nivel medio superior):
//  1) Catálogos sencillos (arreglos asociativos) con opciones legibles.
//  2) Formulario con <select> y casillas de verificación (checkbox múltiples).
//  3) Recepción segura de datos vía POST (sin JavaScript y sin base de datos).
//  4) Validación mínima en el servidor y re-muestreo de la selección del usuario.
//  5) Reglas simples para bautizar el pastel resultante.
//  6) Botón "Restablecer opciones" que limpia en el SERVIDOR (no solo en el navegador).
//  7) Variables, funciones, clases CSS y comentarios 100% en español.
//
// NOTA: Por simplicidad esta versión NO usa sesiones ni token CSRF. En entornos
//       con autenticación o acciones que modifican datos reales, debe añadirse
//       protección CSRF. Aquí buscamos la claridad didáctica.
// ======================================================================================

// -----------------------------------------------------------------------------
// (1) CATÁLOGOS DE OPCIONES (clave interna → etiqueta visible)
// -----------------------------------------------------------------------------
// Estos arreglos definen qué puede elegir el usuario. Las CLAVES VIAJAN en el
// formulario y las ETIQUETAS se muestran en pantalla. Para añadir opciones,
// basta con agregar nuevas líneas respetando el patrón clave => etiqueta.

$bases_pastel = [
    'vainilla'        => 'Vainilla',
    'chocolate'       => 'Chocolate',
    'terciopelo_rojo' => 'Terciopelo rojo',
    'limon'           => 'Limón',
    'zanahoria'       => 'Zanahoria',
];

$rellenos = [
    'mermelada_fresa'   => 'Mermelada de fresa',
    'ganache_chocolate' => 'Ganache de chocolate',
    'crema_limon'       => 'Crema de limón',
    'crema_batida'      => 'Crema batida',
];

$cubiertas = [
    'crema_mantequilla'   => 'Crema de mantequilla',
    'cobertura_chocolate' => 'Cobertura de chocolate',
    'queso_crema'         => 'Queso crema',
    'glaseado_limon'      => 'Glaseado de limón',
];

$adornos = [
    'chispas'          => 'Chispas de colores',
    'frutos_rojos'     => 'Frutos rojos',
    'coco_rallado'     => 'Coco rallado',
];

// -----------------------------------------------------------------------------
// (2) FUNCIONES AUXILIARES (salida segura y clasificación)
// -----------------------------------------------------------------------------
/**
 * escapar_html
 *  Escapa un texto ANTES de imprimirlo en HTML para impedir inyecciones de
 *  código (XSS). Convierte caracteres especiales en entidades seguras.
 *
 * @param string $texto  Texto original (puede provenir del usuario)
 * @return string        Texto seguro para insertar en HTML
 */
function escapar_html(string $texto): string {
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}

/**
 * clasificar_pastel
 *  Aplica reglas sencillas y legibles para asignar un "nombre" al pastel
 *  según la combinación seleccionada. El ORDEN de las reglas importa: la
 *  primera que coincida detiene la evaluación.
 *
 * @param string $base            Clave elegida de base
 * @param string $relleno         Clave elegida de relleno
 * @param string $cubierta        Clave elegida de cubierta
 * @param array  $adornos_sel     Lista de claves de adornos marcados
 * @return string                 Nombre amigable del pastel
 */
function clasificar_pastel(string $base, string $relleno, string $cubierta, array $adornos_sel): string {
    // Regla 1: "Triple chocolate" (chocolate en base, relleno y cubierta)
    if ($base === 'chocolate' && $relleno === 'ganache_chocolate' && $cubierta === 'cobertura_chocolate') {
        return 'Triple chocolate';
    }

    // Regla 2: "Vainilla con fresas" (vainilla + mermelada de fresa + crema batida)
    if ($base === 'vainilla' && $relleno === 'mermelada_fresa' && $cubierta === 'crema_batida') {
        return 'Vainilla con fresas';
    }

    // Regla 3: "Limón clásico" (base limón + glaseado de limón; relleno flexible)
    if ($base === 'limon' && $cubierta === 'glaseado_limon') {
        return 'Limón clásico';
    }

    // Si ninguna regla coincide, devolvemos un nombre genérico
    return 'Pastel personalizado';
}

// -----------------------------------------------------------------------------
// (3) LÓGICA DEL FORMULARIO (lectura, validación, limpieza, resultado)
// -----------------------------------------------------------------------------
// Detectamos si el navegador envió datos con POST. Si es true, el usuario
// pulsó algún botón del formulario.
$es_peticion_post = ($_SERVER['REQUEST_METHOD'] === 'POST');

// Aquí acumularemos mensajes de error (si los hay) y el resultado final.
$lista_errores = [];
$resumen_resultado = null; // Estructura lista para mostrar al usuario

// Variables para conservar lo que el alumno eligió (repoblado del formulario)
$base_elegida = '';
$relleno_elegido = '';
$cubierta_elegida = '';
$adornos_elegidos = [];

if ($es_peticion_post) {
    // Distinguimos entre dos acciones posibles del mismo formulario:
    //  - accion = "procesar"   → valida, clasifica y muestra el resumen
    //  - accion = "limpiar"    → restablece como si fuera la primera carga
    $accion = $_POST['accion'] ?? 'procesar';

    if ($accion === 'limpiar') {
        // Limpiamos TODO y mostramos placeholders nuevamente
        $base_elegida = $relleno_elegido = $cubierta_elegida = '';
        $adornos_elegidos = [];
        $resumen_resultado = null;
        $lista_errores = [];
        $es_peticion_post = false; // simula primera visita para los <option>
    } else {
        // Lectura segura de campos (si faltan, usamos valores neutros)
        $base_elegida     = $_POST['base']     ?? '';
        $relleno_elegido  = $_POST['relleno']  ?? '';
        $cubierta_elegida = $_POST['cubierta'] ?? '';
        $adornos_elegidos = (array)($_POST['adornos'] ?? []); // checkbox múltiples

        // Validación de pertenencia a catálogo (defensa contra manipulación de POST)
        if (!array_key_exists($base_elegida, $bases_pastel)) {
            $lista_errores[] = 'Selecciona una base válida.';
        }
        if (!array_key_exists($relleno_elegido, $rellenos)) {
            $lista_errores[] = 'Selecciona un relleno válido.';
        }
        if (!array_key_exists($cubierta_elegida, $cubiertas)) {
            $lista_errores[] = 'Selecciona una cubierta válida.';
        }

        // Filtramos adornos para quedarnos únicamente con los válidos
        $adornos_filtrados = [];
        foreach ($adornos_elegidos as $clave_ad) {
            if (array_key_exists($clave_ad, $adornos)) {
                $adornos_filtrados[] = $clave_ad;
            }
        }
        $adornos_elegidos = $adornos_filtrados;

        // Si todo es válido, clasificamos y preparamos un resumen "bonito"
        if (empty($lista_errores)) {
            $nombre_tipo = clasificar_pastel($base_elegida, $relleno_elegido, $cubierta_elegida, $adornos_elegidos);
            $resumen_resultado = [
                'tipo'     => $nombre_tipo,
                'base'     => $bases_pastel[$base_elegida],
                'relleno'  => $rellenos[$relleno_elegido],
                'cubierta' => $cubiertas[$cubierta_elegida],
                'adornos'  => array_map(function ($k) use ($adornos) { return $adornos[$k]; }, $adornos_elegidos),
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Constructor de pasteles (básico)</title>
    <style>
        /* Estilos simples, con nombres de clases en español para coherencia didáctica */
        body{font-family:system-ui,Arial,sans-serif;line-height:1.45;margin:0;color:#222}
        .contenedor{max-width:820px;margin:2rem auto;padding:0 1rem}
        .tarjeta{border:1px solid #ddd;border-radius:10px;padding:1rem;background:#fff}
        h1{margin:.2rem 0 0;font-size:1.6rem}
        p.texto_secundario{color:#555}
        label{display:block;font-weight:700;margin:.4rem 0}
        select{width:100%;padding:.45rem;border:1px solid #ccc;border-radius:8px}
        .rejilla_opciones{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:.4rem .8rem}
        .margen_superior{margin-top:1rem}
        .lista_errores{color:#a00}
        .grupo_botones{display:flex;gap:.5rem;flex-wrap:wrap}
        button.boton{padding:.6rem 1rem;border:1px solid #444;border-radius:8px;background:#f5f5f5;cursor:pointer}
        button.boton_secundario{border-color:#999}
        .etiquetas_pastel span{display:inline-block;border:1px solid #ccc;border-radius:999px;padding:.1rem .6rem;margin:.15rem .25rem 0 0}
    </style>
</head>
<body>
<div class="contenedor">
    <!-- Encabezado con propósito claro -->
    <h1>Constructor de pasteles (básico)</h1>
    <p class="texto_secundario">Elige opciones y te diremos qué pastel resulta. (PHP + HTML, sin base de datos)</p>

    <!-- Bloque de errores (solo aparece si hubo envío POST con errores) -->
    <?php if ($es_peticion_post && !empty($lista_errores)): ?>
        <div class="tarjeta lista_errores margen_superior" aria-live="polite">
            <strong>Corrige lo siguiente:</strong>
            <ul>
                <?php foreach ($lista_errores as $mensaje_error): ?>
                    <li><?= escapar_html($mensaje_error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Formulario principal: se envía a este mismo archivo (action vacío) -->
    <form class="tarjeta margen_superior" method="post" action="">
        <!-- Campo: Base del pastel -->
        <div class="margen_superior">
            <label for="base">Base (sabor del pastel)</label>
            <select id="base" name="base" required>
                <!-- Placeholder deshabilitado. Se selecciona por defecto si NO hubo POST -->
                <option value="" disabled <?= (!$es_peticion_post ? 'selected' : '') ?>>Selecciona la base</option>
                <?php foreach ($bases_pastel as $clave => $etiqueta): ?>
                    <option value="<?= escapar_html($clave) ?>" <?= ($base_elegida === $clave ? 'selected' : '') ?>>
                        <?= escapar_html($etiqueta) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Campo: Relleno -->
        <div class="margen_superior">
            <label for="relleno">Relleno</label>
            <select id="relleno" name="relleno" required>
                <option value="" disabled <?= (!$es_peticion_post ? 'selected' : '') ?>>Selecciona el relleno</option>
                <?php foreach ($rellenos as $clave => $etiqueta): ?>
                    <option value="<?= escapar_html($clave) ?>" <?= ($relleno_elegido === $clave ? 'selected' : '') ?>>
                        <?= escapar_html($etiqueta) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Campo: Cubierta -->
        <div class="margen_superior">
            <label for="cubierta">Cubierta</label>
            <select id="cubierta" name="cubierta" required>
                <option value="" disabled <?= (!$es_peticion_post ? 'selected' : '') ?>>Selecciona la cubierta</option>
                <?php foreach ($cubiertas as $clave => $etiqueta): ?>
                    <option value="<?= escapar_html($clave) ?>" <?= ($cubierta_elegida === $clave ? 'selected' : '') ?>>
                        <?= escapar_html($etiqueta) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Campo: Adornos (múltiple) -->
        <div class="margen_superior">
            <label>Adornos (opcional)</label>
            <div class="rejilla_opciones">
                <?php foreach ($adornos as $clave => $etiqueta): ?>
                    <label>
                        <!-- name="adornos[]" indica a PHP que recibirá un ARREGLO de valores -->
                        <input type="checkbox" name="adornos[]" value="<?= escapar_html($clave) ?>" <?= in_array($clave, $adornos_elegidos, true) ? 'checked' : '' ?>>
                        <?= escapar_html($etiqueta) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Botones de acción: procesar (calcular) y restablecer (limpiar en servidor) -->
        <div class="margen_superior grupo_botones">
            <button class="boton" type="submit" name="accion" value="procesar">Construir pastel</button>
            <button class="boton boton_secundario" type="submit" name="accion" value="limpiar" title="Vuelve a dejar el formulario como nuevo">Restablecer opciones</button>
            <!-- Alternativa solo del navegador (opcional):
            <button class="boton boton_secundario" type="reset">Limpiar (navegador)</button> -->
        </div>
    </form>

    <!-- Bloque de resultado (solo si hubo envío válido sin errores) -->
    <?php if ($es_peticion_post && empty($lista_errores) && $resumen_resultado): ?>
        <div class="tarjeta margen_superior" aria-live="polite">
            <strong>Tipo de pastel: <?= escapar_html($resumen_resultado['tipo']) ?></strong>
            <p><strong>Base:</strong> <?= escapar_html($resumen_resultado['base']) ?></p>
            <p><strong>Relleno:</strong> <?= escapar_html($resumen_resultado['relleno']) ?></p>
            <p><strong>Cubierta:</strong> <?= escapar_html($resumen_resultado['cubierta']) ?></p>
            <p><strong>Adornos:</strong>
                <?php if (empty($resumen_resultado['adornos'])): ?>
                    Ninguno
                <?php else: ?>
                    <span class="etiquetas_pastel">
                        <?php foreach ($resumen_resultado['adornos'] as $etiqueta_ad): ?>
                            <span><?= escapar_html($etiqueta_ad) ?></span>
                        <?php endforeach; ?>
                    </span>
                <?php endif; ?>
            </p>
        </div>
    <?php endif; ?>

    <!-- Pie de página didáctico -->
    <p class="margen_superior texto_secundario"><small>Ruta de aprendizaje: catálogos → formulario → POST → validación → clasificación → resultado. Sin JavaScript. Sin base de datos.</small></p>
</div>
</body>
</html>
