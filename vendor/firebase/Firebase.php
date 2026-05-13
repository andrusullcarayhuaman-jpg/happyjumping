<?php
class Firebase {
    private $database_url;
    private $credentials_path;

    public function __construct($database_url, $credentials_path) {
        $this->database_url     = rtrim($database_url, '/');
        $this->credentials_path = $credentials_path;
    }

    public function push($node, $data) {
        $token = $this->getAccessToken();
        if (!$token) return false;

        $url  = $this->database_url . '/' . ltrim($node, '/') . '.json?access_token=' . $token;
        $body = json_encode($data);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        ]);

        $response  = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $http_code === 200;
    }

    private function getAccessToken() {
        if (!file_exists($this->credentials_path)) return false;

        $credentials = json_decode(file_get_contents($this->credentials_path), true);

        $now     = time();
        $header  = $this->base64url(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = $this->base64url(json_encode([
            'iss'   => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.database https://www.googleapis.com/auth/userinfo.email',
            'aud'   => 'https://oauth2.googleapis.com/token',
            'iat'   => $now,
            'exp'   => $now + 3600,
        ]));

        $sign_input = $header . '.' . $payload;
        openssl_sign($sign_input, $signature, $credentials['private_key'], 'SHA256');
        $jwt = $sign_input . '.' . $this->base64url($signature);

        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]),
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);

        return $response['access_token'] ?? false;
    }

    private function base64url($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}