<?php
/**
 * JWT 認證測試
 * 
 * TDD 流程：
 * 1. 🔴 這個測試會測試 auth.php 的 JWT 功能
 * 2. 🟢 確保 auth.php 實現正確
 * 3. 🔵 重構（如有需要）
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../includes/auth.php';

class JwtAuthTest extends TestCase
{
    private $auth;
    
    protected function setUp(): void
    {
        $this->auth = new Auth();
    }
    
    /**
     * 測試 1: Auth 類是否存在
     */
    public function testAuthClassExists(): void
    {
        $this->assertTrue(
            class_exists('Auth'),
            "Auth 類不存在，請創建 backend/includes/auth.php"
        );
    }
    
    /**
     * 測試 2: 生成 Token
     */
    public function testGenerateToken(): void
    {
        $payload = [
            'user_id' => 1,
            'email' => 'test@example.com',
            'role' => 'employee'
        ];
        
        $token = $this->auth->generateToken($payload);
        
        $this->assertNotEmpty($token, '應該生成 Token');
        $this->assertIsString($token, 'Token 應該是字符串');
        
        // JWT Token 應該有 3 個部分
        $parts = explode('.', $token);
        $this->assertCount(3, $parts, 'JWT Token 應該有 3 個部分');
    }
    
    /**
     * 測試 3: Token 包含 Payload 數據
     */
    public function testTokenContainsPayload(): void
    {
        $payload = [
            'user_id' => 123,
            'email' => 'user@example.com',
            'role' => 'admin'
        ];
        
        $token = $this->auth->generateToken($payload);
        $decoded = $this->auth->decodeToken($token);
        
        $this->assertEquals(123, $decoded['user_id'], 'Token 應該包含正確的 user_id');
        $this->assertEquals('user@example.com', $decoded['email'], 'Token 應該包含正確的 email');
        $this->assertEquals('admin', $decoded['role'], 'Token 應該包含正確的 role');
    }
    
    /**
     * 測試 4: Token 包含時間戳
     */
    public function testTokenContainsTimestamps(): void
    {
        $payload = ['user_id' => 1];
        $token = $this->auth->generateToken($payload);
        $decoded = $this->auth->decodeToken($token);
        
        $this->assertArrayHasKey('iat', $decoded, 'Token 應該包含 iat (Issued At)');
        $this->assertArrayHasKey('exp', $decoded, 'Token 應該包含 exp (Expiration)');
        
        // exp 應該大於 iat
        $this->assertGreaterThan($decoded['iat'], $decoded['exp'], 'exp 應該大於 iat');
    }
    
    /**
     * 測試 5: 驗證有效 Token
     */
    public function testVerifyValidToken(): void
    {
        $payload = ['user_id' => 1];
        $token = $this->auth->generateToken($payload);
        
        $this->assertTrue(
            $this->auth->verifyToken($token),
            '有效 Token 應該驗證通過'
        );
    }
    
    /**
     * 測試 6: 驗證無效 Token（簽名錯誤）
     */
    public function testVerifyInvalidToken(): void
    {
        $payload = ['user_id' => 1];
        $token = $this->auth->generateToken($payload);
        
        // 篡改 Token
        $parts = explode('.', $token);
        $parts[2] = 'tampered_signature';
        $tamperedToken = implode('.', $parts);
        
        $this->assertFalse(
            $this->auth->verifyToken($tamperedToken),
            '篡改的 Token 應該驗證失敗'
        );
    }
    
    /**
     * 測試 7: 驗證過期 Token
     */
    public function testVerifyExpiredToken(): void
    {
        // 創建一個已過期的 Token
        $payload = [
            'user_id' => 1,
            'exp' => time() - 3600 // 1 小時前過期
        ];
        
        $token = $this->auth->generateToken($payload);
        
        $this->assertFalse(
            $this->auth->verifyToken($token),
            '過期 Token 應該驗證失敗'
        );
    }
    
    /**
     * 測試 8: 解碼 Token
     */
    public function testDecodeToken(): void
    {
        $payload = [
            'user_id' => 456,
            'email' => 'decode@test.com',
            'role' => 'employee',
            'custom_field' => 'custom_value'
        ];
        
        $token = $this->auth->generateToken($payload);
        $decoded = $this->auth->decodeToken($token);
        
        $this->assertEquals(456, $decoded['user_id']);
        $this->assertEquals('decode@test.com', $decoded['email']);
        $this->assertEquals('employee', $decoded['role']);
        $this->assertEquals('custom_value', $decoded['custom_field']);
    }
    
    /**
     * 測試 9: 解碼無效 Token
     */
    public function testDecodeInvalidToken(): void
    {
        $decoded = $this->auth->decodeToken('invalid.token.here');
        
        $this->assertNull($decoded, '無效 Token 應該返回 null');
    }
    
    /**
     * 測試 10: Token 過期時間為 24 小時
     */
    public function testTokenExpirationTime(): void
    {
        $payload = ['user_id' => 1];
        $token = $this->auth->generateToken($payload);
        $decoded = $this->auth->decodeToken($token);
        
        $expectedExp = $decoded['iat'] + 86400; // 24 小時
        
        $this->assertEquals(
            $expectedExp,
            $decoded['exp'],
            'Token 過期時間應該為 24 小時',
            1 // 允許 1 秒誤差
        );
    }
}
