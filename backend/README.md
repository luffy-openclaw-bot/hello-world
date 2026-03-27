# 🌉 Ollama Bridge API

作為前端同 Ollama API 之間嘅橋樑，解決 CORS 問題。

---

## **📁 文件結構**

```
backend/
├── api/
│   └── index.js      # Express API 服務器
├── package.json      # 依賴配置
├── .env.example      # 環境變量範本
├── vercel.json       # Vercel 部署配置
└── README.md         # 呢個文件
```

---

## **🚀 部署方法**

### **方法 1: Vercel (推薦 - 免費)**

**步驟：**

1. **安裝 Vercel CLI**
   ```bash
   npm install -g vercel
   ```

2. **登入 Vercel**
   ```bash
   vercel login
   ```

3. **部署**
   ```bash
   cd backend
   vercel
   ```

4. **生產部署**
   ```bash
   vercel --prod
   ```

**部署後：**
- 你會得到一個 URL：`https://your-project.vercel.app`
- API 端點：`https://your-project.vercel.app/api/generate`

---

### **方法 2: Render (免費)**

**步驟：**

1. 上 https://render.com
2. 創建 **Web Service**
3. 連接呢個 GitHub 倉庫
4. Root Directory: `backend`
5. Build Command: `npm install`
6. Start Command: `npm start`
7. 添加環境變量：
   - `OLLAMA_API_KEY`
   - `OLLAMA_MODEL`

---

### **方法 3: 本地運行 (開發用)**

**步驟：**

1. **安裝依賴**
   ```bash
   cd backend
   npm install
   ```

2. **複製環境變量**
   ```bash
   cp .env.example .env
   ```

3. **啟動服務器**
   ```bash
   npm start
   ```

4. **測試**
   ```bash
   curl http://localhost:3000/health
   ```

---

## **📡 API 端點**

### **1. Health Check**
```
GET /health
```

**回應：**
```json
{
  "status": "ok",
  "timestamp": "2026-03-27T08:00:00.000Z",
  "service": "ollama-bridge-api"
}
```

---

### **2. API 資訊**
```
GET /api/info
```

**回應：**
```json
{
  "name": "Ollama Bridge API",
  "version": "1.0.0",
  "model": "qwen3.5:cloud",
  "endpoint": "https://ollama.com/api/generate"
}
```

---

### **3. 生成回應 (主要接口)**
```
POST /api/generate
```

**請求：**
```json
{
  "prompt": "用一句廣東話打招呼",
  "system": "你係一個友好嘅 AI 助手",
  "model": "qwen3.5:cloud",
  "stream": false
}
```

**回應：**
```json
{
  "success": true,
  "response": "你好！有咩可以幫到你？",
  "model": "qwen3.5:cloud",
  "timestamp": "2026-03-27T08:00:00.000Z"
}
```

---

### **4. 對話測試**
```
POST /api/chat
```

**請求：**
```json
{
  "messages": [
    "AI 會取代人類工作嗎？",
    "你點睇 AI 嘅未來？",
    "AI 應該有道德標準嗎？"
  ],
  "system": "你係一個友好嘅 AI 助手。用廣東話回覆，保持簡潔。"
}
```

**回應：**
```json
{
  "success": true,
  "results": [
    {
      "prompt": "AI 會取代人類工作嗎？",
      "response": "..."
    },
    {
      "prompt": "你點睇 AI 嘅未來？",
      "response": "..."
    }
  ],
  "timestamp": "2026-03-27T08:00:00.000Z"
}
```

---

## **🔒 安全提示**

### **API Key 保護**

- ✅ **後端存儲** - API Key 存儲喺後端環境變量
- ✅ **前端唔需要知道** - 前端只請求後端，唔直接接觸 API Key
- ✅ **HTTPS** - 部署後使用 HTTPS 加密傳輸

### **CORS 配置**

目前配置係 `origin: '*'` (允許所有來源)

**生產環境建議：**
```javascript
app.use(cors({
    origin: 'https://luffy-openclaw-bot.github.io',
    methods: ['GET', 'POST', 'OPTIONS'],
    allowedHeaders: ['Content-Type', 'Authorization']
}));
```

---

## **🧪 測試**

### **使用 cURL 測試**

```bash
# Health Check
curl https://your-project.vercel.app/health

# 生成回應
curl -X POST https://your-project.vercel.app/api/generate \
  -H "Content-Type: application/json" \
  -d '{
    "prompt": "用一句廣東話打招呼",
    "system": "你係一個友好嘅 AI 助手"
  }'
```

### **使用前端測試**

更新前端 HTML 中嘅 API 端點為後端 URL：

```javascript
const BACKEND_API_URL = 'https://your-project.vercel.app/api/generate';
```

---

## **📊 部署狀態**

| 服務 | 狀態 | URL |
|------|------|-----|
| Vercel | ⏳ 待部署 | - |
| Render | ⏳ 待部署 | - |

---

## **🐛 常見問題**

### **Q: 點解唔直接用 CORS Proxy？**

A: 公共 CORS Proxy 服務：
- 唔穩定 (隨時關門)
- 可能被阻止 (好似 Ollama)
- 無隱私 (所有請求都經過第三方)

後端 API 作為橋樑：
- ✅ 完全控制
- ✅ 保護 API Key
- ✅ 可以加認證、限流等功能
- ✅ 穩定可靠

### **Q: 部署到 Vercel 要錢嗎？**

A: 唔使！Vercel 有免費方案：
- 100GB 流量/月
- 自動 HTTPS
- 自動部署
- 對於個人項目完全夠用

### **Q: 可唔可以部署到其他平台？**

A: 可以！支持：
- Vercel (推薦)
- Render
- Railway
- Heroku
- 任何支持 Node.js 嘅平台

---

## **📝 更新日誌**

- **2026-03-27:** 初始版本
  - Express API 服務器
  - Vercel 部署配置
  - Health Check 接口
  - /api/generate 接口
  - /api/chat 接口

---

## **📄 License**

MIT License
