<?php
/* ========================================================================
   corredor.php — HIPER‑documentada (línea por línea)
   AUTOR: Héctor Arciniega.
   ------------------------------------------------------------------------
   OBJETIVO: Simular una carrera de 100 m con cámara fija en el corredor.
   TECNOLOGÍAS: HTML + CSS + JS nativo (sin librerías). 
   ======================================================================== */
?>
<!doctype html> <!-- Tipo de documento HTML5 para que el navegador renderice en modo estándar -->
<html lang="es"> <!-- Idioma del documento: español -->
<head> <!-- Cabecera: metadatos, título y estilos -->
  <meta charset="utf-8"/> <!-- Codificación UTF‑8 para soportar tildes/ñ -->
  <meta name="viewport" content="width=device-width, initial-scale=1"/> <!-- Escala inicial en móviles (responsivo) -->
  <title>Corredor 100 m — Pista profesional</title> <!-- Título que se verá en la pestaña del navegador -->

  <style> /* ====== INICIO DE ESTILOS CSS ====== */
  /* =====================================================================
     (1) PALETA Y ESTILOS BASE — Variables reutilizables y tipografía
     ===================================================================== */
  :root{ /* :root = selector que apunta al elemento raíz (html). Aquí definimos variables CSS globales. */
    --cielo-1:#aee2ff; /* Color superior del gradiente vertical del cielo (azul claro). */
    --cielo-2:#58b7ff; /* Color inferior del cielo (azul más intenso). */
    --verde-1:#b1f0a3; /* Verdes para colinas/pradera: tono claro. */
    --verde-2:#7bdc6d; /* Verde medio 1. */
    --verde-3:#49b35a; /* Verde medio 2. */
    --verde-4:#2b8d40; /* Verde oscuro (cercano). */
    --tinta:#0b1630;   /* Color de textos (HUD y UI). */
  }
  *{ box-sizing:border-box } /* Incluye padding y borde en los cálculos de ancho/alto (más predecible). */
  html,body{ height:100% } /* Cuerpo ocupa el 100% de alto de la ventana. */
  body{ /* Estilos generales del body (fondo y tipografía). */
    margin:0; /* Elimina margen por defecto del body. */
    background:linear-gradient(#e9f6ff,#cfefff); /* Degradado vertical muy sutil detrás de la tarjeta. */
    color:var(--tinta); /* Usa el color de texto declarado en --tinta. */
    font-family:ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,Ubuntu,Cantarell,"Noto Sans",Arial; /* Pila de fuentes seguras. */
    display:grid; place-items:center; /* Centra el contenido (tarjetas) horizontal y verticalmente. */
    padding:16px; /* Separación con el borde de la ventana. */
  }
  .tarjeta{ /* Caja visual con borde redondeado, sombra y fondo translúcido. */
    width:min(1100px,100%); /* Máximo 1100px, pero no más del 100% del ancho disponible. */
    background:#ffffffcc; /* Blanco con canal alfa (cc ≈ 80% opacidad). */
    border:1px solid #e3e7ef; /* Borde gris claro. */
    border-radius:16px; /* Esquinas redondeadas. */
    padding:14px 16px; /* Relleno interno. */
    backdrop-filter:blur(6px); /* Difumina el fondo bajo la tarjeta (navegadores compatibles). */
    box-shadow:0 10px 30px rgba(0,0,0,.08); /* Sombra suave para elevación. */
  }
  .controles{ margin-bottom:12px } /* Separación inferior entre controles y escena. */
  .fila{ display:flex; flex-wrap:wrap; gap:10px; align-items:center } /* Layout de línea para controles. */
  .fila h1{ margin:0; margin-right:auto; font-size:20px; letter-spacing:.3px } /* Título de la escena. */
  .botonera{ display:flex; gap:8px } /* Agrupa botones con separación. */
  .boton{ /* Estilo visual uniforme de botones. */
    appearance:none; border:1px solid #c9d2e3; background:#f3f6fb; color:#0d1a3a;
    border-radius:10px; padding:8px 12px; font-size:14px; cursor:pointer;
  }
  .boton[disabled]{ opacity:.4; cursor:not-allowed } /* Botón deshabilitado con menor opacidad y sin cursor “mano”. */
  .ayuda{ font-size:12px; color:#53627a } /* Texto de ayuda discreto. */
  .cronometro{ margin-top:8px; font-weight:700; font-size:14px; color:#0d1a3a } /* Línea del cronómetro. */
  .etiqueta-deslizador{ display:flex; align-items:center; gap:10px } /* Etiqueta + input range alineados. */
  .deslizador{ width:260px } /* Ancho del slider. */
  #valor-tiempo{ min-width:64px; font-weight:700; text-align:right } /* Salida del valor del slider alineada a la derecha. */

  /* =====================================================================
     (2) ESCENA (viewport) — Contenedor de la animación con cielo base
     ===================================================================== */
  .escena{
    position:relative; /* Necesario para posicionar elementos internos con position:absolute relativos a la escena. */
    height:540px; /* Altura fija de la ventana de animación (responsivo en ancho, fijo en alto). */
    border-radius:14px; /* Esquinas redondeadas. */
    overflow:hidden; /* Recorta todo lo que salga (capas extra anchas). */
    border:1px solid #d8e1ef; /* Borde sutil. */
    background:linear-gradient(var(--cielo-1),var(--cielo-2)); /* Cielo base: degradado vertical de --cielo-1 a --cielo-2. */
  }
  .hud-distancia{ /* Indicador de metros restantes (HUD). */
    position:absolute; top:10px; left:12px; z-index:60; /* Arriba-izquierda, por encima de todo (z-index alto). */
    background:#111c2f; color:#fff; font-weight:800; font-size:16px; /* Caja oscura con texto blanco y negrita. */
    padding:6px 10px; border-radius:8px; box-shadow:0 6px 16px rgba(0,0,0,.18); /* Relleno, esquinas y sombra. */
  }

  /* =====================================================================
     (3) CIELO: sol, halo, nubes, pájaros — Gradientes y animación simple
     ---------------------------------------------------------------------
     NOTA radial-gradient(ancho elipse alto elipse at X% Y%, color1 dist1%, color2 dist2%)
       • "at X% Y%" = posición del centro de la elipse dentro del elemento.
       • Distancias (dist1, dist2) = hasta dónde llega cada color desde el centro.
       • #0000 significa “transparente”.
     ===================================================================== */
  .cielo{ position:absolute; inset:-50% -50% -50% -50%; z-index:1 } /* Lienzo grande para gradientes (margen negativo para cubrir). */
  .sol{ /* Disco solar con dos anillos de color. */
    position:absolute; right:8%; top:8%; /* Posición relativa a la escena. */
    width:130px; height:130px; border-radius:50%; /* Círculo perfecto. */
    background:radial-gradient(circle at 40% 40%, /* Centro del gradiente en 40% x, 40% y (ligero desplazamiento). */
                              #fff8c9 0 55%,      /* Del centro hasta 55%: color crema claro. */
                              #ffd86b 56% 100%);  /* Del 56% al borde: amarillo más fuerte. */
    z-index:2; /* Sobre el cielo, bajo las nubes. */
  }
  .halo-sol{ /* Halo difuso alrededor del sol. */
    position:absolute; right:6%; top:6%; width:240px; height:240px; border-radius:50%;
    background:radial-gradient(circle, #fff2 0 20%, #fff0 60%); /* Blanco semitransparente → transparente. */
    filter:blur(10px); /* Difumina el halo para que se integre con el cielo. */
    z-index:2;
  }
  .nubes{ /* Tres nubes hechas con gradientes radiales elípticos, superpuestos. */
    position:absolute; inset:0; opacity:.7; z-index:3;
    background:
      radial-gradient(220px 70px at 20% 20%, #ffffffc8 40%, transparent 60%), /* Nube 1: elipse ancha y baja opacidad. */
      radial-gradient(260px 80px at 52% 14%, #ffffffc8 40%, transparent 60%), /* Nube 2: más grande y más arriba. */
      radial-gradient(200px 60px at 82% 26%, #ffffffc8 40%, transparent 60%); /* Nube 3: hacia la derecha. */
  }
  .pajaros{ /* Grupo de pájaros minimalistas que cruzan el cielo. */
    position:absolute; left:-12%; top:18%; width:22%; height:40px; z-index:4; /* Empiezan fuera de la escena por la izquierda. */
    background:
      radial-gradient(6px 3px at 10% 50%, #2b2b2b 98%, #0000 100%), /* Pájaro 1: elipse estrecha (ala). */
      radial-gradient(6px 3px at 20% 50%, #2b2b2b 98%, #0000 100%), /* Pájaro 2: igual patrón, distinta posición. */
      radial-gradient(6px 3px at 30% 50%, #2b2b2b 98%, #0000 100%); /* Pájaro 3. */
    animation:pajaros 14s linear infinite; /* Se mueve de izquierda a derecha en 14s, velocidad constante, bucle infinito. */
    opacity:.7; filter:drop-shadow(0 1px 0 #0002); /* Sombra muy sutil bajo los pájaros. */
  }
  @keyframes pajaros{ from{ transform:translateX(0) } to{ transform:translateX(650%) } } /* Recorre 6.5 anchos del bloque. */

  /* =====================================================================
     (4) SUELO FIJO — Siempre visible en la parte baja (no se mueve nunca)
     ===================================================================== */
  .suelo-fijo{
    position:absolute; left:0; bottom:0; width:100%; height:360px; /* Franja verde inferior. */
    z-index:10; pointer-events:none; /* No interfiere con el ratón y queda por encima del fondo. */
    background:linear-gradient(180deg, #8be57a 0 60px, #4eb14a 60px 100%); /* Verde claro arriba → verde más oscuro abajo. */
  }

  /* =====================================================================
     (5) CAPAS MÓVILES (PARALLAX) — Anchas y colocadas por planos
     ===================================================================== */
  .capa{ position:absolute } /* Base para capas posicionadas dentro de la escena. */
  .capa-ancha{ width:1600%; left:0 } /* MUY anchas (16× el ancho de la escena) para que nunca “se acaben”. */
  .montana{ opacity:.95 } /* Ligera opacidad para mezclar mejor con el cielo. */

  .capa-fondo{ /* Plano lejano: colinas del fondo. */
    bottom:300px; height:200px; z-index:5; /* Más arriba (más lejos). */
    background:linear-gradient(180deg, var(--verde-1), #66c96a); /* Degradado vertical de verdes. */
    clip-path:polygon( /* Silueta ondulada: lista de puntos (x% y%) en porcentajes del tamaño del elemento. */
      0% 100%, 10% 70%, 20% 78%, 30% 60%, 42% 75%, 52% 58%, 65% 72%, 76% 50%,
      88% 66%, 100% 54%, 100% 100%
    );
  }
  .capa-medio{ /* Plano intermedio. */
    bottom:250px; height:240px; z-index:6;
    background:linear-gradient(180deg, var(--verde-2), #4fb45a);
    clip-path:polygon(
      0% 100%, 8% 72%, 16% 66%, 28% 48%, 38% 64%, 52% 42%, 68% 60%, 82% 40%,
      94% 56%, 100% 44%, 100% 100%
    );
  }
  .capa-frente{ /* Plano cercano (más oscuro). */
    bottom:210px; height:270px; z-index:7;
    background:linear-gradient(180deg, var(--verde-3), var(--verde-4));
    clip-path:polygon(
      0% 100%, 6% 78%, 18% 58%, 30% 72%, 42% 52%, 56% 70%, 70% 48%, 84% 68%,
      96% 54%, 100% 62%, 100% 100%
    );
  }
  .gradas{ /* Gradas del estadio (bandas horizontales repetidas). */
    bottom:170px; height:60px; z-index:8;
    background:repeating-linear-gradient(180deg, #8ea0b8 0 6px, #7e8fa8 6px 12px); /* Franja gris repetida verticalmente. */
    opacity:.9;
  }

  .pradera-base{ /* Pradera que se mueve con la pista (cubre la parte baja). */
    position:absolute; left:0; bottom:0; height:340px; width:1600%; z-index:11;
    background:linear-gradient(180deg, #8be57a 0 60px, #4eb14a 60px 100%);
  }

  /* =====================================================================
     (6) PISTA + MARCAS + META — Estructura de carrera
     ===================================================================== */
  .pista{ /* Pista roja de atletismo. */
    position:absolute; bottom:48px; height:140px; z-index:12; width:1600%; left:0; /* Ancha y cerca del suelo. */
    transform:rotateX(6deg) translateZ(0); /* Pequeña sensación de perspectiva (inclinación). */
    background:repeating-linear-gradient(0deg, #c24b3f 0 18px, #d46257 18px 36px); /* Textura de carriles (franjas horizontales). */
    border-top:2px solid #fff; border-bottom:2px solid #fff; /* Bordes blancos superior e inferior. */
    overflow:hidden; /* Recorta marcas internas si se salieran. */
  }
  .carriles{ /* Líneas blancas que separan carriles (cada 36px, línea de 2px). */
    position:absolute; inset:0; /* Ocupa todo el tamaño de la pista. */
    background:repeating-linear-gradient(0deg, transparent 0 36px, rgba(255,255,255,.9) 36px 38px);
    opacity:.7; z-index:13;
  }
  .marcas{ /* Pequeñas rayitas horizontales cerca del borde inferior de la pista. */
    position:absolute; left:0; right:0; bottom:12px; height:12px;
    background:repeating-linear-gradient(90deg, rgba(255,255,255,.85) 0 2px, transparent 2px 10%);
    opacity:.7; z-index:14;
  }
  .marcas-10{ position:absolute; inset:0; z-index:15; pointer-events:none } /* Contendrá marcas a 10 m y 5 m (JS las ubica). */
  .marca-10{ position:absolute; bottom:0; transform:translateX(50%) } /* Se centra sobre su “right: px” (JS). */
  .marca-10 .tique{ width:3px; height:18px; background:#fff; box-shadow:0 0 0 2px rgba(0,0,0,.12); margin:0 auto }
  .marca-10 .etiqueta{ /* Globito con el texto “10 m”, “20 m”, ... */
    position:absolute; bottom:20px; left:50%; transform:translateX(-50%);
    background:#0b1630; color:#fff; font-size:13px; font-weight:800; padding:3px 5px; border-radius:5px;
  }
  .marca-5{ position:absolute; bottom:0; transform:translateX(50%) } /* Igual que la de 10 m pero sin etiqueta. */
  .marca-5 .tique{ width:2px; height:12px; background:#fff; opacity:.8; margin:0 auto }

  .linea-meta{ /* Línea vertical blanca de meta dentro de la pista. */
    position:absolute; right:12px; top:-10px; bottom:-10px; width:10px;
    background:#fff; border:2px solid #0b1630; box-shadow:0 0 0 3px rgba(255,255,255,.6);
    z-index:20; display:flex; align-items:flex-start; justify-content:center;
  }
  .linea-meta span{ /* Etiqueta flotante “META • 100 m”. */
    position:absolute; top:-22px; background:#0b1630; color:#fff; font-weight:800; font-size:12px; padding:2px 6px; border-radius:6px;
  }
  .poste-meta{ /* Poste del banderín al lado de la meta. */
    position:absolute; right:12px; bottom:130px; width:6px; height:72px; background:#93a6c9;
    border-radius:3px; box-shadow:0 0 0 1px rgba(0,0,0,.08) inset; z-index:21;
  }
  .bandera{ position:absolute; left:6px; top:-6px; font-size:22px; transform-origin:0 50% } /* Emoji de banderín. */
  .cinta-meta{ position:absolute; left:-30px; top:30px; width:60px; height:6px; background:red; transform-origin:left center; animation:none } /* Cinta a romper. */
  .cinta-meta.rotura{ animation:romper 1s forwards } /* Al añadir la clase “rotura”, se dispara la animación. */
  @keyframes romper{ /* Animación de la cinta al romperse (gira, estira y se desvanece). */
    0%{ transform:rotate(0) scaleX(1);   opacity:1 }
    50%{transform:rotate(-40deg) scaleX(1.2); opacity:.9 }
    100%{transform:rotate(-80deg) scaleX(1.4); opacity:0 }
  }

  /* =====================================================================
     (7) CORREDOR (SVG) + SOMBRA — El corredor no “avanza”: el mundo se mueve
     ===================================================================== */
  .sombra{ position:absolute; left:12px; bottom:80px; width:220px; height:40px; opacity:.55; z-index:22 } /* Elipse oscura bajo el corredor. */
  .corredor{ position:absolute; left:12px; bottom:96px; width:240px; transform-origin:50% 100%; z-index:23 } /* SVG del corredor. */

  /* Microanimaciones estéticas del cuerpo (balanceos), no afectan el movimiento real. */
  .grupo-tronco{ transform-origin:160px 140px; animation:inclinacion .8s cubic-bezier(.4,0,.2,1) infinite }
  @keyframes inclinacion{ 0%,100%{transform:rotate(-6deg)} 50%{transform:rotate(-10deg)} }
  .grupo-cabeza{ transform-origin:152px 44px; animation:cabeceo .42s ease-in-out infinite }
  @keyframes cabeceo{ 0%,100%{transform:translateY(0)} 50%{transform:translateY(1.6px)} }
  .grupo-brazo-delantero{ transform-origin:170px 102px; animation:brazo-adelante .46s cubic-bezier(.4,0,.2,1) infinite }
  @keyframes brazo-adelante{ 0%{transform:rotate(28deg)} 50%{transform:rotate(-36deg)} 100%{transform:rotate(28deg)} }
  .grupo-brazo-trasero{ transform-origin:120px 102px; animation:brazo-atras .46s cubic-bezier(.4,0,.2,1) infinite }
  @keyframes brazo-atras{ 0%{transform:rotate(-28deg)} 50%{transform:rotate(36deg)} 100%{transform:rotate(-28deg)} }
  .grupo-cadera{ transform-origin:148px 146px; animation:balanceo-cadera .5s ease-in-out infinite }
  @keyframes balanceo-cadera{ 0%{transform:rotate(4deg)} 50%{transform:rotate(-4deg)} 100%{transform:rotate(4deg)} }
  .grupo-pierna-delantera{ transform-origin:170px 146px; animation:paso-delante .5s cubic-bezier(.4,0,.2,1) infinite }
  @keyframes paso-delante{ 0%{transform:rotate(26deg)} 25%{transform:rotate(8deg)} 50%{transform:rotate(-20deg)} 75%{transform:rotate(6deg)} 100%{transform:rotate(26deg)} }
  .grupo-pierna-trasera{ transform-origin:146px 146px; animation:paso-atras .5s cubic-bezier(.4,0,.2,1) infinite }
  @keyframes paso-atras{ 0%{transform:rotate(-20deg)} 25%{transform:rotate(6deg)} 50%{transform:rotate(26deg)} 75%{transform:rotate(8deg)} 100%{transform:rotate(-20deg)} }
  /* ====== FIN DE ESTILOS CSS ====== */
  </style>
</head>
<body> <!-- Cuerpo visible del documento -->

  <!-- ========================= BARRA DE CONTROLES (UI) ========================= -->
  <div class="tarjeta controles"> <!-- Tarjeta con controles -->
    <div class="fila"> <!-- Fila con título y botones -->
      <h1>Corredor 100 m — Pista profesional</h1> <!-- Título descriptivo -->
      <div class="botonera"> <!-- Grupo de botones de control -->
        <button id="boton-iniciar"   class="boton">▶️ Iniciar</button> <!-- Comienza la carrera -->
        <button id="boton-pausar"    class="boton" disabled>⏸️ Pausar</button> <!-- Pausa/Reanuda (inicia deshabilitado) -->
        <button id="boton-reiniciar" class="boton" disabled>↺ Reiniciar</button> <!-- Vuelve al estado inicial -->
      </div>
    </div>
    <div class="fila"> <!-- Fila con slider de tiempo -->
      <label class="etiqueta-deslizador">Tiempo 100 m (segundos)
        <input id="deslizador-tiempo" class="deslizador" type="range" min="9" max="30" step="0.5" value="16"> <!-- Rango de 9 a 30s, paso 0.5 -->
        <output id="valor-tiempo">16.0 s</output> <!-- Muestra el valor elegido -->
      </label>
      <span class="ayuda">Cámara fija en el corredor. Cruza la meta (100 m) y se detiene.</span> <!-- Breve ayuda textual -->
    </div>
    <div id="cronometro" class="cronometro">0.0 s — 0.0 m/s — 0.0 km/h</div> <!-- Cronómetro en vivo -->
  </div>

  <!-- =============================== ESCENA =============================== -->
  <div id="escena" class="escena tarjeta"> <!-- Ventana de la animación, también con estilo de tarjeta -->
    <div id="hud-distancia" class="hud-distancia">100.0 m restantes</div> <!-- HUD que muestra metros restantes -->

    <!-- Cielo y decoración -->
    <div class="cielo"></div> <!-- Lienzo de fondo para gradientes generales del cielo -->
    <div class="sol"></div>   <!-- Disco solar -->
    <div class="halo-sol"></div> <!-- Halo luminoso → se integra con el sol -->
    <div class="nubes"></div>   <!-- Nubes suaves -->
    <div class="pajaros"></div> <!-- Pájaros cruzando el cielo -->

    <!-- Suelo fijo (no se mueve, tapa siempre la parte inferior). -->
    <div class="suelo-fijo"></div>

    <!-- Capas de montañas (móviles, participan del parallax). -->
    <div class="montana capa capa-ancha capa-fondo"></div>  <!-- Plano de fondo (f = 0.6) -->
    <div class="montana capa capa-ancha capa-medio"></div>   <!-- Plano intermedio (f = 0.8) -->
    <div class="montana capa capa-ancha capa-frente"></div>  <!-- Plano cercano (f = 1.0) -->
    <div class="gradas   capa capa-ancha"></div>             <!-- Gradas del estadio (f = 1.0) -->

    <!-- Pradera móvil (se mueve junto con la pista). -->
    <div class="pradera-base capa capa-ancha"></div>

    <!-- Pista y meta (móviles). -->
    <div id="pista" class="pista capa-ancha">
      <div class="carriles"></div>  <!-- Líneas separadoras de carriles (pintadas con gradiente repetido). -->
      <div class="marcas"></div>    <!-- Rayitas decorativas longitudinales. -->
      <div id="marcas-10" class="marcas-10"></div> <!-- Aquí JS inserta marcas de 10 m y 5 m, posicionadas por “right: px”. -->
      <div id="linea-meta" class="linea-meta"><span>META • 100 m</span></div> <!-- Línea de meta con etiqueta flotante. -->
      <div id="poste-meta" class="poste-meta"> <!-- Poste al lado de la meta con banderín y cinta roja. -->
        <div class="bandera">🏁</div>
        <div id="cinta-meta" class="cinta-meta"></div> <!-- Cinta roja que “se rompe” al llegar. -->
      </div>
    </div>

    <!-- Sombra elíptica bajo el corredor (SVG con animación de radio). -->
    <svg class="sombra" viewBox="0 0 200 40" aria-hidden="true">
      <ellipse cx="100" cy="20" rx="60" ry="10" fill="rgba(0,0,0,.32)">
        <animate attributeName="rx" values="52;60;52" dur="0.8s" repeatCount="indefinite"/>
      </ellipse>
    </svg>

    <!-- Corredor (SVG). Sus grupos internos tienen animaciones CSS de balanceo. -->
    <svg id="corredor" class="corredor" viewBox="0 0 220 220" aria-label="Corredor">
      <defs> <!-- Definiciones de gradientes para piel, camiseta, pantalón y zapatillas -->
        <linearGradient id="piel" x1="0" x2="0" y1="0" y2="1"><stop offset="0%" stop-color="#f3d2b0"/><stop offset="100%" stop-color="#e0ab83"/></linearGradient>
        <linearGradient id="camiseta" x1="0" x2="1" y1="0" y2="0"><stop offset="0%" stop-color="#1f6bff"/><stop offset="100%" stop-color="#1242b8"/></linearGradient>
        <linearGradient id="pantalon" x1="0" x2="1" y1="0" y2="0"><stop offset="0%" stop-color="#1b2534"/><stop offset="100%" stop-color="#0e1522"/></linearGradient>
        <linearGradient id="zapatilla" x1="0" x2="1" y1="0" y2="0"><stop offset="0%" stop-color="#111"/><stop offset="100%" stop-color="#333"/></linearGradient>
      </defs>
      <g class="grupo-tronco"> <!-- Tronco + cabeza -->
        <path d="M108,92 C104,76 118,64 132,64 L154,64 C166,64 176,73 176,86 L176,122 C176,130 168,136 160,136 L124,136 C116,136 108,130 108,122 Z" fill="url(#camiseta)"/>
        <rect x="146" y="56" width="12" height="10" rx="3" fill="url(#piel)"/>
        <g class="grupo-cabeza">
          <circle cx="152" cy="44" r="16" fill="url(#piel)"/>
          <path d="M134,42 C139,29 154,27 166,38 C164,27 154,23 149,23 C142,23 137,26 134,42 Z" fill="#2b2b2b"/>
          <circle cx="148" cy="46" r="1.5" fill="#1a1a1a"/>
          <circle cx="157" cy="46" r="1.5" fill="#1a1a1a"/>
          <path d="M147,52 Q152,55 157,52" stroke="#1a1a1a" stroke-width="1.5" fill="none"/>
        </g>
      </g>
      <g class="grupo-brazo-delantero"><rect x="170" y="96" width="32" height="10" rx="5" fill="url(#piel)"/><rect x="200" y="96" width="22" height="9" rx="5" fill="url(#piel)"/></g>
      <g class="grupo-brazo-trasero"><rect x="92" y="96" width="28" height="10" rx="5" fill="url(#piel)"/><rect x="70" y="96" width="22" height="9" rx="5" fill="url(#piel)"/></g>
      <g class="grupo-cadera"><rect x="130" y="130" width="36" height="16" rx="8" fill="#14233d"/></g>
      <g class="grupo-pierna-delantera"><rect x="158" y="144" width="14" height="38" rx="7" fill="url(#pantalon)"/><rect x="158" y="180" width="14" height="36" rx="7" fill="url(#pantalon)"/><path d="M154,214 L182,214 L180,220 L158,220 Z" fill="url(#zapatilla)"/></g>
      <g class="grupo-pierna-trasera"><rect x="134" y="144" width="14" height="38" rx="7" fill="url(#pantalon)"/><rect x="134" y="180" width="14" height="36" rx="7" fill="url(#pantalon)"/><path d="M130,214 L158,214 L156,220 L134,220 Z" fill="url(#zapatilla)"/></g>
    </svg>
  </div>

  <script> // ====== INICIO DE LÓGICA JS ======
  /* ====================================================================
     IDEAS CLAVE:
     - El corredor NO se traslada: movemos TODO el “mundo” hacia la izda.
     - Calculamos D (px) = distancia desde el corredor hasta la meta.
     - pixelesPorMetro = D / 100 → para ubicar marcas a 10 m, 20 m, etc.
     - Web Animations API: element.animate(keyframes, options) con fill:'forwards'.
     - Parallax: fondo 0.6×D, medio 0.8×D, frente/pradera/pista 1.0×D.
     ==================================================================== */

  /* === Referencias DOM a controles y escena === */
  const botonIniciar     = document.getElementById('boton-iniciar');    // Botón “Iniciar”.
  const botonPausar      = document.getElementById('boton-pausar');     // Botón “Pausar/Reanudar”.
  const botonReiniciar   = document.getElementById('boton-reiniciar');  // Botón “Reiniciar a inicio”.
  const deslizadorTiempo = document.getElementById('deslizador-tiempo'); // Slider de tiempo total (segundos). 
  const valorTiempo      = document.getElementById('valor-tiempo');      // Output visual del valor del slider.

  const escena           = document.getElementById('escena');    // Viewport de la animación.
  const corredor         = document.getElementById('corredor');  // SVG del corredor (fijo en X).
  const posteMeta        = document.getElementById('poste-meta'); // Poste junto a la línea de meta.
  const cintaMeta        = document.getElementById('cinta-meta'); // Cinta roja que se “rompe”.

  const cronometro       = document.getElementById('cronometro');  // Línea que muestra t, m/s y km/h.
  const hudDistancia     = document.getElementById('hud-distancia'); // HUD con metros restantes.
  const contenedorMarcas = document.getElementById('marcas-10');     // Contenedor donde se insertan marcas (10/5 m).

  /* === Estado de simulación === */
  let corriendo=false, pausado=false;         // Flags de control de animación.
  let animacionCorredor=null, animacionesMundo=[]; // Web Animations activas (para pausar/reanudar/cancelar).
  let inicioTiempo=null, rafId=null;          // Tiempos para cronómetro y requestAnimationFrame.
  let pixelesPorMetro=1;                      // Conversión “m → px”, se calcula al iniciar.
  let acumulado=0;                            // Tiempo acumulado en ms durante pausas (cronómetro exacto).

  /* Muestra en pantalla el valor del slider con un decimal y sufijo “s”. */
  function fijar_tiempo(seg){ valorTiempo.textContent = seg.toFixed(1) + ' s'; }

  /* Convierte el valor del slider (segundos) a milisegundos para animate(...). */
  function milis(){ return Number(deslizadorTiempo.value) * 1000; }

  /* --------------------------------------------------------------------
     medir_destino(): calcula D (px) que debe moverse el mundo.
     - inicioX = X del corredor relativa al borde izq. de la escena.
     - destinoX = X del posteMeta menos el ancho del corredor + ajuste.
     - distancia = destinoX - inicioX (px totales que debe viajar la pista).
     -------------------------------------------------------------------- */
  function medir_destino(){
    const rEsc  = escena.getBoundingClientRect();   // Rect de la escena (posición y tamaño en viewport).
    const rCor  = corredor.getBoundingClientRect(); // Rect del corredor.
    const rMeta = posteMeta.getBoundingClientRect(); // Rect del poste de meta.

    const inicioX  = rCor.left  - rEsc.left;                             // X relativa del corredor en la escena.
    const destinoX = (rMeta.left - rEsc.left) - rCor.width + 6;          // X donde su “punta” alcanza el poste.
    return { inicioX, destinoX, distancia: destinoX - inicioX };         // Devuelve también la distancia total D en px.
  }

  /* --------------------------------------------------------------------
     crear_marcas(): inserta marcas a 10 m (con etiqueta) y 5 m (tique).
     - Se posicionan usando style.right = metros*pixelesPorMetro + 12 px.
       +12 px es un pequeño margen interior de la pista.
     - Usamos “right” (y no left) porque la pista se mueve hacia la IZDA:
       así las marcas “llegan” al corredor naturalmente.
     -------------------------------------------------------------------- */
  function crear_marcas(){
    contenedorMarcas.innerHTML=''; // Limpia marcas previas si existían.

    for(let i=10;i<=90;i+=10){ // 10, 20, 30, ... 90 m.
      const marca=document.createElement('div'); marca.className='marca-10';
      const tique=document.createElement('div'); tique.className='tique';
      const etiqueta=document.createElement('div'); etiqueta.className='etiqueta'; etiqueta.textContent=i+' m';
      marca.appendChild(tique); marca.appendChild(etiqueta);
      marca.style.right = (i*pixelesPorMetro + 12) + 'px'; // Coloca la marca a la distancia correcta.
      contenedorMarcas.appendChild(marca);
    }
    for(let i=5;i<=95;i+=10){ // 5, 15, 25, ... 95 m.
      const marca=document.createElement('div'); marca.className='marca-5';
      const tique=document.createElement('div'); tique.className='tique';
      marca.appendChild(tique);
      marca.style.right = (i*pixelesPorMetro + 12) + 'px'; // Igual idea para marcas de 5 m.
      contenedorMarcas.appendChild(marca);
    }
  }

  /* Cancela todas las animaciones (si existen) para poder reiniciar limpio. */
  function limpiar_anims(){
    if(animacionCorredor){ animacionCorredor.cancel(); animacionCorredor=null; }
    animacionesMundo.forEach(a=>a.cancel());
    animacionesMundo=[];
  }

  /* Actualiza el HUD (metros restantes) a partir de la fracción f (0..1). */
  function hud(f){ hudDistancia.textContent = (100*(1-f)).toFixed(1) + ' m restantes'; }

  /* --------------------------------------------------------------------
     ciclo_crono(): refresca tiempo, velocidad media y HUD en cada frame.
     - t = (ahora - inicio + acumulado) / 1000  → segundos reales.
     - v = 100 / total                          → m/s.
     - kmh = v * 3.6                            → km/h.
     - f = t / total (capado a 1)               → fracción de avance (HUD).
     -------------------------------------------------------------------- */
  function ciclo_crono(){
    if(!corriendo || pausado) return; // Si no está corriendo o está pausado, no actualizamos.

    const t    = (performance.now() - inicioTiempo + acumulado) / 1000; // Segundos reales.
    const total= Number(deslizadorTiempo.value);                         // Segundos totales elegidos.
    const v    = 100/total;                                             // m/s promedio.
    const kmh  = v*3.6;                                                 // Conversión a km/h.

    cronometro.textContent = `${t.toFixed(1)} s — ${v.toFixed(2)} m/s — ${kmh.toFixed(2)} km/h`;
    let f=t/total; if(f>1) f=1; hud(f);                                 // Actualiza HUD con metros restantes.

    rafId = requestAnimationFrame(ciclo_crono);                         // Pide el siguiente frame.
  }

  /* --------------------------------------------------------------------
     beep(): genera un bip corto usando Web Audio API.
     EXPLICACIÓN (línea por línea):
       - AudioContext = “estudio de audio” con reloj preciso.
       - createOscillator() = fuente de onda (nota pura).
       - createGain() = control de volumen (envolvente). 
       - Conexión: oscilador → ganancia → destino.
       - Envolvente exponencial para evitar “clicks” y sonar natural.
       - start/stop con tiempos absolutos del reloj del contexto.
     -------------------------------------------------------------------- */
  function beep(){
    try{
      const contexto = new (window.AudioContext || window.webkitAudioContext)(); // Crea el contexto (estudio). Compat WebKit.
      const oscilador = contexto.createOscillator(); // Fuente: genera una onda periódica.
      const ganancia  = contexto.createGain();       // Nodo de ganancia: controla volumen.

      oscilador.type = 'triangle';          // Timbre: onda triangular (suave, más audible que sine).
      oscilador.frequency.value = 880;      // Frecuencia en Hz: 880 = nota La5 (aguda, clara).

      oscilador.connect(ganancia);          // Conecta fuente → volumen
      ganancia.connect(contexto.destination);// Conecta volumen → salida del sistema (parlantes).

      const t0 = contexto.currentTime;      // Marca temporal actual del reloj de audio (en segundos).
      ganancia.gain.setValueAtTime(0.001, t0);                 // Nivel inicial casi cero (evita pop/click).
      ganancia.gain.exponentialRampToValueAtTime(0.2, t0+0.01);// Sube exponencial a 0.2 en 10 ms (ataque).
      ganancia.gain.exponentialRampToValueAtTime(0.001, t0+0.25); // Baja exponencial a casi 0 en 250 ms (release).

      oscilador.start(t0);                  // Inicia la nota YA (en t0).
      oscilador.stop(t0 + 0.26);            // La detiene a los ~260 ms (coincide con la envolvente).
    }catch(error){ /* Si el navegador bloquea audio/autoplay, ignoramos el bip sin romper nada. */ }
  }

  /* --------------------------------------------------------------------
     iniciar(): prepara y lanza TODAS las animaciones sincronizadas.
     Pasos:
       1) Seguridad anti-doble clic.
       2) Estado de botones, limpieza y reinicio de acumulados.
       3) Calcula D (px), define pixelesPorMetro y crea marcas.
       4) Lanza parallax: para cada .capa-ancha, mueve -D*f en X.
       5) Crea una animación “vacía” en el corredor para enganchar onfinish
          y ejecutar la rotura de la cinta + beep + parada de cronómetro.
     -------------------------------------------------------------------- */
  function iniciar(){
    if(corriendo) return; // Evita reentradas si ya está corriendo.

    corriendo=true; pausado=false;                        // Actualiza estado.
    botonIniciar.disabled=true;                           // Deshabilita “Iniciar” durante la carrera.
    botonPausar.disabled=false; botonReiniciar.disabled=false; // Activa Pausar y Reiniciar.
    botonPausar.textContent='⏸️ Pausar';                  // Texto inicial del botón Pausar.
    limpiar_anims(); acumulado=0;                         // Cancela animaciones previas y resetea acumulado.

    const {distancia} = medir_destino();                  // D en píxeles.
    pixelesPorMetro = distancia/100;                      // Conversión m → px.
    crear_marcas();                                       // Inserta marcas en la pista.

    const dur = milis();                                  // Duración total elegida (ms).
    inicioTiempo = performance.now();                     // Guarda tiempo base para el cronómetro.
    rafId = requestAnimationFrame(ciclo_crono);           // Arranca el ciclo del cronómetro/HUD.

    // Recorre TODAS las capas anchas (pista, pradera, montañas, gradas) para animarlas.
    document.querySelectorAll('.capa-ancha').forEach(el=>{
      let f=1;                                            // Factor por defecto (plano cercano).
      if(el.classList.contains('capa-fondo')) f=0.6;      // Plano de fondo se mueve 60% de D.
      else if(el.classList.contains('capa-medio')) f=0.8; // Plano medio 80% de D.

      const anim = el.animate(
        [ {transform:'translateX(0px)'},                 // Keyframe inicial: sin desplazamiento.
          {transform:`translateX(${-distancia*f}px)`}    // Keyframe final: movido a la izquierda.
        ],
        { duration:dur, fill:'forwards', easing:'linear' } // Opciones: dura “dur”, mantiene estado final, velocidad constante.
      );
      animacionesMundo.push(anim);                        // Guardamos para pausar/reanudar/cancelar más tarde.
    });

    // Animación “vacía” del corredor (no se mueve). Sirve para tener un onfinish sincronizado.
    animacionCorredor = corredor.animate(
      [ {transform:'translateX(0px)'}, {transform:'translateX(0px)'} ], // No hay cambio de posición.
      { duration:dur, fill:'forwards' }
    );

    animacionCorredor.onfinish = () => {                  // Cuando termina el tiempo: llegó a la meta.
      corriendo=false; botonIniciar.disabled=false;       // Permite iniciar de nuevo.
      cintaMeta.classList.add('rotura');                  // Activa animación de la cinta.
      beep();                                             // Sonido breve de confirmación.
      cancelAnimationFrame(rafId); hud(1);                // Detiene cronómetro y pone HUD en 0.0 m restantes.
    };
  }

  /* Pausa/Reanuda: conserva el tiempo transcurrido (acumulado) y pausa o
     reanuda tanto el cronómetro (rAF) como TODAS las Web Animations. */
  function pausar(){
    if(!corriendo) return;           // Si no corre, nada que pausar.
    pausado=!pausado;                // Alterna estado.
    const accion = pausado ? 'pause' : 'play'; // Método a invocar en animaciones.
    botonPausar.textContent = pausado ? '▶️ Reanudar' : '⏸️ Pausar';

    if(pausado){
      acumulado += performance.now() - inicioTiempo; // Guarda tiempo transcurrido hasta ahora.
      cancelAnimationFrame(rafId);                   // Detiene ciclo del cronómetro.
    }else{
      inicioTiempo = performance.now();              // Reanuda referencia temporal.
      rafId = requestAnimationFrame(ciclo_crono);   // Vuelve a arrancar el ciclo.
    }

    if(animacionCorredor) animacionCorredor[accion](); // Pausa/Reanuda anim “vacía” del corredor.
    animacionesMundo.forEach(a=>a[accion]());          // Pausa/Reanuda TODAS las capas.
  }

  /* Reinicia todo al estado inicial visible (sin desplazamiento ni cintas rotas). */
  function reiniciar(){
    limpiar_anims();                                           // Cancela cualquier animación pendiente.
    corredor.style.transform='translateX(0px)';                // Asegura que el corredor no tenga transform residual.
    document.querySelectorAll('.capa-ancha').forEach(el=> el.style.transform='translateX(0px)'); // Devuelve capas a origen.

    corriendo=false; pausado=false; acumulado=0;               // Estado base.
    botonIniciar.disabled=false;                               // Listo para iniciar.
    botonPausar.disabled=true; botonReiniciar.disabled=true;   // Pausar/Reiniciar deshabilitados.
    botonPausar.textContent='⏸️ Pausar';                       // Texto por defecto.
    cronometro.textContent='0.0 s — 0.0 m/s — 0.0 km/h';       // Cronómetro reseteado.
    hudDistancia.textContent='100.0 m restantes';              // HUD reseteado.
    cintaMeta.classList.remove('rotura');                      // Quita animación de cinta si quedó activada.

    const d=medir_destino().distancia;                         // Recalcula D por si cambió el tamaño de pantalla.
    pixelesPorMetro = d/100; crear_marcas();                   // Recoloca marcas en pista.
    cancelAnimationFrame(rafId);                               // Asegura que no quede un rAF vivo.
  }

  /* === Listeners de UI === */
  deslizadorTiempo.addEventListener('input',()=> fijar_tiempo(Number(deslizadorTiempo.value)) ); // Actualiza texto al mover el slider.
  window.addEventListener('resize',()=>{ const d=medir_destino().distancia; pixelesPorMetro=d/100; crear_marcas(); }); // Recoloca marcas si cambia el ancho.
  botonIniciar .addEventListener('click', iniciar);   // Arranca la simulación.
  botonPausar  .addEventListener('click', pausar);    // Pausa/Continúa.
  botonReiniciar.addEventListener('click', reiniciar);// Vuelve al inicio.

  /* === Estado inicial (al cargar) === */
  fijar_tiempo(Number(deslizadorTiempo.value));       // Pinta el valor inicial del slider.
  setTimeout(()=>{ const d=medir_destino().distancia; pixelesPorMetro=d/100; crear_marcas(); },0); // Espera un "tick" para medidas correctas.
  reiniciar();                                        // Asegura arranque limpio.
  // ====== FIN DE LÓGICA JS ======
  </script>
</body>
</html>
