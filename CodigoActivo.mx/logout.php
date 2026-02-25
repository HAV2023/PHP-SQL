<?php
/**
 * logout.php - Cerrar sesión del usuario
 *
 * Destruye la sesión actual, elimina cookies relacionadas (si aplica)
 * y redirige al login o página principal.
 *
 * @author    Hector Arciniega
 * @copyright 2026 Hector - Código Activo
 * @license   MIT
 * @version   1.0.0
 * @package   CodigoActivo
 * @category  Autenticación
 * @since     Febrero 2026
 */

declare(strict_types=1);

session_start();
session_destroy();
header("Location: login.php");
exit;
?>
