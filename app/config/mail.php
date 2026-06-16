<?php
// ============================================================
// CONFIGURACIÓN DE CORREO - Hostinger SMTP
// ============================================================
// IMPORTANTE: reemplaza MAIL_PASSWORD con la contraseña real
// del buzón atencion@happyjumpingperu.com que creaste en hPanel.
// ============================================================

define('MAIL_HOST',     'smtp.hostinger.com');
define('MAIL_PORT',     465);
define('MAIL_ENCRYPTION', 'ssl');               // Hostinger usa SSL en el puerto 465
define('MAIL_USERNAME', 'atencion@happyjumpingperu.com');
define('MAIL_PASSWORD', 'adminHJ123_');
define('MAIL_FROM',     'atencion@happyjumpingperu.com');
define('MAIL_NAME',     'Happy Jumping Peru');