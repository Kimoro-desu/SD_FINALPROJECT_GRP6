<?php
namespace Utils;

class JwtHelper
{
    // A secure secret key for signing the tokens
    // NOTE: In production, this should be moved to an environment variable (.env)
    private static $secret_key = "HariBorrow_Super_Secret_Key_2026!";

    /**
     * Generate a JSON Web Token (JWT)
     * @param array $payload The user data to encode
     * @return string Valid JWT string
     */
    public static function generateToken($payload)
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $base64UrlHeader = self::base64UrlEncode($header);

        // Add Issued At (iat) and Expiration (exp) timestamps
        $payload['iat'] = time();
        $payload['exp'] = time() + (60 * 60 * 24); // Token expires in 24 hours
        $base64UrlPayload = self::base64UrlEncode(json_encode($payload));

        // Create the signature using HMAC-SHA256
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secret_key, true);
        $base64UrlSignature = self::base64UrlEncode($signature);

        // Combine all three parts
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    /**
     * Validate a JWT token and extract the payload
     * @param string $jwt The token from the Authorization header
     * @return mixed Array of payload if valid, false if invalid or expired
     */
    public static function validateToken($jwt)
    {
        if (!$jwt)
            return false;

        $tokenParts = explode('.', $jwt);
        if (count($tokenParts) != 3) {
            return false;
        }

        $base64UrlHeader = $tokenParts[0];
        $base64UrlPayload = $tokenParts[1];
        $signatureProvided = $tokenParts[2];

        // Re-create the signature to verify its authenticity
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secret_key, true);
        $base64UrlSignature = self::base64UrlEncode($signature);

        // Verify the provided signature matches our re-created signature
        $isSignatureValid = ($base64UrlSignature === $signatureProvided);

        if ($isSignatureValid) {
            $payloadJson = self::base64UrlDecode($base64UrlPayload);
            $payloadArray = json_decode($payloadJson, true);

            // Re-verify expiration
            if (isset($payloadArray['exp']) && $payloadArray['exp'] < time()) {
                return false; // Token is expired
            }
            return $payloadArray;
        }

        return false;
    }

    // Helper method to base64url-encode strings safely for transport in URLs headers
    private static function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    // Helper method to decode base64url strings
    private static function base64UrlDecode($data)
    {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }
}
?>