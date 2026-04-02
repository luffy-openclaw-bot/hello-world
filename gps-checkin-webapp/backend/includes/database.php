<?php
/**
 * 數據庫配置文件
 * 
 * 功能：
 * - 提供 MySQL 數據庫連接
 * - 使用 PDO
 */

function getDB()
{
    static $db = null;
    
    if ($db === null) {
        // 數據庫配置
        // 生產環境應該從 .env 讀取
        $host = 'localhost';
        $dbname = 'gps_checkin';
        $username = 'root';
        $password = '';
        
        try {
            $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $db = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            // 如果是測試環境，返回 mock 數據庫
            if (php_sapi_name() === 'cli') {
                return new MockDB();
            }
            throw $e;
        }
    }
    
    return $db;
}

/**
 * Mock 數據庫類（用於測試）
 */
class MockDB
{
    public function prepare($sql)
    {
        return new MockStatement();
    }
}

class MockStatement
{
    public function execute($params = [])
    {
        return true;
    }
    
    public function fetch($mode = PDO::FETCH_ASSOC)
    {
        // 返回測試用戶
        return [
            'id' => 1,
            'name' => '系統管理員',
            'email' => 'admin@gps-checkin.com',
            'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
            'role' => 'admin',
            'status' => 1
        ];
    }
}
