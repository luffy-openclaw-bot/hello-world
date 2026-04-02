<?php
/**
 * JWT 認證工具類
 * 
 * 功能：
 * - 生成 JWT Token
 * - 驗證 JWT Token
 * - 解碼 JWT Token
 */

class Auth
{
    private $secretKey;
    private $expireTime;
    
    public function __construct()
    {
        // 生產環境應該從 .env 讀取
        $this->secretKey = 'gps-checkin-secret-key-2026';
        $this->expireTime = 86400; // 24 小時
    }
    
    /**
     * 生成 JWT Token
     * 
     * @param array $payload Token 載荷
     * @return string JWT Token
     */
    public function generateToken($payload)
    {
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT'
        ];
        
        $payload['iat'] = time(); // Issued at
        $payload['exp'] = time() + $this->expireTime; // Expiration time
        
        $base64Header = $this->base64UrlEncode(json_encode($header));
        $base64Payload = $this->base64UrlEncode(json_encode($payload));
        
        $signature = hash_hmac('SHA256', "$base64Header.$base64Payload", $this->secretKey, true);
        $base64Signature = $this->base64UrlEncode($signature);
        
        return "$base64Header.$base64Payload.$base64Signature";
    }
    
    /**
     * 驗證 JWT Token
     * 
     * @param string $token JWT Token
     * @return bool 是否有效
     */
    public function verifyToken($token)
    {
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                return false;
            }
            
            list($base64Header, $base64Payload, $base64Signature) = $parts;
            
            // 重新計算簽名
            $signature = hash_hmac('SHA256', "$base64Header.$base64Payload", $this->secretKey, true);
            $base64SignatureExpected = $this->base64UrlEncode($signature);
            
            // 驗證簽名
            if (!$this->secureCompare($base64Signature, $base64SignatureExpected)) {
                return false;
            }
            
            // 解碼 payload
            $payload = json_decode($this->base64UrlDecode($base64Payload), true);
            
            // 檢查過期時間
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                return false;
            }
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * 解碼 JWT Token
     * 
     * @param string $token JWT Token
     * @return array|null 載荷數據
     */
    public function decodeToken($token)
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }
        
        $base64Payload = $parts[1];
        return json_decode($this->base64UrlDecode($base64Payload), true);
    }
    
    /**
     * Base64 URL 編碼
     */
    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Base64 URL 解碼
     */
    private function base64UrlDecode($data)
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
    
    /**
     * 安全比較字符串（防止定時攻擊）
     */
    private function secureCompare($a, $b)
    {
        if (function_exists('hash_equals')) {
            return hash_equals($a, $b);
        }
        
        if (strlen($a) !== strlen($b)) {
            return false;
        }
        
        $result = 0;
        for ($i = 0; $i < strlen($a); $i++) {
            $result |= ord($a[$i]) ^ ord($b[$i]);
        }
        
        return $result === 0;
    }
}
