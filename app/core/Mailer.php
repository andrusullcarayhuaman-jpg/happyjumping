<?php
// ============================================================
// MAILER - Envía correos usando Gmail SMTP
// Usar: Mailer::enviarConfirmacion($reserva, $correo, $nombre)
// ============================================================

require_once APP_ROOT . '/../vendor/phpmailer/src/PHPMailer.php';
require_once APP_ROOT . '/../app/config/mail.php';

use PHPMailer\PHPMailer\PHPMailer;

class Mailer {

    /**
     * Envía correo de confirmación de reserva al cliente
     */
    public static function enviarConfirmacion($reserva, $correo_cliente, $nombre_cliente) {
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->Port       = MAIL_PORT;
        $mail->SMTPAuth   = true;
        $mail->SMTPSecure = 'tls';
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->From       = MAIL_FROM;
        $mail->FromName   = MAIL_NAME;
        $mail->CharSet    = 'UTF-8';

        $mail->addAddress($correo_cliente, $nombre_cliente);
        $mail->isHTML(true);
        $mail->Subject = '¡Tu reserva en Happy Jumping Peru fue confirmada! 🎉';

        $fecha_formateada = date('d/m/Y', strtotime($reserva->fecha));
        $hora_formateada  = substr($reserva->hora_inicio, 0, 5);

        $mail->Body = '
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"></head>
<body style="font-family: Poppins, Arial, sans-serif; background:#f4f8ff; margin:0; padding:20px;">
  <div style="max-width:600px; margin:0 auto; background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.1);">
    
    <!-- Header -->
    <div style="background:linear-gradient(90deg,#ff3c8d,#00c6ff); padding:30px; text-align:center;">
      <h1 style="color:white; margin:0; font-size:1.8rem;">¡Reserva Confirmada! 🎉</h1>
      <p style="color:white; margin:8px 0 0; opacity:0.9;">Happy Jumping Peru</p>
    </div>

    <!-- Body -->
    <div style="padding:30px;">
      <p style="font-size:1.1rem; color:#333;">Hola <strong>' . htmlspecialchars($nombre_cliente) . '</strong>,</p>
      <p style="color:#555;">Tu reserva ha sido <strong style="color:#00a854;">CONFIRMADA</strong>. Aquí están los detalles:</p>

      <div style="background:#f9f0ff; border-left:4px solid #7b2ff7; border-radius:8px; padding:20px; margin:20px 0;">
        <table style="width:100%; border-collapse:collapse;">
          <tr><td style="padding:8px 0; color:#888; width:40%;">🎂 Cumpleañero</td>
              <td style="padding:8px 0; font-weight:600; color:#333;">' . htmlspecialchars($reserva->nombre_cumpleanero) . '</td></tr>
          <tr><td style="padding:8px 0; color:#888;">📦 Paquete</td>
              <td style="padding:8px 0; font-weight:600; color:#333;">' . htmlspecialchars($reserva->nombre_paquete) . '</td></tr>
          <tr><td style="padding:8px 0; color:#888;">📅 Fecha</td>
              <td style="padding:8px 0; font-weight:600; color:#333;">' . $fecha_formateada . '</td></tr>
          <tr><td style="padding:8px 0; color:#888;">🕐 Hora</td>
              <td style="padding:8px 0; font-weight:600; color:#333;">' . $hora_formateada . '</td></tr>
          <tr><td style="padding:8px 0; color:#888;">👥 Personas</td>
              <td style="padding:8px 0; font-weight:600; color:#333;">' . $reserva->cantidad_personas . '</td></tr>
          <tr><td style="padding:8px 0; color:#888;">💰 Monto</td>
              <td style="padding:8px 0; font-weight:600; color:#7b2ff7;">S/ ' . number_format($reserva->monto, 2) . '</td></tr>
        </table>
      </div>

      <div style="background:#fff3cd; border-radius:8px; padding:15px; margin:20px 0;">
        <p style="margin:0; color:#856404; font-size:0.9rem;">
          ⚠️ <strong>Recuerda:</strong> Reserva con mínimo 3 días de anticipación. 
          Queda prohibido traer bebidas embotelladas. 
          Llega 15 minutos antes de tu horario.
        </p>
      </div>

      <p style="color:#555;">Si tienes alguna duda, escríbenos a <a href="mailto:happyjumpingperu01@gmail.com" style="color:#7b2ff7;">happyjumpingperu01@gmail.com</a></p>
      <p style="color:#555;">¡Nos vemos pronto! 🎈</p>
    </div>

    <!-- Footer -->
    <div style="background:#f4f8ff; padding:20px; text-align:center; border-top:1px solid #eee;">
      <p style="margin:0; color:#aaa; font-size:0.85rem;">Happy Jumping Peru &mdash; happyjumpingperu.com</p>
    </div>

  </div>
</body>
</html>';

        return $mail->send();
    }
}
