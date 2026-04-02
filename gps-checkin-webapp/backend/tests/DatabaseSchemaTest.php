<?php
/**
 * 數據庫 Schema 測試
 * 
 * TDD 流程：
 * 1. 🔴 這個測試會失敗（因為 schema.sql 還未創建）
 * 2. 🟢 創建 schema.sql 讓測試通過
 * 3. 🔵 重構（如有需要）
 */

use PHPUnit\Framework\TestCase;

class DatabaseSchemaTest extends TestCase
{
    private $schemaFile;
    
    protected function setUp(): void
    {
        $this->schemaFile = __DIR__ . '/../../database/schema.sql';
    }
    
    /**
     * 測試 1: schema.sql 文件是否存在
     */
    public function testSchemaFileExists(): void
    {
        $this->assertFileExists(
            $this->schemaFile,
            "schema.sql 文件不存在，請創建 database/schema.sql"
        );
    }
    
    /**
     * 測試 2: schema.sql 文件不為空
     */
    public function testSchemaFileNotEmpty(): void
    {
        if (!file_exists($this->schemaFile)) {
            $this->markTestSkipped('schema.sql 文件不存在');
        }
        
        $content = file_get_contents($this->schemaFile);
        $this->assertNotEmpty(
            trim($content),
            "schema.sql 文件為空，請添加 SQL 內容"
        );
    }
    
    /**
     * 測試 3: 包含 users 表
     */
    public function testUsersTableExists(): void
    {
        if (!file_exists($this->schemaFile)) {
            $this->markTestSkipped('schema.sql 文件不存在');
        }
        
        $content = file_get_contents($this->schemaFile);
        $this->assertStringContainsString(
            'CREATE TABLE users',
            strtoupper($content),
            "schema.sql 必須包含 users 表"
        );
    }
    
    /**
     * 測試 4: 包含 checkins 表
     */
    public function testCheckinsTableExists(): void
    {
        if (!file_exists($this->schemaFile)) {
            $this->markTestSkipped('schema.sql 文件不存在');
        }
        
        $content = file_get_contents($this->schemaFile);
        $this->assertStringContainsString(
            'CREATE TABLE checkins',
            strtoupper($content),
            "schema.sql 必須包含 checkins 表"
        );
    }
    
    /**
     * 測試 5: users 表包含必要字段
     */
    public function testUsersTableHasRequiredFields(): void
    {
        if (!file_exists($this->schemaFile)) {
            $this->markTestSkipped('schema.sql 文件不存在');
        }
        
        $content = file_get_contents($this->schemaFile);
        
        $requiredFields = [
            'id',
            'name',
            'email',
            'password_hash',
            'role',
            'created_at'
        ];
        
        foreach ($requiredFields as $field) {
            $this->assertStringContainsString(
                strtoupper($field),
                strtoupper($content),
                "users 表必須包含字段：$field"
            );
        }
    }
    
    /**
     * 測試 6: checkins 表包含必要字段
     */
    public function testCheckinsTableHasRequiredFields(): void
    {
        if (!file_exists($this->schemaFile)) {
            $this->markTestSkipped('schema.sql 文件不存在');
        }
        
        $content = file_get_contents($this->schemaFile);
        
        $requiredFields = [
            'id',
            'user_id',
            'latitude',
            'longitude',
            'checkin_time',
            'created_at'
        ];
        
        foreach ($requiredFields as $field) {
            $this->assertStringContainsString(
                strtoupper($field),
                strtoupper($content),
                "checkins 表必須包含字段：$field"
            );
        }
    }
    
    /**
     * 測試 7: 包含 fraud_logs 表（作弊檢測）
     */
    public function testFraudLogsTableExists(): void
    {
        if (!file_exists($this->schemaFile)) {
            $this->markTestSkipped('schema.sql 文件不存在');
        }
        
        $content = file_get_contents($this->schemaFile);
        $this->assertStringContainsString(
            'CREATE TABLE fraud_logs',
            strtoupper($content),
            "schema.sql 必須包含 fraud_logs 表"
        );
    }
}
