# 📋 TDD 開發計劃

## 🎯 項目目標

開發一個 GPS 打卡 Web App，讓員工可以通過瀏覽器打卡，管理員可以查看員工位置和檢測早走。

---

## 📅 開發時程

| Phase | 內容 | 預計時間 |
|-------|------|----------|
| Phase 1 | 項目設置 + 測試框架 | Day 1 |
| Phase 2 | 後端 PHP + MySQL (TDD) | Day 2-3 |
| Phase 3 | 前端 React SPA (TDD) | Day 4-5 |
| Phase 4 | 部署 + Demo 準備 | Day 6-7 |

---

## 🔴🟢🔵 TDD 流程

每個功能都遵循以下流程：

```
1. 🔴 Red:   先寫測試 (會失敗)
2. 🟢 Green: 寫代碼 (讓測試通過)
3. 🔵 Refactor: 重構代碼 (優化)
4. 📝 Commit: 提交到 Git
```

---

## 📝 Phase 1: 項目設置 (Day 1)

### Task 1.1: 創建項目結構
- [x] 創建文件夾結構
- [ ] Git Commit: `feat: 創建項目結構`

### Task 1.2: 初始化 Git
- [x] git init
- [x] 創建 .gitignore
- [ ] Git Commit: `chore: 初始化 Git 倉庫`

### Task 1.3: 創建文檔
- [ ] README.md
- [ ] REQUIREMENTS.md
- [ ] API.md
- [ ] Git Commit: `docs: 創建項目文檔`

### Task 1.4: 數據庫 Schema
- [ ] 🔴 測試：test_database_schema.php
- [ ] 🟢 代碼：schema.sql
- [ ] Git Commit: `feat: 創建數據庫 Schema`

### Task 1.5: PHPUnit 測試框架
- [ ] 🔴 測試：運行 phpunit
- [ ] 🟢 代碼：安裝 PHPUnit + 配置
- [ ] Git Commit: `chore: 安裝 PHPUnit 測試框架`

---

## 📝 Phase 2: 後端開發 (Day 2-3)

### Task 2.1: 用戶登入 API
- [ ] 🔴 測試：test_login.php
  - [ ] 測試登入成功
  - [ ] 測試登入失敗（錯誤密碼）
  - [ ] 測試登入失敗（用戶不存在）
- [ ] 🟢 代碼：login.php + auth.php
- [ ] Git Commit: `feat: 實現登入 API`

### Task 2.2: JWT 認證
- [ ] 🔴 測試：test_auth.php
  - [ ] 測試 Token 生成
  - [ ] 測試 Token 驗證
  - [ ] 測試 Token 過期
- [ ] 🟢 代碼：JWT 工具類
- [ ] Git Commit: `feat: 實現 JWT 認證`

### Task 2.3: GPS 打卡 API
- [ ] 🔴 測試：test_checkin.php
  - [ ] 測試上班打卡
  - [ ] 測試下班打卡
  - [ ] 測試無效 GPS
- [ ] 🟢 代碼：checkin.php
- [ ] Git Commit: `feat: 實現 GPS 打卡 API`

### Task 2.4: 照片上傳
- [ ] 🔴 測試：test_upload.php
  - [ ] 測試照片上傳
  - [ ] 測試文件大小限制
  - [ ] 測試文件類型驗證
- [ ] 🟢 代碼：upload.php
- [ ] Git Commit: `feat: 實現照片上傳功能`

### Task 2.5: 作弊檢測
- [ ] 🔴 測試：test_fraud.php
  - [ ] 測試 GPS 欺騙檢測
  - [ ] 測試時間異常檢測
  - [ ] 測試設備指紋
- [ ] 🟢 代碼：fraud_detection.php
- [ ] Git Commit: `feat: 實現作弊檢測`

---

## 📝 Phase 3: 前端開發 (Day 4-5)

### Task 3.1: React 項目設置
- [ ] 🔴 測試：App.test.jsx
- [ ] 🟢 代碼：React + Vite + Tailwind
- [ ] Git Commit: `feat: 初始化 React 項目`

### Task 3.2: 登入頁面
- [ ] 🔴 測試：Login.test.jsx
- [ ] 🟢 代碼：Login.jsx
- [ ] Git Commit: `feat: 實現登入頁面`

### Task 3.3: 打卡頁面
- [ ] 🔴 測試：Checkin.test.jsx
- [ ] 🟢 代碼：Checkin.jsx
- [ ] Git Commit: `feat: 實現打卡頁面`

### Task 3.4: Dashboard
- [ ] 🔴 測試：Dashboard.test.jsx
- [ ] 🟢 代碼：Dashboard.jsx
- [ ] Git Commit: `feat: 實現 Dashboard`

---

## 📝 Phase 4: 部署 + Demo (Day 6-7)

### Task 4.1: 部署配置
- [ ] 🔴 測試：部署腳本
- [ ] 🟢 代碼：部署配置
- [ ] Git Commit: `chore: 添加部署配置`

### Task 4.2: Demo 腳本
- [ ] 🔴 測試：完整 Demo 流程
- [ ] 🟢 代碼：Demo 腳本文檔
- [ ] Git Commit: `docs: 添加 Demo 腳本`

---

## 📊 測試覆蓋率目標

| 組件 | 目標覆蓋率 |
|------|------------|
| 後端 API | 80%+ |
| 前端組件 | 70%+ |
| 核心邏輯 | 90%+ |

---

## 📝 Commit Message 規範

```
feat: 新功能
fix: 修復 Bug
test: 添加測試
chore: 配置/文檔
refactor: 重構代碼
docs: 文檔更新
```

---

**最後更新：** 2026-04-01
