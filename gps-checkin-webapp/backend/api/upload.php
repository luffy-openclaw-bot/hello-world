<?php
/**
 * 照片上傳 API
 * 
 * 請求方法：POST
 * 請求頭：Authorization: Bearer {token}
 * 請求參數：
 *   - photo: 照片文件（multipart/form-data）
 * 
 * 響應格式：
 * {
 *   "status": "success|error",
 *   "message": "提示信息",
 *   "data": {
 *     "path": "文件路徑",
 *     "url": "訪問 URL",
 *     "size": 文件大小
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
require_once __DIR__ . '/../includes/auth.php';

// 驗證授權 Token（可選，如果允許未登入用戶上傳）
$authHeader = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : '';

if (!empty($authHeader)) {
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
}

// 檢查是否有上傳文件
if (!isset($_FILES['photo'])) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => '請上傳照片文件'
    ]);
    exit;
}

$photo = $_FILES['photo'];

// 檢查上傳錯誤
if ($photo['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => '文件超過 php.ini 的 upload_max_filesize 限制',
        UPLOAD_ERR_FORM_SIZE => '文件超過表單的 MAX_FILE_SIZE 限制',
        UPLOAD_ERR_PARTIAL => '文件只有部分被上傳',
        UPLOAD_ERR_NO_FILE => '沒有文件被上傳',
        UPLOAD_ERR_NO_TMP_DIR => '找不到臨時文件夾',
        UPLOAD_ERR_CANT_WRITE => '文件寫入失敗',
        UPLOAD_ERR_EXTENSION => 'PHP 擴展阻止了文件上傳'
    ];
    
    $errorMessage = isset($errorMessages[$photo['error']]) 
        ? $errorMessages[$photo['error']] 
        : '上傳失敗';
    
    echo json_encode([
        'status' => 'error',
        'message' => $errorMessage
    ]);
    exit;
}

// 驗證文件類型
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
if (!in_array($photo['type'], $allowedTypes)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => '只允許上傳圖片文件（JPG, PNG, GIF）'
    ]);
    exit;
}

// 驗證文件大小（最大 5MB）
$maxSize = 5 * 1024 * 1024; // 5MB
if ($photo['size'] > $maxSize) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => '文件大小不能超過 5MB'
    ]);
    exit;
}

// 驗證文件確實是圖片
$imageInfo = getimagesize($photo['tmp_name']);
if ($imageInfo === false) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => '文件不是有效的圖片'
    ]);
    exit;
}

// 創建上傳目錄
$uploadDir = __DIR__ . '/../uploads/checkins/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// 生成唯一文件名
$extension = pathinfo($photo['name'], PATHINFO_EXTENSION);
$filename = uniqid('checkin_') . '.' . $extension;
$targetPath = $uploadDir . $filename;

// 移動上傳文件
if (move_uploaded_file($photo['tmp_name'], $targetPath)) {
    // 上傳成功
    $relativePath = 'uploads/checkins/' . $filename;
    
    echo json_encode([
        'status' => 'success',
        'message' => '照片上傳成功',
        'data' => [
            'path' => $relativePath,
            'url' => '/' . $relativePath,
            'size' => $photo['size'],
            'type' => $photo['type'],
            'filename' => $filename
        ]
    ]);
} else {
    // 上傳失敗
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => '照片上傳失敗，請重試'
    ]);
}
