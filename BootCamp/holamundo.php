<!DOCTYPE html> <!-- Declaramos HTML5 para que el navegador use el estándar moderno -->
<html lang="es"> <!-- Indicamos que el contenido está en español -->
<head>
    <meta charset="UTF-8"> <!-- Codificación UTF-8: soporta acentos, ñ y caracteres especiales -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Escala correcta en móviles -->
    <title>
        <?php 
        /* Dentro del <title> imprimimos texto con PHP.
           echo envía la cadena al navegador como parte del título de la pestaña. */
        echo "Hola Mundo con PHP"; 
        ?>
    </title>

    <style>
        /* ====== ESTILOS GENERALES ====== */

        /* Estilos para la etiqueta <body> (toda la página) */
        body {
            font-family: Arial, sans-serif;     /* Tipografía sin serif: limpia y legible */
            background-color: #f0f8ff;          /* Color de fondo (AliceBlue) para contraste suave */
            text-align: center;                  /* Centra el texto por defecto */
            padding: 50px;                       /* Espaciado interno para que “respire” el contenido */
        }

        /* Contenedor principal con caja blanca y sombra */
        .contenedor {
            background-color: white;             /* Fondo blanco dentro del contenedor */
            border-radius: 10px;                 /* Bordes redondeados */
            padding: 30px;                       /* Espacio interno de la caja */
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);/* Sombra sutil para elevar visualmente la caja */
            max-width: 600px;                    /* Ancho máximo para no extenderse en pantallas grandes */
            margin: 0 auto;                      /* Centra horizontalmente el contenedor */
        }

        /* Estilo del título principal */
        .saludo {
            color: #2c3e50;                      /* Azul oscuro/grisáceo */
            font-size: 2em;                      /* Tamaño grande para destacar el saludo */
            margin-bottom: 20px;                 /* Separación inferior del resto del contenido */
        }

        /* Estilos para párrafos de información */
        .info {
            color: #7f8c8d;                      /* Gris medio para texto secundario */
            font-size: 1.1em;                    /* Un poco más grande que el texto base */
        }
    </style>
</head>

<body>
    <!-- Contenedor visual principal -->
    <div class="contenedor">
        <!-- Título de la página impreso con PHP -->
        <h1 class="saludo">
            <?php 
            // Imprime el saludo principal dentro del <h1>
            echo "¡Hola Mundo!"; 
            ?>
        </h1>
        
        <!-- Bloque informativo: fecha y hora de generación de la página -->
        <p class="info">
            Esta página fue generada el: 
            <strong>
                <?php 
                /* date("d/m/Y H:i:s") formatea la fecha actual del servidor:
                   d: día (01-31), m: mes (01-12), Y: año 4 dígitos, H:i:s: hora:minuto:segundo en 24h */
                echo date("d/m/Y H:i:s"); 
                ?>
            </strong>
        </p>
        
        <!-- Información del servidor donde se ejecuta el script -->
        <p class="info">
            Servidor: 
            <strong>
                <?php 
                /* $_SERVER es un arreglo superglobal con datos del servidor y la petición.
                   'SERVER_NAME' normalmente contiene el nombre de host del servidor web. */
                echo $_SERVER['SERVER_NAME']; 
                ?>
            </strong>
        </p>
        
        <?php
            /* ====== LÓGICA PHP PARA EL SALUDO SEGÚN HORA ====== */

            // Variable con el “nombre del visitante”; podría reemplazarse por un valor real o de formulario.
            $visitante = "Amigo visitante";

            /* date("H") devuelve la hora actual en formato 24 horas (00-23).
               La guardamos en $hora para decidir el saludo contextual. */
            $hora = date("H");
            
            /* Estructura condicional:
               - Si la hora es menor a 12 → “Buenos días”.
               - Si no, pero menor a 18 → “Buenas tardes”.
               - En cualquier otro caso → “Buenas noches”. */
            if ($hora < 12) {
                $saludo_tiempo = "Buenos días";
            } elseif ($hora < 18) {
                $saludo_tiempo = "Buenas tardes";
            } else {
                $saludo_tiempo = "Buenas noches";
            }
        ?>
        
        <!-- Tarjeta con el saludo contextual y la hora actual simplificada -->
        <div style="margin-top: 30px; padding: 20px; background-color: #ecf0f1; border-radius: 5px;">
            <h2>
                <?php 
                /* Concatenamos el saludo contextual con el “nombre del visitante”.
                   El operador punto (.) une cadenas en PHP. */
                echo $saludo_tiempo . ", " . $visitante; 
                ?>!
            </h2>

            <p>
                Son las 
                <?php 
                /* Mostramos solo hora y minutos con formato 24h: H:i */
                echo date("H:i"); 
                ?> 
                horas
            </p>
        </div>
        
        <!-- Pie de página con la versión de PHP del servidor -->
        <footer style="margin-top: 30px; font-size: 0.9em; color: #95a5a6;">
            <p>
                Creado con PHP 
                <?php 
                /* phpversion() devuelve la versión actual de PHP instalada en el servidor. */
                echo phpversion(); 
                ?> 
                ❤️
            </p>
        </footer>
    </div>
</body>
</html>

