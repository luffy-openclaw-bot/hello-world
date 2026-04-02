<?php
/**
 * 作弊檢測工具類
 * 
 * 功能：
 * - GPS 欺騙檢測
 * - 時間異常檢測
 * - 設備指紋比對
 * - 風險評分計算
 */

class FraudDetection
{
    /**
     * 檢測 GPS 欺騙（位置跳躍）
     * 
     * @param array $prevLocation 上一個位置 ['lat' => x, 'lng' => y]
     * @param array $currLocation 當前位置 ['lat' => x, 'lng' => y]
     * @param int $timeDiff 時間差（秒）
     * @return array ['fraud' => bool, 'type' => string, 'score' => int]
     */
    public function detectGpsSpoof($prevLocation, $currLocation, $timeDiff)
    {
        $distance = $this->calculateDistance(
            $prevLocation['lat'],
            $prevLocation['lng'],
            $currLocation['lat'],
            $currLocation['lng']
        );
        
        // 計算速度（米/秒）
        $speed = $distance / $timeDiff;
        
        // 如果速度超過 100km/h (27.78 m/s)，可能是 GPS 欺騙
        if ($speed > 27.78) {
            return [
                'fraud' => true,
                'type' => 'gps_spoof',
                'score' => 90,
                'message' => '檢測到異常移動速度（可能為 GPS 欺騙）'
            ];
        }
        
        return [
            'fraud' => false,
            'type' => null,
            'score' => 0,
            'message' => 'GPS 位置正常'
        ];
    }
    
    /**
     * 檢測時間異常（早走/遲到）
     * 
     * @param string $expectedTime 預期時間（格式：HH:MM:SS）
     * @param string $actualTime 實際時間（格式：HH:MM:SS）
     * @param int $thresholdMinutes 允許誤差（分鐘）
     * @return array ['fraud' => bool, 'type' => string, 'score' => int]
     */
    public function detectTimeAnomaly($expectedTime, $actualTime, $thresholdMinutes = 30)
    {
        $expected = new DateTime($expectedTime);
        $actual = new DateTime($actualTime);
        
        $diff = $expected->diff($actual);
        $diffMinutes = ($diff->h * 60) + $diff->i;
        
        // 早走超過閾值
        if ($actual < $expected && $diffMinutes > $thresholdMinutes) {
            return [
                'fraud' => true,
                'type' => 'early_leave',
                'score' => 70,
                'message' => '檢測到早走行為'
            ];
        }
        
        return [
            'fraud' => false,
            'type' => null,
            'score' => 0,
            'message' => '時間正常'
        ];
    }
    
    /**
     * 檢測設備指紋不匹配
     * 
     * @param string $savedFingerprint 保存的設備指紋
     * @param string $currentFingerprint 當前設備指紋
     * @return array ['fraud' => bool, 'type' => string, 'score' => int]
     */
    public function detectDeviceMismatch($savedFingerprint, $currentFingerprint)
    {
        if ($savedFingerprint !== $currentFingerprint) {
            return [
                'fraud' => true,
                'type' => 'device_mismatch',
                'score' => 50,
                'message' => '設備指紋不匹配'
            ];
        }
        
        return [
            'fraud' => false,
            'type' => null,
            'score' => 0,
            'message' => '設備正常'
        ];
    }
    
    /**
     * 計算風險評分
     * 
     * @param array $factors 風險因素 ['gps_spoof' => bool, 'time_anomaly' => bool, ...]
     * @return int 風險評分 (0-100)
     */
    public function calculateRiskScore($factors)
    {
        $score = 0;
        
        if (isset($factors['gps_spoof']) && $factors['gps_spoof']) {
            $score += 40;
        }
        
        if (isset($factors['time_anomaly']) && $factors['time_anomaly']) {
            $score += 30;
        }
        
        if (isset($factors['device_mismatch']) && $factors['device_mismatch']) {
            $score += 20;
        }
        
        if (isset($factors['photo_missing']) && $factors['photo_missing']) {
            $score += 10;
        }
        
        return min($score, 100);
    }
    
    /**
     * 計算兩個 GPS 座標之間的距離（Haversine 公式）
     * 
     * @param float $lat1 緯度 1
     * @param float $lng1 經度 1
     * @param float $lat2 緯度 2
     * @param float $lng2 經度 2
     * @return float 距離（米）
     */
    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371000; // 地球半徑（米）
        
        $lat1Rad = deg2rad($lat1);
        $lng1Rad = deg2rad($lng1);
        $lat2Rad = deg2rad($lat2);
        $lng2Rad = deg2rad($lng2);
        
        $deltaLat = $lat2Rad - $lat1Rad;
        $deltaLng = $lng2Rad - $lng1Rad;
        
        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
             cos($lat1Rad) * cos($lat2Rad) *
             sin($deltaLng / 2) * sin($deltaLng / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }
}
