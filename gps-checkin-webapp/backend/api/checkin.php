<?php
/**
 * GPS 打卡 API
 * 
 * 請求方法：POST
 * 請求頭：Authorization: Bearer {token}
 * 請求參數：
 *   - latitude: 緯度（必填，-90 到 90）
 *   - longitude: 經度（必填，-180 到 180）
 *   - type: 打卡類型（checkin 或 checkout）
 *   - photo: 照片（可選，base64）
 *   - note: 備註（可選）
 * 
 * 響應格式：
 * {
 *   "status": "success|error",
 *   "message": "提示信息",
 *   "data": {
 *     "id": 打卡記錄 ID,
 *     "checkin_time": 上班時間,
 *     "location": {
 *       "latitude": 緯度,
 *       "longitude": 經度
 *     }
 *   }
 * }
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// 只接受 POST 請求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => '只接受 POST 請求'
    ]);
    exit;
}

// 引入配置文件
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';

// 驗證授權 Token
$authHeader = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : '';

if (empty($authHeader)) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => '缺少授權 Token'
    ]);
    exit;
}

// 提取 Token
$token = str_replace('Bearer ', '', $authHeader);

$auth = new Auth();
if (!$auth->verifyToken($token)) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => '無效的授權 Token'
    ]);
    exit;
}

// 解碼 Token 獲取用戶信息
$tokenData = $auth->decodeToken($token);
$userId = $tokenData['user_id'] ?? 0;

if (!$userId) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => '無效的用戶信息'
    ]);
    exit;
}

// 獲取並驗證 POST 數據
$latitude = isset($_POST['latitude']) ? floatval($_POST['latitude']) : null;
$longitude = isset($_POST['longitude']) ? floatval($_POST['longitude']) : null;
$type = isset($_POST['type']) ? trim($_POST['type']) : null;
$photo = isset($_POST['photo']) ? $_POST['photo'] : null;
$note = isset($_POST['note']) ? trim($_POST['note']) : null;

// 驗證緯度
if ($latitude === null) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => '請提供緯度'
    ]);
    exit;
}

if ($latitude < -90 || $latitude > 90) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => '緯度無效（應該在 -90 到 90 之間）'
    ]);
    exit;
}

// 驗證經度
if ($longitude === null) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => '請提供經度'
    ]);
    exit;
}

if ($longitude < -180 || $longitude > 180) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => '經度無效（應該在 -180 到 180 之間）'
    ]);
    exit;
}

// 驗證打卡類型
if (empty($type)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => '請提供打卡類型（checkin 或 checkout）'
    ]);
    exit;
}

if (!in_array($type, ['checkin', 'checkout'])) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => '無效的打卡類型'
    ]);
    exit;
}

try {
    // 連接數據庫
    $db = getDB();
    
    // 獲取設備指紋
    $deviceFingerprint = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'unknown';
    
    if ($type === 'checkin') {
        // 上班打卡
        $stmt = $db->prepare("
            INSERT INTO checkins (user_id, latitude, longitude, checkin_time, photo_path, device_fingerprint, note)
            VALUES (:user_id, :latitude, :longitude, NOW(), :photo_path, :device_fingerprint, :note)
        ");
        
        $stmt->execute([
            ':user_id' => $userId,
            ':latitude' => $latitude,
            ':longitude' => $longitude,
            ':photo_path' => $photo ? 'uploads/checkins/' . uniqid() . '.jpg' : null,
            ':device_fingerprint' => $deviceFingerprint,
            ':note' => $note
        ]);
        
        $checkinId = $db->lastInsertId();
        
        // 如果照片存在，保存照片
        if ($photo) {
            $photoPath = __DIR__ . '/../uploads/checkins/' . $checkinId . '.jpg';
            $photoData = str_replace('data:image/jpeg;base64,', '', $photo);
            $photoData = str_replace('data:image/png;base64,', '', $photoData);
            file_put_contents($photoPath, base64_decode($photoData));
        }
        
        echo json_encode([
            'status' => 'success',
            'message' => '上班打卡成功',
            'data' => [
                'id' => $checkinId,
                'checkin_time' => date('Y-m-d H:i:s'),
                'location' => [
                    'latitude' => $latitude,
                    'longitude' => $longitude
                ]
            ]
        ]);
        
    } else {
        // 下班打卡
        $stmt = $db->prepare("
            UPDATE checkins
            SET checkout_time = NOW()
            WHERE user_id = :user_id
            AND DATE(checkin_time) = CURDATE()
            AND checkout_time IS NULL
        ");
        
        $stmt->execute([
            ':user_id' => $userId
        ]);
        
        if ($stmt->rowCount() === 0) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => '未找到今日的上班打卡記錄'
            ]);
            exit;
        }
        
        echo json_encode([
            'status' => 'success',
            'message' => '下班打卡成功',
            'data' => [
                'checkout_time' => date('Y-m-d H:i:s')
            ]
        ]);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => '數據庫錯誤：' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => '服務器錯誤：' . $e->getMessage()
    ]);
}
