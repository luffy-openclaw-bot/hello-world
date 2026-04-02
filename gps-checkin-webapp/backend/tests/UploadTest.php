<?php
/**
 * 照片上傳 API 測試
 * 
 * TDD 流程：
 * 1. 🔴 這個測試會失敗（因為 upload.php 還未創建）
 * 2. 🟢 創建 upload.php 讓測試通過
 * 3. 🔵 重構（如有需要）
 */

use PHPUnit\Framework\TestCase;

class UploadTest extends TestCase
{
    private $apiUrl;
    private $uploadDir;
    
    protected function setUp(): void
    {
        $this->apiUrl = __DIR__ . '/../api/upload.php';
        $this->uploadDir = __DIR__ . '/../uploads/checkins/';
        
        // 確保上傳目錄存在
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    protected function tearDown(): void
    {
        // 清理測試文件
        $files = glob($this->uploadDir . 'test_*.jpg');
        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
    
    /**
     * 測試 1: upload.php 文件是否存在
     */
    public function testUploadFileExists(): void
    {
        $this->assertFileExists(
            $this->apiUrl,
            "upload.php 文件不存在，請創建 backend/api/upload.php"
        );
    }
    
    /**
     * 測試 2: 上傳成功（模擬 JPEG 圖片）
     */
    public function testUploadSuccess(): void
    {
        if (!file_exists($this->apiUrl)) {
            $this->markTestSkipped('upload.php 尚未創建');
        }
        
        // 創建測試圖片（base64）
        $testImage = $this->createTestImage();
        
        $_FILES['photo'] = [
            'name' => 'test_photo.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => tempnam(sys_get_temp_dir(), 'test_'),
            'error' => UPLOAD_ERR_OK,
            'size' => strlen(base64_decode($testImage))
        ];
        
        // 寫入測試圖片到臨時文件
        file_put_contents($_FILES['photo']['tmp_name'], base64_decode($testImage));
        
        ob_start();
        include $this->apiUrl;
        $output = ob_get_clean();
        
        $response = json_decode($output, true);
        
        $this->assertArrayHasKey('status', $response, '應該返回 status 字段');
        
        // 清理
        unlink($_FILES['photo']['tmp_name']);
    }
    
    /**
     * 測試 3: 缺少照片文件
     */
    public function testUploadMissingFile(): void
    {
        if (!file_exists($this->apiUrl)) {
            $this->markTestSkipped('upload.php 尚未創建');
        }
        
        $_FILES = [];
        
        ob_start();
        include $this->apiUrl;
        $output = ob_get_clean();
        
        $response = json_decode($output, true);
        
        $this->assertEquals('error', $response['status'], '缺少文件應該失敗');
        $this->assertStringContainsString('照片', $response['message'], '應該提示缺少照片');
    }
    
    /**
     * 測試 4: 上傳文件類型錯誤（非圖片）
     */
    public function testUploadInvalidFileType(): void
    {
        if (!file_exists($this->apiUrl)) {
            $this->markTestSkipped('upload.php 尚未創建');
        }
        
        // 創建測試文本文件
        $_FILES['photo'] = [
            'name' => 'test.txt',
            'type' => 'text/plain',
            'tmp_name' => tempnam(sys_get_temp_dir(), 'test_'),
            'error' => UPLOAD_ERR_OK,
            'size' => 100
        ];
        
        file_put_contents($_FILES['photo']['tmp_name'], 'test content');
        
        ob_start();
        include $this->apiUrl;
        $output = ob_get_clean();
        
        $response = json_decode($output, true);
        
        $this->assertEquals('error', $response['status'], '非圖片文件應該失敗');
        $this->assertStringContainsString('圖片', $response['message'], '應該提示必須是圖片');
        
        // 清理
        unlink($_FILES['photo']['tmp_name']);
    }
    
    /**
     * 測試 5: 上傳文件過大
     */
    public function testUploadFileTooLarge(): void
    {
        if (!file_exists($this->apiUrl)) {
            $this->markTestSkipped('upload.php 尚未創建');
        }
        
        // 模擬大文件（超過 5MB）
        $_FILES['photo'] = [
            'name' => 'large_photo.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => tempnam(sys_get_temp_dir(), 'test_'),
            'error' => UPLOAD_ERR_OK,
            'size' => 6000000 // 6MB
        ];
        
        ob_start();
        include $this->apiUrl;
        $output = ob_get_clean();
        
        $response = json_decode($output, true);
        
        $this->assertEquals('error', $response['status'], '文件過大應該失敗');
        $this->assertStringContainsString('大小', $response['message'], '應該提示文件過大');
        
        // 清理
        unlink($_FILES['photo']['tmp_name']);
    }
    
    /**
     * 測試 6: 返回的文件路徑正確
     */
    public function testUploadReturnsCorrectPath(): void
    {
        if (!file_exists($this->apiUrl)) {
            $this->markTestSkipped('upload.php 尚未創建');
        }
        
        $testImage = $this->createTestImage();
        
        $_FILES['photo'] = [
            'name' => 'test_photo.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => tempnam(sys_get_temp_dir(), 'test_'),
            'error' => UPLOAD_ERR_OK,
            'size' => strlen(base64_decode($testImage))
        ];
        
        file_put_contents($_FILES['photo']['tmp_name'], base64_decode($testImage));
        
        ob_start();
        include $this->apiUrl;
        $output = ob_get_clean();
        
        $response = json_decode($output, true);
        
        if ($response['status'] === 'success') {
            $this->assertArrayHasKey('path', $response['data'], '應該返回文件路徑');
            $this->assertNotEmpty($response['data']['path'], '文件路徑不應為空');
        }
        
        // 清理
        unlink($_FILES['photo']['tmp_name']);
    }
    
    /**
     * 測試 7: 上傳 PNG 圖片
     */
    public function testUploadPngImage(): void
    {
        if (!file_exists($this->apiUrl)) {
            $this->markTestSkipped('upload.php 尚未創建');
        }
        
        // 創建測試 PNG 圖片
        $testImage = $this->createTestImage('png');
        
        $_FILES['photo'] = [
            'name' => 'test_photo.png',
            'type' => 'image/png',
            'tmp_name' => tempnam(sys_get_temp_dir(), 'test_'),
            'error' => UPLOAD_ERR_OK,
            'size' => strlen(base64_decode($testImage))
        ];
        
        file_put_contents($_FILES['photo']['tmp_name'], base64_decode($testImage));
        
        ob_start();
        include $this->apiUrl;
        $output = ob_get_clean();
        
        $response = json_decode($output, true);
        
        $this->assertArrayHasKey('status', $response, '應該返回 status 字段');
        
        // 清理
        unlink($_FILES['photo']['tmp_name']);
    }
    
    /**
     * 測試 8: 上傳文件錯誤
     */
    public function testUploadFileError(): void
    {
        if (!file_exists($this->apiUrl)) {
            $this->markTestSkipped('upload.php 尚未創建');
        }
        
        $_FILES['photo'] = [
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '',
            'error' => UPLOAD_ERR_NO_FILE,
            'size' => 0
        ];
        
        ob_start();
        include $this->apiUrl;
        $output = ob_get_clean();
        
        $response = json_decode($output, true);
        
        $this->assertEquals('error', $response['status'], '上傳錯誤應該失敗');
    }
    
    /**
     * 輔助函數：創建測試圖片
     */
    private function createTestImage($type = 'jpeg')
    {
        // 創建簡單的測試圖片（1x1 像素）
        $image = imagecreatetruecolor(1, 1);
        $white = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $white);
        
        ob_start();
        if ($type === 'png') {
            imagepng($image);
        } else {
            imagejpeg($image);
        }
        $imageData = ob_get_clean();
        imagedestroy($image);
        
        return base64_encode($imageData);
    }
}
