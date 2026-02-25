<?php
/**
 * footer.php - Plantilla de pie de página común del sitio
 *
 * Cierra el contenedor principal, muestra el footer con copyright y nota
 * de uso restringido, incluye el script de Bootstrap JS y cierra las
 * etiquetas <body> y <html>. Se incluye al final de todas las páginas.
 *
 * @author    Hector @Gebirgsjager73
 * @copyright 2026 Hector - Código Activo
 * @license   MIT
 * @version   1.0.0
 * @package   CodigoActivo
 * @category  Plantillas / Layout
 * @since     Febrero 2026
 */

declare(strict_types=1);  // Opcional aquí: puedes quitar esta línea si prefieres
?>

</div> <!-- cierre del container principal -->

<footer class="bg-dark text-white text-center py-4 mt-5">
    <div class="container">
        <p class="mb-1">&copy; <?php echo date('Y'); ?> Código Activo - Portal Privado</p>
        <small>Desarrollado para alumnos de bachillerato | Solo acceso autorizado</small>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
