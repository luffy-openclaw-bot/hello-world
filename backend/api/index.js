/**
 * Ollama Bridge API
 * 
 * 作為前端同 Ollama API 之間嘅橋樑
 * 解決 CORS 問題，同時保護 API Key
 */

const express = require('express');
const cors = require('cors');
require('dotenv').config();

const app = express();
const PORT = process.env.PORT || 3000;

// Ollama API 配置
const OLLAMA_API_KEY = process.env.OLLAMA_API_KEY || '24eb99cd231348d28cab9f2f6c5fe656.y3hVb_bR0H8eNh4IUKmX9sSh';
const OLLAMA_MODEL = process.env.OLLAMA_MODEL || 'qwen3.5:cloud';
const OLLAMA_API_URL = 'https://ollama.com/api/generate';

// 中間件
app.use(cors({
    origin: '*', // 允許所有來源 (GitHub Pages)
    methods: ['GET', 'POST', 'OPTIONS'],
    allowedHeaders: ['Content-Type', 'Authorization']
}));

app.use(express.json());

// Health Check
app.get('/health', (req, res) => {
    res.json({
        status: 'ok',
        timestamp: new Date().toISOString(),
        service: 'ollama-bridge-api'
    });
});

// API 資訊
app.get('/api/info', (req, res) => {
    res.json({
        name: 'Ollama Bridge API',
        version: '1.0.0',
        model: OLLAMA_MODEL,
        endpoint: OLLAMA_API_URL
    });
});

/**
 * POST /api/generate
 * 
 * 代理請求到 Ollama API
 */
app.post('/api/generate', async (req, res) => {
    try {
        const { prompt, system, model = OLLAMA_MODEL, stream = false } = req.body;
        
        if (!prompt) {
            return res.status(400).json({
                error: 'Missing required field: prompt'
            });
        }
        
        console.log(`[${new Date().toISOString()}] 收到請求:`, {
            model,
            promptLength: prompt.length,
            hasSystem: !!system
        });
        
        // 設置 30 秒超時
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 30000);
        
        try {
            // 請求 Ollama API
            const response = await fetch(OLLAMA_API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${OLLAMA_API_KEY}`
                },
                body: JSON.stringify({
                    model,
                    prompt,
                    system: system || '',
                    stream
                }),
                signal: controller.signal
            });
            
            clearTimeout(timeoutId);
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error(`Ollama API 錯誤 (${response.status}):`, errorText);
                
                return res.status(response.status).json({
                    error: `Ollama API Error: ${response.status}`,
                    details: errorText
                });
            }
            
            const data = await response.json();
            
            console.log(`[${new Date().toISOString()}] 請求成功`);
            
            res.json({
                success: true,
                response: data.response || data.text || '',
                model,
                timestamp: new Date().toISOString()
            });
            
        } catch (error) {
            clearTimeout(timeoutId);
            
            if (error.name === 'AbortError') {
                console.error('Ollama API 請求超時 (30 秒)');
                return res.status(504).json({
                    error: 'Gateway Timeout',
                    message: 'Ollama API 請求超時 (超過 30 秒)，請稍後再試。'
                });
            }
            
            throw error;
        }
        
    } catch (error) {
        console.error('伺服器錯誤:', error);
        
        res.status(500).json({
            error: 'Internal Server Error',
            message: error.message
        });
    }
});

/**
 * POST /api/chat
 * 
 * 對話測試接口 (連續問多個問題)
 */
app.post('/api/chat', async (req, res) => {
    try {
        const { messages = [], system = '' } = req.body;
        
        if (!messages || messages.length === 0) {
            return res.status(400).json({
                error: 'Missing required field: messages'
            });
        }
        
        const results = [];
        
        for (const message of messages) {
            const response = await fetch(OLLAMA_API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${OLLAMA_API_KEY}`
                },
                body: JSON.stringify({
                    model: OLLAMA_MODEL,
                    prompt: message,
                    system,
                    stream: false
                })
            });
            
            if (!response.ok) {
                throw new Error(`Ollama API Error: ${response.status}`);
            }
            
            const data = await response.json();
            results.push({
                prompt: message,
                response: data.response || data.text || ''
            });
            
            // 等待 500ms 先問下一個問題
            await new Promise(resolve => setTimeout(resolve, 500));
        }
        
        res.json({
            success: true,
            results,
            timestamp: new Date().toISOString()
        });
        
    } catch (error) {
        console.error('對話錯誤:', error);
        
        res.status(500).json({
            error: 'Internal Server Error',
            message: error.message
        });
    }
});

// 404 Handler
app.use((req, res) => {
    res.status(404).json({
        error: 'Not Found',
        path: req.path
    });
});

// Error Handler
app.use((err, req, res, next) => {
    console.error('未處理嘅錯誤:', err);
    
    res.status(500).json({
        error: 'Internal Server Error',
        message: err.message
    });
});

// 啟動服務器
app.listen(PORT, () => {
    console.log(`
╔════════════════════════════════════════════╗
║     🤖 Ollama Bridge API 已啟動！          ║
╠════════════════════════════════════════════╣
║  端口：${PORT}                                
║  Model: ${OLLAMA_MODEL}                      
║  端點：http://localhost:${PORT}/api/generate  
╚════════════════════════════════════════════╝
    `);
});

module.exports = app;
