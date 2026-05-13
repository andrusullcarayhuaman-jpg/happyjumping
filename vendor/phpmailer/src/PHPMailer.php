<?php
/**
 * PHPMailer slim - SMTP only version for HappyJumping
 */
namespace PHPMailer\PHPMailer;

class Exception extends \Exception {}

class PHPMailer {
    public $Host       = 'smtp.gmail.com';
    public $Port       = 587;
    public $SMTPAuth   = true;
    public $SMTPSecure = 'tls';
    public $Username   = '';
    public $Password   = '';
    public $From       = '';
    public $FromName   = '';
    public $Subject    = '';
    public $Body       = '';
    public $isHTML     = false;
    public $CharSet    = 'UTF-8';
    public $ErrorInfo  = '';

    private $to = [];
    private $useSmtp = false;

    public function isSMTP()  { $this->useSmtp = true; }
    public function isHTML($v = true) { $this->isHTML = $v; }
    public function addAddress($email, $name = '') { $this->to[] = [$email, $name]; }

    public function send() {
        try {
            if (empty($this->to)) throw new Exception('No recipient');

            $context = stream_context_create([
                'ssl' => [
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true,
                ]
            ]);

            $socket = stream_socket_client(
                "tcp://{$this->Host}:{$this->Port}",
                $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context
            );
            if (!$socket) throw new Exception("Connect failed: $errstr ($errno)");

            $this->_read($socket); // 220

            $this->_send($socket, "EHLO happyjumpingperu.com\r\n");
            $this->_read($socket);

            $this->_send($socket, "STARTTLS\r\n");
            $this->_read($socket);

            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);

            $this->_send($socket, "EHLO happyjumpingperu.com\r\n");
            $this->_read($socket);

            $this->_send($socket, "AUTH LOGIN\r\n");
            $this->_read($socket);
            $this->_send($socket, base64_encode($this->Username) . "\r\n");
            $this->_read($socket);
            $this->_send($socket, base64_encode($this->Password) . "\r\n");
            $resp = $this->_read($socket);
            if (strpos($resp, '235') === false) throw new Exception("Auth failed: $resp");

            $this->_send($socket, "MAIL FROM:<{$this->From}>\r\n");
            $this->_read($socket);

            foreach ($this->to as $recipient) {
                $this->_send($socket, "RCPT TO:<{$recipient[0]}>\r\n");
                $this->_read($socket);
            }

            $this->_send($socket, "DATA\r\n");
            $this->_read($socket);

            $toStr = implode(', ', array_map(fn($r) => $r[1] ? "\"{$r[1]}\" <{$r[0]}>" : $r[0], $this->to));
            $contentType = $this->isHTML ? 'text/html' : 'text/plain';
            $headers  = "From: \"{$this->FromName}\" <{$this->From}>\r\n";
            $headers .= "To: $toStr\r\n";
            $headers .= "Subject: {$this->Subject}\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: $contentType; charset={$this->CharSet}\r\n";
            $headers .= "Date: " . date('r') . "\r\n";

            $body = str_replace("\n.", "\n..", $this->Body);
            $this->_send($socket, $headers . "\r\n" . $body . "\r\n.\r\n");
            $resp = $this->_read($socket);

            $this->_send($socket, "QUIT\r\n");
            fclose($socket);

            if (strpos($resp, '250') === false) throw new Exception("Send failed: $resp");
            return true;

        } catch (Exception $e) {
            $this->ErrorInfo = $e->getMessage();
            return false;
        }
    }

    private function _send($socket, $data) { fwrite($socket, $data); }
    private function _read($socket) {
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') break;
        }
        return $response;
    }
}
