# 📍 GPS 打卡系統

> 一個基於 Web 的 GPS 打卡系統，讓員工可以通過瀏覽器打卡，管理員可以實時查看員工位置和檢測早走。

## 🎯 項目目標

- ✅ 無需安裝 App（Web App）
- ✅ GPS 定位打卡
- ✅ 實時 Dashboard
- ✅ 早走檢測
- ✅ 作弊檢測
- ✅ 低成本的解決方案

## 🛠️ 技術棧

### 前端
- React 18 + Vite
- Tailwind CSS
- Leaflet.js (地圖)

### 後端
- PHP 8.x
- MySQL 8.x
- JWT 認證

### 部署
- 前端：Vercel
- 後端：Shared Hosting

## 📁 項目結構

```
gps-checkin-webapp/
├── backend/          # PHP 後端
│   ├── api/         # API 端點
│   ├── includes/    # 工具函數
│   ├── uploads/     # 照片存儲
│   └── tests/       # PHPUnit 測試
├── frontend/         # React 前端
│   ├── src/
│   └── __tests__/   # Jest 測試
├── database/         # 數據庫 Schema
└── docs/            # 文檔
```

## 🚀 快速開始

### 後端設置

```bash
cd backend
composer install
cp .env.example .env
# 配置 MySQL 數據庫
```

### 前端設置

```bash
cd frontend
npm install
npm run dev
```

## 📋 功能清單

### 員工端
- [ ] 登入/登出
- [ ] GPS 打卡（上班/下班）
- [ ] 查看個人記錄
- [ ] 拍照上傳（可選）

### 管理員端
- [ ] Dashboard（實時查看員工位置）
- [ ] 用戶管理
- [ ] 報表導出
- [ ] 早走檢測
- [ ] 作弊檢測

## 🧪 TDD 開發

本项目採用 TDD (Test-Driven Development) 流程：

1. 🔴 Red:   先寫測試
2. 🟢 Green: 寫代碼讓測試通過
3. 🔵 Refactor: 重構代碼
4. 📝 Commit: 提交到 Git

詳細計劃請查看：[docs/TDD_PLAN.md](docs/TDD_PLAN.md)

## 📊 API 文檔

詳細 API 文檔請查看：[docs/API.md](docs/API.md)

## 💰 成本估算

| 項目 | 成本 |
|------|------|
| Shared Hosting | $600/年 |
| 域名 | $150/年 |
| 總計 | $750/年 |

## 📄 授權

© 2026 GPS Checkin WebApp

---

**最後更新：** 2026-04-01
