<?php
/**
 * 登入 API 測試
 * 
 * TDD 流程：
 * 1. 🔴 這個測試會失敗（因為 login.php 還未創建）
 * 2. 🟢 創建 login.php 讓測試通過
 * 3. 🔵 重構（如有需要）
 */

use PHPUnit\Framework\TestCase;

class LoginTest extends TestCase
{
    private $apiUrl;
    private $testUser = [
        'email' => 'test@example.com',
        'password' => 'test123',
        'name' => '測試用戶'
    ];
    
    protected function setUp(): void
    {
        $this->apiUrl = __DIR__ . '/../api/login.php';
        
        // 確保測試數據庫存在
        if (!file_exists($this->apiUrl)) {
            $this->markTestSkipped('login.php 尚未創建');
        }
    }
    
    /**
     * 測試 1: login.php 文件是否存在
     */
    public function testLoginFileExists(): void
    {
        $this->assertFileExists(
            $this->apiUrl,
            "login.php 文件不存在，請創建 backend/api/login.php"
        );
    }
    
    /**
     * 測試 2: 登入成功（正確郵箱和密碼）
     */
    public function testLoginSuccess(): void
    {
        if (!file_exists($this->apiUrl)) {
            $this->markTestSkipped('login.php 尚未創建');
        }
        
        // 模擬 POST 請求
        $_POST = [
            'email' => 'admin@gps-checkin.com',
            'password' => 'admin123'
        ];
        
        // 捕獲輸出
        ob_start();
        include $this->apiUrl;
        $output = ob_get_clean();
        
        // 解析 JSON 響應
        $response = json_decode($output, true);
        
        $this->assertEquals('success', $response['status'], '登入應該成功');
        $this->assertArrayHasKey('token', $response['data'], '應該返回 JWT Token');
        $this->assertArrayHasKey('user', $response['data'], '應該返回用戶信息');
    }
    
    /**
     * 測試 3: 登入失敗（錯誤密碼）
     */
    public function testLoginWrongPassword(): void
    {
        if (!file_exists($this->apiUrl)) {
            $this->markTestSkipped('login.php 尚未創建');
        }
        
        $_POST = [
            'email' => 'admin@gps-checkin.com',
            'password' => 'wrongpassword'
        ];
        
        ob_start();
        include $this->apiUrl;
        $output = ob_get_clean();
        
        $response = json_decode($output, true);
        
        $this->assertEquals('error', $response['status'], '密碼錯誤應該登入失敗');
        $this->assertStringContainsString('密碼', $response['message'], '應該提示密碼錯誤');
    }
    
    /**
     * 測試 4: 登入失敗（用戶不存在）
     */
    public function testLoginUserNotFound(): void
    {
        if (!file_exists($this->apiUrl)) {
            $this->markTestSkipped('login.php 尚未創建');
        }
        
        $_POST = [
            'email' => 'nonexistent@example.com',
            'password' => 'anypassword'
        ];
        
        ob_start();
        include $this->apiUrl;
        $output = ob_get_clean();
        
        $response = json_decode($output, true);
        
        $this->assertEquals('error', $response['status'], '用戶不存在應該登入失敗');
        $this->assertStringContainsString('用戶', $response['message'], '應該提示用戶不存在');
    }
    
    /**
     * 測試 5: 登入失敗（缺少郵箱）
     */
    public function testLoginMissingEmail(): void
    {
        if (!file_exists($this->apiUrl)) {
            $this->markTestSkipped('login.php 尚未創建');
        }
        
        $_POST = [
            'password' => 'anypassword'
        ];
        
        ob_start();
        include $this->apiUrl;
        $output = ob_get_clean();
        
        $response = json_decode($output, true);
        
        $this->assertEquals('error', $response['status'], '缺少郵箱應該登入失敗');
        $this->assertStringContainsString('郵箱', $response['message'], '應該提示缺少郵箱');
    }
    
    /**
     * 測試 6: 登入失敗（缺少密碼）
     */
    public function testLoginMissingPassword(): void
    {
        if (!file_exists($this->apiUrl)) {
            $this->markTestSkipped('login.php 尚未創建');
        }
        
        $_POST = [
            'email' => 'test@example.com'
        ];
        
        ob_start();
        include $this->apiUrl;
        $output = ob_get_clean();
        
        $response = json_decode($output, true);
        
        $this->assertEquals('error', $response['status'], '缺少密碼應該登入失敗');
        $this->assertStringContainsString('密碼', $response['message'], '應該提示缺少密碼');
    }
    
    /**
     * 測試 7: 返回的 Token 格式正確
     */
    public function testTokenFormat(): void
    {
        if (!file_exists($this->apiUrl)) {
            $this->markTestSkipped('login.php 尚未創建');
        }
        
        $_POST = [
            'email' => 'admin@gps-checkin.com',
            'password' => 'admin123'
        ];
        
        ob_start();
        include $this->apiUrl;
        $output = ob_get_clean();
        
        $response = json_decode($output, true);
        
        $token = $response['data']['token'] ?? '';
        $this->assertNotEmpty($token, '應該返回 Token');
        $this->assertIsString($token, 'Token 應該是字符串');
        
        // JWT Token 應該有 3 個部分（header.payload.signature）
        $parts = explode('.', $token);
        $this->assertCount(3, $parts, 'JWT Token 應該有 3 個部分');
    }
    
    /**
     * 測試 8: 返回的用戶信息包含必要字段
     */
    public function testUserInfoContainsRequiredFields(): void
    {
        if (!file_exists($this->apiUrl)) {
            $this->markTestSkipped('login.php 尚未創建');
        }
        
        $_POST = [
            'email' => 'admin@gps-checkin.com',
            'password' => 'admin123'
        ];
        
        ob_start();
        include $this->apiUrl;
        $output = ob_get_clean();
        
        $response = json_decode($output, true);
        
        $user = $response['data']['user'] ?? [];
        
        $requiredFields = ['id', 'name', 'email', 'role'];
        foreach ($requiredFields as $field) {
            $this->assertArrayHasKey($field, $user, "用戶信息應該包含字段：$field");
        }
    }
}
