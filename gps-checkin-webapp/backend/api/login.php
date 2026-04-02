<?php
/**
 * 用戶登入 API
 * 
 * 請求方法：POST
 * 請求參數：
 *   - email: 用戶郵箱
 *   - password: 用戶密碼
 * 
 * 響應格式：
 * {
 *   "status": "success|error",
 *   "message": "提示信息",
 *   "data": {
 *     "token": "JWT Token",
 *     "user": {
 *       "id": 用戶 ID,
 *       "name": 姓名,
 *       "email": 郵箱,
 *       "role": 角色
 *     }
 *   }
 * }
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

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

// 獲取 POST 數據
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// 驗證輸入
if (empty($email)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => '請輸入郵箱'
    ]);
    exit;
}

if (empty($password)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => '請輸入密碼'
    ]);
    exit;
}

try {
    // 連接數據庫
    $db = getDB();
    
    // 查詢用戶
    $stmt = $db->prepare("SELECT id, name, email, password_hash, role, status FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 用戶不存在
    if (!$user) {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => '用戶不存在'
        ]);
        exit;
    }
    
    // 驗證密碼
    if (!password_verify($password, $user['password_hash'])) {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => '密碼錯誤'
        ]);
        exit;
    }
    
    // 檢查用戶狀態
    if ($user['status'] != 1) {
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'message' => '賬戶已被禁用'
        ]);
        exit;
    }
    
    // 生成 JWT Token
    $auth = new Auth();
    $token = $auth->generateToken([
        'user_id' => $user['id'],
        'email' => $user['email'],
        'role' => $user['role']
    ]);
    
    // 移除密碼哈希
    unset($user['password_hash']);
    
    // 登入成功
    echo json_encode([
        'status' => 'success',
        'message' => '登入成功',
        'data' => [
            'token' => $token,
            'user' => $user
        ]
    ]);
    
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
