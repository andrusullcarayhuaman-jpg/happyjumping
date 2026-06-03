<?php
require_once APP_ROOT . '/../vendor/phpmailer/src/PHPMailer.php';
require_once APP_ROOT . '/../app/config/mail.php';

use PHPMailer\PHPMailer\PHPMailer;

class Mailer {

    private static function instancia() {
        $m = new PHPMailer();
        $m->isSMTP();
        $m->Host       = MAIL_HOST;
        $m->Port       = MAIL_PORT;
        $m->SMTPAuth   = true;
        $m->SMTPSecure = 'tls';
        $m->Username   = MAIL_USERNAME;
        $m->Password   = MAIL_PASSWORD;
        $m->From       = MAIL_FROM;
        $m->FromName   = MAIL_NAME;
        $m->CharSet    = 'UTF-8';
        $m->isHTML(true);
        return $m;
    }

    // ── Código de verificación de cuenta nueva ────────────────────────────────
    public static function enviarCodigoVerificacion($correo, $nombre, $codigo) {
        $m = self::instancia();
        $m->addAddress($correo, $nombre);
        $m->Subject = 'Tu código de verificación — Happy Jumping Peru';
        $m->Body = '<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"></head>
<body style="font-family:Poppins,Arial,sans-serif;background:#f4f8ff;margin:0;padding:20px;">
  <div style="max-width:520px;margin:0 auto;background:#fff;border-radius:18px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.10);">
    <div style="background:linear-gradient(90deg,#ff3c8d,#7F00FF);padding:32px;text-align:center;">
      <h1 style="color:#fff;margin:0;font-size:1.7rem;">Happy Jumping Peru 🎈</h1>
      <p style="color:rgba(255,255,255,.85);margin:6px 0 0;">Verificación de cuenta</p>
    </div>
    <div style="padding:32px;">
      <p style="font-size:1.05rem;color:#333;">Hola <strong>' . htmlspecialchars($nombre) . '</strong>,</p>
      <p style="color:#555;line-height:1.6;">Usa el siguiente código para verificar tu correo. Válido por <strong>15 minutos</strong>.</p>
      <div style="background:#f3e5ff;border-radius:14px;padding:28px;text-align:center;margin:24px 0;">
        <p style="margin:0 0 8px;color:#7F00FF;font-weight:700;font-size:.85rem;letter-spacing:2px;text-transform:uppercase;">Código de verificación</p>
        <div style="font-size:3rem;font-weight:900;letter-spacing:10px;color:#7F00FF;font-family:monospace;">' . $codigo . '</div>
      </div>
      <p style="color:#888;font-size:.85rem;">Si no creaste esta cuenta, ignora este correo.</p>
    </div>
    <div style="background:#f4f8ff;padding:16px;text-align:center;border-top:1px solid #eee;">
      <p style="margin:0;color:#aaa;font-size:.8rem;">Happy Jumping Peru — happyjumpingperu.com</p>
    </div>
  </div>
</body></html>';
        return $m->send();
    }

    // ── Confirmación de reserva ───────────────────────────────────────────────
    public static function enviarConfirmacion($reserva, $correo_cliente, $nombre_cliente) {
        $m = self::instancia();
        $m->addAddress($correo_cliente, $nombre_cliente);
        $m->Subject = '¡Tu reserva en Happy Jumping Peru fue confirmada! 🎉';
        $fecha = date('d/m/Y', strtotime($reserva->fecha));
        $hora  = substr($reserva->hora_inicio, 0, 5);
        $m->Body = '<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"></head>
<body style="font-family:Poppins,Arial,sans-serif;background:#f4f8ff;margin:0;padding:20px;">
  <div style="max-width:600px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.1);">
    <div style="background:linear-gradient(90deg,#ff3c8d,#00c6ff);padding:30px;text-align:center;">
      <h1 style="color:white;margin:0;font-size:1.8rem;">¡Reserva Confirmada! 🎉</h1>
      <p style="color:white;margin:8px 0 0;opacity:.9;">Happy Jumping Peru</p>
    </div>
    <div style="padding:30px;">
      <p style="font-size:1.1rem;color:#333;">Hola <strong>' . htmlspecialchars($nombre_cliente) . '</strong>,</p>
      <p style="color:#555;">Tu reserva ha sido <strong style="color:#00a854;">CONFIRMADA</strong>.</p>
      <div style="background:#f9f0ff;border-left:4px solid #7b2ff7;border-radius:8px;padding:20px;margin:20px 0;">
        <table style="width:100%;border-collapse:collapse;">
          <tr><td style="padding:8px 0;color:#888;width:40%;">🎂 Cumpleañero</td><td style="padding:8px 0;font-weight:600;color:#333;">' . htmlspecialchars($reserva->nombre_cumpleanero) . '</td></tr>
          <tr><td style="padding:8px 0;color:#888;">📦 Paquete</td><td style="padding:8px 0;font-weight:600;color:#333;">' . htmlspecialchars($reserva->nombre_paquete) . '</td></tr>
          <tr><td style="padding:8px 0;color:#888;">📅 Fecha</td><td style="padding:8px 0;font-weight:600;color:#333;">' . $fecha . '</td></tr>
          <tr><td style="padding:8px 0;color:#888;">🕐 Hora</td><td style="padding:8px 0;font-weight:600;color:#333;">' . $hora . '</td></tr>
          <tr><td style="padding:8px 0;color:#888;">👥 Personas</td><td style="padding:8px 0;font-weight:600;color:#333;">' . $reserva->cantidad_personas . '</td></tr>
          <tr><td style="padding:8px 0;color:#888;">💰 Monto</td><td style="padding:8px 0;font-weight:600;color:#7b2ff7;">S/ ' . number_format($reserva->monto, 2) . '</td></tr>
        </table>
      </div>
      <div style="background:#fff3cd;border-radius:8px;padding:15px;margin:20px 0;">
        <p style="margin:0;color:#856404;font-size:.9rem;">⚠️ <strong>Recuerda:</strong> Llega 15 minutos antes. No traer bebidas embotelladas.</p>
      </div>
      <p style="color:#555;">¿Dudas? <a href="mailto:happyjumpingperu01@gmail.com" style="color:#7b2ff7;">happyjumpingperu01@gmail.com</a></p>
      <p style="color:#555;">¡Nos vemos pronto! 🎈</p>
    </div>
    <div style="background:#f4f8ff;padding:20px;text-align:center;border-top:1px solid #eee;">
      <p style="margin:0;color:#aaa;font-size:.85rem;">Happy Jumping Peru — happyjumpingperu.com</p>
    </div>
  </div>
</body></html>';
        return $m->send();
    }
}
