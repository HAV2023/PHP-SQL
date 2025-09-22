// Apertura del script PHP
// Abrimos la 'caja' de PHP: desde aquí el servidor ejecuta instrucciones.
<?php
// ======================================================================================
// cakeboss.php
// Autor Héctor Arciniega Valencia
// Ejercicio comentado con glosario.
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

// Catálogo de bases de pastel (clave => etiqueta)
$bases_pastel = [
    'vainilla'        => 'Vainilla',
    'chocolate'       => 'Chocolate',
    'terciopelo_rojo' => 'Terciopelo rojo',
    'limon'           => 'Limón',
    'zanahoria'       => 'Zanahoria',
];

// Catálogo de rellenos
$rellenos = [
    'mermelada_fresa'   => 'Mermelada de fresa',
    'ganache_chocolate' => 'Ganache de chocolate',
    'crema_limon'       => 'Crema de limón',
    'crema_batida'      => 'Crema batida',
];

// Catálogo de cubiertas
$cubiertas = [
    'crema_mantequilla'   => 'Crema de mantequilla',
    'cobertura_chocolate' => 'Cobertura de chocolate',
    'queso_crema'         => 'Queso crema',
    'glaseado_limon'      => 'Glaseado de limón',
];

// Catálogo de adornos múltiples (checkbox)
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
// Función: escapa texto para HTML seguro
function escapar_html(string $texto): string {
// Escapar caracteres especiales para HTML
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
// Fin de bloque
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
// Función: decide nombre del pastel según reglas
function clasificar_pastel(string $base, string $relleno, string $cubierta, array $adornos_sel): string {
    // Regla 1: "Triple chocolate" (chocolate en base, relleno y cubierta)
// Condicional if
    if ($base === 'chocolate' && $relleno === 'ganache_chocolate' && $cubierta === 'cobertura_chocolate') {
// Retorno de función
        return 'Triple chocolate';
// Fin de bloque
    }

    // Regla 2: "Vainilla con fresas" (vainilla + mermelada de fresa + crema batida)
// Condicional if
    if ($base === 'vainilla' && $relleno === 'mermelada_fresa' && $cubierta === 'crema_batida') {
// Retorno de función
        return 'Vainilla con fresas';
// Fin de bloque
    }

    // Regla 3: "Limón clásico" (base limón + glaseado de limón; relleno flexible)
// Condicional if
    if ($base === 'limon' && $cubierta === 'glaseado_limon') {
// Retorno de función
        return 'Limón clásico';
// Fin de bloque
    }

    // Si ninguna regla coincide, devolvemos un nombre genérico
// Retorno de función
    return 'Pastel personalizado';
// Fin de bloque
}

// -----------------------------------------------------------------------------
// (3) LÓGICA DEL FORMULARIO (lectura, validación, limpieza, resultado)
// -----------------------------------------------------------------------------
// Detectamos si el navegador envió datos con POST. Si es true, el usuario
// pulsó algún botón del formulario.
// Booleano: true si la petición es POST
// ¿El formulario fue enviado? Si el método HTTP es POST, significa que el usuario dio clic en un botón del formulario.
$es_peticion_post = ($_SERVER['REQUEST_METHOD'] === 'POST');

// Aquí acumularemos mensajes de error (si los hay) y el resultado final.
$lista_errores = [];
$resumen_resultado = null; // Estructura lista para mostrar al usuario

// Variables para conservar lo que el alumno eligió (repoblado del formulario)
$base_elegida = '';
$relleno_elegido = '';
$cubierta_elegida = '';
// Catálogo de adornos múltiples (checkbox)
$adornos_elegidos = [];

// Booleano: true si la petición es POST
if ($es_peticion_post) {
    // Distinguimos entre dos acciones posibles del mismo formulario:
    //  - accion = "procesar"   → valida, clasifica y muestra el resumen
    //  - accion = "limpiar"    → restablece como si fuera la primera carga
// Acción enviada por botón (procesar o limpiar)
    $accion = $_POST['accion'] ?? 'procesar';

// Condicional if
    if ($accion === 'limpiar') {
        // Limpiamos TODO y mostramos placeholders nuevamente
        $base_elegida = $relleno_elegido = $cubierta_elegida = '';
// Catálogo de adornos múltiples (checkbox)
        $adornos_elegidos = [];
        $resumen_resultado = null;
        $lista_errores = [];
// Booleano: true si la petición es POST
        $es_peticion_post = false; // simula primera visita para los <option>
// Fin de bloque
    } else {
        // Lectura segura de campos (si faltan, usamos valores neutros)
        $base_elegida     = $_POST['base']     ?? '';
        // Leemos la selección del usuario desde el formulario.
// Si el usuario no eligió nada, dejamos esto como cadena vacía para detectar el error.
$relleno_elegido = $_POST['relleno'] ?? '';
        // Leemos la selección del usuario desde el formulario.
// Si el usuario no eligió nada, dejamos esto como cadena vacía para detectar el error.
$cubierta_elegida = $_POST['cubierta'] ?? '';
// Catálogo de adornos múltiples (checkbox)
        $adornos_elegidos = (array)($_POST['adornos'] ?? []); // checkbox múltiples

        // Validación de pertenencia a catálogo (defensa contra manipulación de POST)
// Validación: clave existe en catálogo
        // Validación: si la base elegida NO existe en el catálogo, significa que falta o se manipuló el formulario.
if (!array_key_exists($base_elegida, $bases_pastel)) {
            $lista_errores[] = 'Selecciona una base válida.';
// Fin de bloque
        }
// Validación: clave existe en catálogo
        // Validación: si el relleno NO existe en el catálogo, reportamos error.
if (!array_key_exists($relleno_elegido, $rellenos)) {
            $lista_errores[] = 'Selecciona un relleno válido.';
// Fin de bloque
        }
// Validación: clave existe en catálogo
        // Validación: si la cubierta NO existe, también es error.
if (!array_key_exists($cubierta_elegida, $cubiertas)) {
            $lista_errores[] = 'Selecciona una cubierta válida.';
// Fin de bloque
        }

        // Filtramos adornos para quedarnos únicamente con los válidos
// Catálogo de adornos múltiples (checkbox)
        $adornos_filtrados = [];
// Bucle foreach
        foreach ($adornos_elegidos as $clave_ad) {
// Validación: clave existe en catálogo
            if (array_key_exists($clave_ad, $adornos)) {
// Catálogo de adornos múltiples (checkbox)
                $adornos_filtrados[] = $clave_ad;
// Fin de bloque
            }
// Fin de bloque
        }
// Catálogo de adornos múltiples (checkbox)
        $adornos_elegidos = $adornos_filtrados;

        // Si todo es válido, clasificamos y preparamos un resumen "bonito"
// Condicional if
        if (empty($lista_errores)) {
            $nombre_tipo = clasificar_pastel($base_elegida, $relleno_elegido, $cubierta_elegida, $adornos_elegidos);
            $resumen_resultado = [
                'tipo'     => $nombre_tipo,
                'base'     => $bases_pastel[$base_elegida],
                'relleno'  => $rellenos[$relleno_elegido],
                'cubierta' => $cubiertas[$cubierta_elegida],
                'adornos'  => array_map(function ($k) use ($adornos) { return $adornos[$k]; }, $adornos_elegidos),
            ];
// Fin de bloque
        }
// Fin de bloque
    }
// Fin de bloque
}
// Cierre del bloque PHP
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Constructor de pasteles (básico)</title>
    <style>
/* ---------- Estilo general de la página ---------- */

/* Cuerpo de la página: define el tipo de letra, la separación entre renglones,
   quita márgenes por defecto y fija el color principal del texto */
body {
  font-family: system-ui, Arial, sans-serif; /* Tipos de letra usados en orden de preferencia */
  line-height: 1.45;                        /* Altura de cada renglón (más legible) */
  margin: 0;                                /* Quita los márgenes por defecto del navegador */
  color: #222;                              /* Color de texto gris muy oscuro */
}

/* ---------- Contenedor principal ---------- */

/* Caja que centra el contenido y limita el ancho para que no se extienda demasiado */
.contenedor {
  max-width: 820px;   /* Ancho máximo de la caja */
  margin: 2rem auto;  /* 2 unidades de margen arriba/abajo y centrado horizontal */
  padding: 0 1rem;    /* Espacio interno solo a los lados (izq y der) */
}

/* ---------- Tarjeta de presentación ---------- */

/* Cuadro con borde, esquinas redondeadas, fondo blanco y espacio interno */
.tarjeta {
  border: 1px solid #ddd;   /* Borde gris claro */
  border-radius: 10px;      /* Esquinas redondeadas */
  padding: 1rem;            /* Espacio interno en todos los lados */
  background: #fff;         /* Fondo blanco */
}

/* ---------- Título y textos ---------- */

/* Título principal */
h1 {
  margin: .2rem 0 0;   /* Margen pequeño arriba, ninguno a los lados, ninguno abajo */
  font-size: 1.6rem;   /* Tamaño de la letra del título */
}

/* Texto secundario: gris medio */
p.texto_secundario {
  color: #555;
}

/* ---------- Formularios ---------- */

/* Etiquetas de formulario */
label {
  display: block;         /* Cada etiqueta ocupa una línea completa */
  font-weight: 700;       /* Texto en negrita */
  margin: .4rem 0;        /* Margen arriba y abajo */
}

/* Listas desplegables */
select {
  width: 100%;             /* Ocupan todo el ancho disponible */
  padding: .45rem;         /* Espacio interno uniforme */
  border: 1px solid #ccc;  /* Borde gris claro */
  border-radius: 8px;      /* Esquinas redondeadas */
}

/* ---------- Rejilla de opciones (adornos) ---------- */

/* Organización en columnas que se adaptan solas al espacio disponible */
.rejilla_opciones {
  display: grid;  
  /* Usamos rejilla: permite ordenar elementos en filas y columnas automáticas */

  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  /* Esto significa:
     - "minmax(220px, 1fr)": cada columna debe medir al menos 220 píxeles,
       pero puede crecer hasta ocupar una fracción del espacio disponible.
     - "repeat(auto-fit, ...)": se repite tantas columnas como quepan en el ancho,
       ajustándose automáticamente según el tamaño de la pantalla. */

  gap: .4rem .8rem; 
  /* Espacio entre filas (0.4) y entre columnas (0.8) */
}

/* ---------- Utilidades ---------- */

/* Margen superior estandarizado */
.margen_superior {
  margin-top: 1rem;
}

/* Lista de errores en color rojo oscuro */
.lista_errores {
  color: #a00;
}

/* ---------- Botonera ---------- */

/* Grupo de botones alineados con separación */
.grupo_botones {
  display: flex;     /* Los coloca en fila */
  gap: .5rem;        /* Espacio entre botones */
  flex-wrap: wrap;   /* Si no caben en la fila, se bajan a otra línea */
}

/* Botón principal */
button.boton {
  padding: .6rem 1rem;        /* Espacio interno (alto y ancho) */
  border: 1px solid #444;     /* Borde gris oscuro */
  border-radius: 8px;         /* Esquinas redondeadas */
  background: #f5f5f5;        /* Fondo gris muy claro */
  cursor: pointer;            /* El cursor cambia a "mano" al pasar encima */
}

/* Botón secundario: igual que el principal, pero con borde más claro */
button.boton_secundario {
  border-color: #999;
}

/* ---------- Etiquetas de resumen ---------- */

/* Cada etiqueta se muestra como una cápsula redondeada con borde */
.etiquetas_pastel span {
  display: inline-block;      /* Se alinean en fila pero respetan ancho propio */
  border: 1px solid #ccc;     /* Borde gris claro */
  border-radius: 999px;       /* Valor muy alto: genera círculos/cápsulas */
  padding: .1rem .6rem;       /* Espacio interno arriba/abajo (.1) y lados (.6) */
  margin: .15rem .25rem 0 0;  /* Separación con otras etiquetas */
}
</style>
</head>
<body>
<div class="contenedor">
    <!-- Encabezado con propósito claro -->
    <h1>Constructor de pasteles (básico)</h1>
    <p class="texto_secundario">Elige opciones y te diremos qué pastel resulta. (PHP + HTML, sin base de datos)</p>

    <!-- Bloque de errores (solo aparece si hubo envío POST con errores) -->
// Apertura del script PHP
// Abrimos la 'caja' de PHP: desde aquí el servidor ejecuta instrucciones.
<?php if ($es_peticion_post && !empty($lista_errores)): ?>
        <div class="tarjeta lista_errores margen_superior" aria-live="polite">
            <strong>Corrige lo siguiente:</strong>
            <ul>
// Apertura del script PHP
// Abrimos la 'caja' de PHP: desde aquí el servidor ejecuta instrucciones.
<!-- Vamos a mostrar los errores en una lista: por cada error, dibujamos un renglón. -->
<?php foreach ($lista_errores as $mensaje_error): ?>
                    <!-- Un renglón de la lista con el texto del error.
     '<?= ... ?>' significa: 'imprime aquí'.
     'escapar_html(...)' limpia el texto para que sea seguro. -->
<li><?= escapar_html($mensaje_error) ?></li>
// Apertura del script PHP
// Abrimos la 'caja' de PHP: desde aquí el servidor ejecuta instrucciones.
<?php endforeach; ?>
            </ul>
        </div>
// Apertura del script PHP
// Abrimos la 'caja' de PHP: desde aquí el servidor ejecuta instrucciones.
<?php endif; ?>

    <!-- Formulario principal: se envía a este mismo archivo (action vacío) -->
<!-- Formulario principal -->
    <!-- Formulario: aquí el usuario elige opciones y envía al servidor. -->
<form class="tarjeta margen_superior" method="post" action="">
        <!-- Campo: Base del pastel -->
        <div class="margen_superior">
            <label for="base">Base (sabor del pastel)</label>
<!-- Lista desplegable -->
            <select id="base" name="base" required>
                <!-- Placeholder deshabilitado. Se selecciona por defecto si NO hubo POST -->
// Booleano: true si la petición es POST
                <option value="" disabled <?= (!$es_peticion_post ? 'selected' : '') ?>>Selecciona la base</option>
// Apertura del script PHP
// Abrimos la 'caja' de PHP: desde aquí el servidor ejecuta instrucciones.
<?php foreach ($bases_pastel as $clave => $etiqueta): ?>
<!-- Opción dentro del select -->
                    <option value="<?= escapar_html($clave) ?>" <?= ($base_elegida === $clave ? 'selected' : '') ?>>
                        <?= escapar_html($etiqueta) ?>
                    </option>
// Apertura del script PHP
// Abrimos la 'caja' de PHP: desde aquí el servidor ejecuta instrucciones.
<?php endforeach; ?>
            </select>
        </div>

        <!-- Campo: Relleno -->
        <div class="margen_superior">
            <!-- Etiqueta para el campo del relleno -->
<label for=\"relleno\">Relleno</label>
<!-- Lista desplegable -->
            <!-- Lista desplegable de rellenos; viaja con el nombre 'relleno' -->
<select name=\"relleno\">
// Booleano: true si la petición es POST
                <option value="" disabled <?= (!$es_peticion_post ? 'selected' : '') ?>>Selecciona el relleno</option>
// Apertura del script PHP
// Abrimos la 'caja' de PHP: desde aquí el servidor ejecuta instrucciones.
<?php foreach ($rellenos as $clave => $etiqueta): ?>
<!-- Opción dentro del select -->
                    <option value="<?= escapar_html($clave) ?>" <?= ($relleno_elegido === $clave ? 'selected' : '') ?>>
                        <?= escapar_html($etiqueta) ?>
                    </option>
// Apertura del script PHP
// Abrimos la 'caja' de PHP: desde aquí el servidor ejecuta instrucciones.
<?php endforeach; ?>
            </select>
        </div>

        <!-- Campo: Cubierta -->
        <div class="margen_superior">
            <!-- Etiqueta para el campo de la cubierta -->
<label for=\"cubierta\">Cubierta</label>
<!-- Lista desplegable -->
            <!-- Lista desplegable de cubiertas; viaja con el nombre 'cubierta' -->
<select name=\"cubierta\">
// Booleano: true si la petición es POST
                <option value="" disabled <?= (!$es_peticion_post ? 'selected' : '') ?>>Selecciona la cubierta</option>
// Apertura del script PHP
// Abrimos la 'caja' de PHP: desde aquí el servidor ejecuta instrucciones.
<?php foreach ($cubiertas as $clave => $etiqueta): ?>
<!-- Opción dentro del select -->
                    <option value="<?= escapar_html($clave) ?>" <?= ($cubierta_elegida === $clave ? 'selected' : '') ?>>
                        <?= escapar_html($etiqueta) ?>
                    </option>
// Apertura del script PHP
// Abrimos la 'caja' de PHP: desde aquí el servidor ejecuta instrucciones.
<?php endforeach; ?>
            </select>
        </div>

        <!-- Campo: Adornos (múltiple) -->
        <div class="margen_superior">
            <label>Adornos (opcional)</label>
            <div class="rejilla_opciones">
// Apertura del script PHP
// Abrimos la 'caja' de PHP: desde aquí el servidor ejecuta instrucciones.
<?php foreach ($adornos as $clave => $etiqueta): ?>
                    <label>
                        <!-- name="adornos[]" indica a PHP que recibirá un ARREGLO de valores -->
// Chequea si valor está dentro del arreglo
                        <!-- Casilla de verificación para un adorno. Si se marca, se agrega a la lista 'adornos[]'. -->
<input type="checkbox" name="adornos[]" value="<?= escapar_html($clave) ?>" <?= in_array($clave, $adornos_elegidos, true) ? 'checked' : '' ?>>
                        <?= escapar_html($etiqueta) ?>
                    </label>
// Apertura del script PHP
// Abrimos la 'caja' de PHP: desde aquí el servidor ejecuta instrucciones.
<?php endforeach; ?>
            </div>
        </div>

        <!-- Botones de acción: procesar (calcular) y restablecer (limpiar en servidor) -->
        <div class="margen_superior grupo_botones">
<!-- Botón de acción -->
            <!-- Botón para calcular el nombre del pastel y mostrar el resumen -->
<button class="boton" type="submit" name="accion" value="procesar">Construir pastel</button>
<!-- Botón de acción -->
            <!-- Botón para reiniciar en el servidor y dejar el formulario vacío -->
<button class="boton boton_secundario" type="submit" name="accion" value="limpiar" title="Vuelve a dejar el formulario como nuevo">Restablecer opciones</button>
            <!-- Alternativa solo del navegador (opcional):
<!-- Botón de acción -->
            <button class="boton boton_secundario" type="reset">Limpiar (navegador)</button> -->
        </div>
    </form>

    <!-- Bloque de resultado (solo si hubo envío válido sin errores) -->
// Apertura del script PHP
// Abrimos la 'caja' de PHP: desde aquí el servidor ejecuta instrucciones.
<?php if ($es_peticion_post && empty($lista_errores) && $resumen_resultado): ?>
        <div class="tarjeta margen_superior" aria-live="polite">
            <strong>Tipo de pastel: <?= escapar_html($resumen_resultado['tipo']) ?></strong>
            <p><strong>Base:</strong> <?= escapar_html($resumen_resultado['base']) ?></p>
            <p><strong>Relleno:</strong> <?= escapar_html($resumen_resultado['relleno']) ?></p>
            <p><strong>Cubierta:</strong> <?= escapar_html($resumen_resultado['cubierta']) ?></p>
            <p><strong>Adornos:</strong>
// Apertura del script PHP
// Abrimos la 'caja' de PHP: desde aquí el servidor ejecuta instrucciones.
<?php if (empty($resumen_resultado['adornos'])): ?>
                    Ninguno
// Apertura del script PHP
// Abrimos la 'caja' de PHP: desde aquí el servidor ejecuta instrucciones.
<?php else: ?>
                    <span class="etiquetas_pastel">
// Apertura del script PHP
// Abrimos la 'caja' de PHP: desde aquí el servidor ejecuta instrucciones.
<?php foreach ($resumen_resultado['adornos'] as $etiqueta_ad): ?>
                            <span><?= escapar_html($etiqueta_ad) ?></span>
// Apertura del script PHP
// Abrimos la 'caja' de PHP: desde aquí el servidor ejecuta instrucciones.
<?php endforeach; ?>
                    </span>
// Apertura del script PHP
// Abrimos la 'caja' de PHP: desde aquí el servidor ejecuta instrucciones.
<?php endif; ?>
            </p>
        </div>
// Apertura del script PHP
// Abrimos la 'caja' de PHP: desde aquí el servidor ejecuta instrucciones.
<?php endif; ?>

    <!-- Pie de página didáctico -->
    <p class="margen_superior texto_secundario"><small>Ruta de aprendizaje: catálogos → formulario → POST → validación → clasificación → resultado. Sin JavaScript. Sin base de datos.</small></p>
</div>
</body>
</html>

<?php
/*
========================
GLOSARIO BÁSICO (Nivel principiante)
========================

Variable ($variable):
- Es una "caja" donde guardamos datos (texto, números, listas, etc.).
- En PHP siempre empiezan con el signo $.

Arreglo (array):
- Es una lista de valores.
- Puede ser simple: ["manzana", "pera"]
- O asociativo (clave => valor): ["choco" => "Chocolate"]

Condicional (if ... else):
- Sirve para tomar decisiones.
- "Si pasa X, haz esto. Si no, haz lo otro."

Bucle foreach:
- Recorre todos los elementos de una lista.
- Ejemplo: foreach (lista as item) { ... } → “para cada cosa de la lista, haz ...”.

Función (function ...):
- Es un bloque de código que podemos reutilizar.
- Ejemplo: function sumar(a, b) { return a+b; }

return:
- Indica lo que la función "devuelve" al terminar.

<?= ... ?>:
- Es una forma rápida de decir "imprime aquí este valor en la página".

Superglobal $_POST:
- Es un arreglo especial donde PHP guarda todo lo que envió el formulario.
- Accedemos con $_POST['nombre_del_campo'].

Seguridad (escapar_html):
- Nunca debemos imprimir directamente lo que escribe un usuario.
- escapar_html(...) convierte caracteres especiales (<, >, &) en símbolos seguros.
- Así evitamos que alguien meta código peligroso.

Formulario HTML (<form> ... </form>):
- Zona donde el usuario elige opciones y presiona botones para enviarlas al servidor.

select, option:
- Menú desplegable. Cada opción tiene un valor que viaja a PHP.

input type="checkbox":
- Casilla de verificación. Puede marcarse o no.
- Con el mismo nombre terminado en [] (ej. adornos[]) se mandan varios valores como lista.

button:
- Botón que envía el formulario. Puede tener valores distintos (ej. procesar, limpiar).

CSS (style):
- Instrucciones para dar formato visual a la página (colores, tamaños, posiciones, etc.).

*/
?>
