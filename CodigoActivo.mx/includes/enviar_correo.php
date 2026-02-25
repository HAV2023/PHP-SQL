<?php
/**
 * enviar_correo.php - Función o script para enviar correos electrónicos
 *
 * Maneja el envío de emails (por ejemplo, verificación de cuenta, recuperación
 * de contraseña, notificaciones). Usa mail() nativo o PHPMailer si está implementado.
 * Se incluye o requiere desde register.php, activar.php u otros.
 *
 * @author    Hector Arciniega
 * @copyright 2026 Hector - Código Activo
 * @license   MIT
 * @version   1.0.0
 * @package   CodigoActivo
 * @category  Email / Notificaciones
 * @since     Febrero 2026
 * @uses      mail() o librería PHPMailer
 */

declare(strict_types=1);

// includes/enviar_correo.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';  // Ajusta la ruta si pusiste src/ en otro lugar

function enviarCorreoActivacion($destinatario, $nombre, $token) {
    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP (IONOS México)
        $mail->isSMTP();
        $mail->Host       = 'smtp.mx';              // Tu servidor confirmado
        $mail->SMTPAuth   = true;                         // Requiere auth
        $mail->Username   = 'no-reply@codigoactivo.mx';   // Usuario completo
        $mail->Password   = '';       // Tu contraseña
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // TLS (STARTTLS)
        $mail->Port       = 587;                          // Puerto recomendado

        // Configuraciones adicionales recomendadas
        $mail->CharSet = 'UTF-8';
        $mail->setLanguage('es', 'PHPMailer/language/'); // Para mensajes en español si hay error
        $mail->SMTPDebug  = 0; // Cambia a 2 para depurar (ver logs de errores), 0 en producción

        // Remitente (From)
        $mail->setFrom('no-reply@codigoactivo.mx', 'Código Activo - Portal Educativo');

        // Destinatario
        $mail->addAddress($destinatario, $nombre);

        // Contenido
        $mail->isHTML(true);
        $mail->Subject = 'Activa tu cuenta en Código Activo';

        // Plantilla HTML simple
        $link_activacion = 'https://www.codigoactivo.mx/activar.php?token=' . urlencode($token);
        
        $mail->Body    = '
        <html>
        <head><title>Activación de Cuenta</title></head>
        <body style="font-family: Arial, sans-serif; color: #333;">
            <h2>¡Bienvenido a Código Activo, ' . htmlspecialchars($nombre) . '!</h2>
            <p>Gracias por registrarte en el portal privado para alumnos del CBTis 52.</p>
            <p>Para activar tu cuenta y acceder al dashboard, haz clic en el botón abajo:</p>
            <p style="text-align: center; margin: 30px 0;">
                <a href="' . $link_activacion . '" style="background-color: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 18px;">
                    Activar mi cuenta
                </a>
            </p>
            <p>Si el botón no funciona, copia y pega este enlace en tu navegador:</p>
            <p><a href="' . $link_activacion . '">' . $link_activacion . '</a></p>
            <p>Este enlace expira en 48 horas por seguridad.</p>
            <hr>
            <p style="font-size: 12px; color: #777;">Si no solicitaste este registro, ignora este correo.</p>
        </body>
        </html>';

        $mail->AltBody = 'Bienvenido, ' . $nombre . '! Activa tu cuenta aquí: ' . $link_activacion . ' (expira en 48h)';

        $mail->send();
        return true; // Éxito

    } catch (Exception $e) {
        // Para depurar: guarda el error en un log o muéstralo temporalmente
        error_log("Error al enviar correo: {$mail->ErrorInfo}");
        return false; // Falló
    }
}
?>
