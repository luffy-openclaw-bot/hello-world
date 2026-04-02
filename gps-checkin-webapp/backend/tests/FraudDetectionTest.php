<?php
/**
 * 作弊檢測 API 測試
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../includes/fraud_detection.php';

class FraudDetectionTest extends TestCase
{
    private $fraudDetector;
    
    protected function setUp(): void
    {
        $this->fraudDetector = new FraudDetection();
    }
    
    public function testFraudDetectionClassExists(): void
    {
        $this->assertTrue(class_exists('FraudDetection'));
    }
    
    public function testDetectGpsSpoof(): void
    {
        // 正常移動（步行速度）
        $result = $this->fraudDetector->detectGpsSpoof(
            ['lat' => 22.3193, 'lng' => 114.1694],
            ['lat' => 22.3203, 'lng' => 114.1704],
            300 // 5 分鐘
        );
        
        $this->assertFalse($result['fraud']);
        
        // 異常移動（飛機速度）
        $result = $this->fraudDetector->detectGpsSpoof(
            ['lat' => 22.3193, 'lng' => 114.1694],
            ['lat' => 23.3193, 'lng' => 115.1694], // 100km 外
            300 // 5 分鐘
        );
        
        $this->assertTrue($result['fraud']);
        $this->assertEquals('gps_spoof', $result['type']);
    }
    
    public function testDetectTimeAnomaly(): void
    {
        // 正常時間
        $result = $this->fraudDetector->detectTimeAnomaly('09:00:00', '09:00:00', 30);
        $this->assertFalse($result['fraud']);
        
        // 早走超過 30 分鐘
        $result = $this->fraudDetector->detectTimeAnomaly('17:00:00', '16:00:00', 30);
        $this->assertTrue($result['fraud']);
        $this->assertEquals('early_leave', $result['type']);
    }
    
    public function testCalculateRiskScore(): void
    {
        $factors = [
            'gps_spoof' => true,
            'time_anomaly' => false,
            'device_mismatch' => false
        ];
        
        $score = $this->fraudDetector->calculateRiskScore($factors);
        $this->assertGreaterThan(50, $score);
    }
}
