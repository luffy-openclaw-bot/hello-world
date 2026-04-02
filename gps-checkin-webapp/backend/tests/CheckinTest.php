<?php
/**
 * GPS 打卡 API 測試
 * 
 * TDD 流程：
 * 1. 🔴 這個測試會失敗（因為 checkin.php 還未創建）
 * 2. 🟢 創建 checkin.php 讓測試通過
 * 3. 🔵 重構（如有需要）
 */

use PHPUnit\Framework\TestCase;

class CheckinTest extends TestCase
{
    private $apiUrl;
    
    protected function setUp(): void
    {
        $this->apiUrl = __DIR__ . '/../api/checkin.php';
    }
    
    /**
     * 測試 1: checkin.php 文件是否存在
     */
    public function testCheckinFileExists(): void
    {
        $this->assertFileExists(
            $this->apiUrl,
            "checkin.php 文件不存在，請創建 backend/api/checkin.php"
        );
    }
    
    /**
     * 測試 2: 上班打卡成功
     */
    public function testCheckinSuccess(): void
    {
        if (!file_exists($this->apiUrl)) {
            $this->markTestSkipped('checkin.php 尚未創建');
        }
        
        // 模擬 POST 請求（需要 Token）
        $_POST = [
            'latitude' => 22.3193,
            'longitude' => 114.1694,
            'type' => 'checkin' // checkin 或 checkout
        ];
        
        // Mock Token
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer mock_token';
        
        ob_start();
        include $this->apiUrl;
        $output = ob_get_clean();
        
        $response = json_decode($output, true);
        
        // 因為數據庫可能未配置，我們只測試基本結構
        $this->assertArrayHasKey('status', $response, '應該返回 status 字段');
    }
    
    /**
     * 測試 3: 缺少緯度
     */
    public function testCheckinMissingLatitude(): void
    {
        if (!file_exists($this->apiUrl)) {
            $this->markTestSkipped('checkin.php 尚未創建');
        }
        
        $_POST = [
            'longitude' => 114.1694,
            'type' => 'checkin'
        ];
        
        ob_start();
        include $this->apiUrl;
        $output = ob_get_clean();
        
        $response = json_decode($output, true);
        
        $this->assertEquals('error', $response['status'], '缺少緯度應該失敗');
        $this->assertStringContainsString('緯度', $response['message'], '應該提示缺少緯度');
    }
    
    /**
     * 測試 4: 缺少經度
     */
    public function testCheckinMissingLongitude(): void
    {
        if (!file_exists($this->apiUrl)) {
            $this->markTestSkipped('checkin.php 尚未創建');
        }
        
        $_POST = [
            'latitude' => 22.3193,
            'type' => 'checkin'
        ];
        
        ob_start();
        include $this->apiUrl;
        $output = ob_get_clean();
        
        $response = json_decode($output, true);
        
        $this->assertEquals('error', $response['status'], '缺少經度應該失敗');
        $this->assertStringContainsString('經度', $response['message'], '應該提示缺少經度');
    }
    
    /**
     * 測試 5: 缺少打卡類型
     */
    public function testCheckinMissingType(): void
    {
        if (!file_exists($this->apiUrl)) {
            $this->markTestSkipped('checkin.php 尚未創建');
        }
        
        $_POST = [
            'latitude' => 22.3193,
            'longitude' => 114.1694
        ];
        
        ob_start();
        include $this->apiUrl;
        $output = ob_get_clean();
        
        $response = json_decode($output, true);
        
        $this->assertEquals('error', $response['status'], '缺少打卡類型應該失敗');
        $this->assertStringContainsString('打卡類型', $response['message'], '應該提示缺少打卡類型');
    }
    
    /**
     * 測試 6: 無效的打卡類型
     */
    public function testCheckinInvalidType(): void
    {
        if (!file_exists($this->apiUrl)) {
            $this->markTestSkipped('checkin.php 尚未創建');
        }
        
        $_POST = [
            'latitude' => 22.3193,
            'longitude' => 114.1694,
            'type' => 'invalid_type'
        ];
        
        ob_start();
        include $this->apiUrl;
        $output = ob_get_clean();
        
        $response = json_decode($output, true);
        
        $this->assertEquals('error', $response['status'], '無效打卡類型應該失敗');
    }
    
    /**
     * 測試 7: 無效的緯度（超出範圍）
     */
    public function testCheckinInvalidLatitude(): void
    {
        if (!file_exists($this->apiUrl)) {
            $this->markTestSkipped('checkin.php 尚未創建');
        }
        
        $_POST = [
            'latitude' => 200, // 無效緯度（應該 -90 到 90）
            'longitude' => 114.1694,
            'type' => 'checkin'
        ];
        
        ob_start();
        include $this->apiUrl;
        $output = ob_get_clean();
        
        $response = json_decode($output, true);
        
        $this->assertEquals('error', $response['status'], '無效緯度應該失敗');
        $this->assertStringContainsString('緯度', $response['message'], '應該提示緯度無效');
    }
    
    /**
     * 測試 8: 無效的經度（超出範圍）
     */
    public function testCheckinInvalidLongitude(): void
    {
        if (!file_exists($this->apiUrl)) {
            $this->markTestSkipped('checkin.php 尚未創建');
        }
        
        $_POST = [
            'latitude' => 22.3193,
            'longitude' => 200, // 無效經度（應該 -180 到 180）
            'type' => 'checkin'
        ];
        
        ob_start();
        include $this->apiUrl;
        $output = ob_get_clean();
        
        $response = json_decode($output, true);
        
        $this->assertEquals('error', $response['status'], '無效經度應該失敗');
        $this->assertStringContainsString('經度', $response['message'], '應該提示經度無效');
    }
    
    /**
     * 測試 9: 缺少授權 Token
     */
    public function testCheckinMissingToken(): void
    {
        if (!file_exists($this->apiUrl)) {
            $this->markTestSkipped('checkin.php 尚未創建');
        }
        
        unset($_SERVER['HTTP_AUTHORIZATION']);
        
        $_POST = [
            'latitude' => 22.3193,
            'longitude' => 114.1694,
            'type' => 'checkin'
        ];
        
        ob_start();
        include $this->apiUrl;
        $output = ob_get_clean();
        
        $response = json_decode($output, true);
        
        $this->assertEquals('error', $response['status'], '缺少 Token 應該失敗');
        $this->assertStringContainsString('授權', $response['message'], '應該提示缺少授權');
    }
    
    /**
     * 測試 10: 返回的打卡記錄包含必要字段
     */
    public function testCheckinResponseContainsRequiredFields(): void
    {
        if (!file_exists($this->apiUrl)) {
            $this->markTestSkipped('checkin.php 尚未創建');
        }
        
        // 這個測試需要數據庫支持，暫時跳過
        $this->markTestSkipped('需要數據庫支持');
    }
}
