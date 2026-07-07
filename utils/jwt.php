<?php
// ─────────────────────────────────────────────────────────
// IMPLEMENTACIÓN MÍNIMA DE JWT (JSON Web Token)
// ─────────────────────────────────────────────────────────

class JWT {

    public static function generar($datos) {
        $secreto = getenv('JWT_SECRET');
        $expiracion = (int) getenv('JWT_EXPIRATION');

        $header = ['alg' => 'HS256', 'typ' => 'JWT'];

        $payload = array_merge($datos, [
            'iat' => time(),
            'exp' => time() + $expiracion,
        ]);

        $headerCodificado  = self::base64UrlEncode(json_encode($header));
        $payloadCodificado = self::base64UrlEncode(json_encode($payload));

        $firma = hash_hmac('sha256', "$headerCodificado.$payloadCodificado", $secreto, true);
        $firmaCodificada = self::base64UrlEncode($firma);

        return "$headerCodificado.$payloadCodificado.$firmaCodificada";
    }

    public static function verificar($token) {
        $secreto = getenv('JWT_SECRET');

        $partes = explode('.', $token);
        if (count($partes) !== 3) {
            throw new Exception('Token con formato inválido.');
        }

        list($headerCodificado, $payloadCodificado, $firmaCodificada) = $partes;

        $firmaEsperada = hash_hmac('sha256', "$headerCodificado.$payloadCodificado", $secreto, true);
        $firmaEsperadaCodificada = self::base64UrlEncode($firmaEsperada);

        if (!hash_equals($firmaEsperadaCodificada, $firmaCodificada)) {
            throw new Exception('Firma del token inválida.');
        }

        $payload = json_decode(self::base64UrlDecode($payloadCodificado), true);

        if (!isset($payload['exp']) || $payload['exp'] < time()) {
            throw new Exception('El token ha expirado.');
        }

        return $payload;
    }

    private static function base64UrlEncode($datos) {
        return rtrim(strtr(base64_encode($datos), '+/', '-_'), '=');
    }

    private static function base64UrlDecode($datos) {
        return base64_decode(strtr($datos, '-_', '+/'));
    }
}