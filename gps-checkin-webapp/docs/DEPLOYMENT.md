# GPS Checkin WebApp 部署文檔

## 📋 部署選項

### 選項 1: Shared Hosting (推薦 - 低成本)

**適合：** 小型項目，預算有限

**成本：** ~$600/年

**步驟：**

1. **購買 Shared Hosting**
   - SiteGround / Bluehost / HostGator
   - 選擇：5GB+ 空間，MySQL 數據庫

2. **上傳後端文件**
   ```bash
   # 使用 FTP 或 cPanel File Manager
   上傳 backend/ 到 /public_html/backend/
   ```

3. **配置數據庫**
   - 在 cPanel 創建 MySQL 數據庫
   - 創建用戶並授權
   - 導入 schema.sql

4. **修改配置**
   ```php
   // backend/includes/database.php
   $host = 'localhost';
   $dbname = 'your_database';
   $username = 'your_username';
   $password = 'your_password';
   ```

5. **部署前端**
   ```bash
   cd frontend
   npm run build
   上傳 dist/ 到 /public_html/
   ```

---

### 選項 2: Vercel + Railway (現代方案)

**適合：** 追求現代技術棧

**成本：** 免費層夠用

**步驟：**

1. **前端部署到 Vercel**
   ```bash
   cd frontend
   vercel deploy
   ```

2. **後端部署到 Railway**
   ```bash
   cd backend
   # 連接 GitHub Repo
   # Railway 自動部署
   ```

3. **數據庫使用 Railway MySQL**
   - Railway 提供免費 MySQL
   - 連接字符串配置到後端

---

### 選項 3: GitHub Pages (靜態前端)

**適合：** Demo/測試

**成本：** 免費

**步驟：**

1. **構建前端**
   ```bash
   cd frontend
   npm run build
   ```

2. **部署到 GitHub Pages**
   ```bash
   # 使用 gh-pages 包
   npm install -D gh-pages
   npx gh-pages -d dist
   ```

3. **後端仍需 Shared Hosting**

---

## 🔧 部署後配置

### 1. 環境變量

```php
// backend/includes/config.php
define('API_URL', 'https://your-domain.com/backend/api');
define('JWT_SECRET', 'your-secret-key');
define('UPLOAD_DIR', '/path/to/uploads');
```

### 2. 數據庫導入

```bash
mysql -u username -p database_name < database/schema.sql
```

### 3. 設置權限

```bash
chmod 755 backend/uploads/checkins/
chmod 644 backend/api/*.php
```

### 4. 測試

- [ ] 登入功能
- [ ] GPS 打卡
- [ ] 照片上傳
- [ ] Dashboard
- [ ] 報表導出

---

## 📱 Demo 準備

### Demo 腳本

1. **登入頁面**
   - 展示簡潔 UI
   - 使用 Demo 賬戶登入

2. **員工打卡**
   - 獲取 GPS 位置
   - 上班打卡
   - 展示成功提示

3. **管理員 Dashboard**
   - 查看統計數據
   - 查看員工位置（地圖）
   - 查看打卡記錄

4. **報表系統**
   - 日期篩選
   - 導出 CSV

### Demo 賬戶

```
管理員：
Email: admin@gps-checkin.com
Password: admin123

員工：
Email: employee@gps-checkin.com
Password: employee123
```

---

## 🎯 上線檢查清單

- [ ] 域名配置
- [ ] HTTPS 證書
- [ ] 數據庫連接
- [ ] 文件上傳權限
- [ ] API 測試
- [ ] 前端測試
- [ ] 移動端測試
- [ ] 性能測試
- [ ] 安全檢查

---

**最後更新：** 2026-04-01
