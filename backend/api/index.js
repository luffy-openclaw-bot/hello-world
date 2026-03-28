/**
 * Ollama Bridge API (串流版本)
 * 
 * 作為前端同 Ollama API 之間嘅橋樑
 * 支持串流回應，讓用戶即時見到部分內容
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
    origin: '*',
    methods: ['GET', 'POST', 'OPTIONS'],
    allowedHeaders: ['Content-Type', 'Authorization', 'X-Requested-With']
}));

app.use(express.json());

// 明確處理 OPTIONS preflight 請求
app.options('*', (req, res) => {
    res.header('Access-Control-Allow-Origin', '*');
    res.header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    res.header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
    res.sendStatus(200);
});

// Health Check
app.get('/health', (req, res) => {
    res.json({
        status: 'ok',
        timestamp: new Date().toISOString(),
        service: 'ollama-bridge-api',
        streaming: true
    });
});

// API 資訊
app.get('/api/info', (req, res) => {
    res.json({
        name: 'Ollama Bridge API',
        version: '4.0 (Streaming)',
        model: OLLAMA_MODEL,
        endpoint: OLLAMA_API_URL
    });
});

/**
 * POST /api/generate
 * 
 * 代理請求到 Ollama API (支持串流)
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
            hasSystem: !!system,
            stream
        });
        
        // 設置 40 秒超時
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 40000);
        
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
                    stream: stream  // 傳遞串流標誌
                }),
                signal: controller.signal
            });
            
            clearTimeout(timeoutId);
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error(`Ollama API 錯誤 (${response.status}):`, errorText);
                
                if (stream) {
                    return res.status(response.status).send('Error');
                }
                return res.status(response.status).json({
                    error: `Ollama API Error: ${response.status}`,
                    details: errorText
                });
            }
            
            // 串流模式
            if (stream) {
                console.log(`[${new Date().toISOString()}] 開始串流`);
                
                // 設置 SSE headers
                res.setHeader('Content-Type', 'text/event-stream');
                res.setHeader('Cache-Control', 'no-cache');
                res.setHeader('Connection', 'keep-alive');
                
                // 直接轉發 Ollama 嘅串流
                const reader = response.body.getReader();
                const decoder = new TextDecoder();
                
                try {
                    while (true) {
                        const { done, value } = await reader.read();
                        if (done) break;
                        
                        const chunk = decoder.decode(value);
                        // 轉發原始數據到前端
                        res.write(chunk);
                    }
                    res.end();
                    console.log(`[${new Date().toISOString()}] 串流完成`);
                } catch (error) {
                    console.error('串流錯誤:', error);
                    res.end();
                }
                return;
            }
            
            // 非串流模式
            const data = await response.json();
            
            console.log(`[${new Date().toISOString()}] 請求成功`, {
                hasResponse: !!data.response,
                hasThinking: !!data.thinking,
                responseLength: data.response?.length || 0,
                thinkingLength: data.thinking?.length || 0
            });
            
            // 合併 thinking 同 response (如果存在)
            let finalResponse = '';
            if (data.thinking && data.response) {
                // 有思考過程 + 結論
                finalResponse = `💭 思考：\n${data.thinking}\n\n✅ 結論：\n${data.response}`;
            } else if (data.response) {
                finalResponse = data.response;
            } else if (data.text) {
                finalResponse = data.text;
            } else if (data.thinking) {
                // 只有思考過程
                finalResponse = data.thinking;
            }
            
            res.json({
                success: true,
                response: finalResponse,
                model,
                timestamp: new Date().toISOString(),
                raw: {
                    hasThinking: !!data.thinking,
                    hasResponse: !!data.response
                }
            });
            
        } catch (error) {
            clearTimeout(timeoutId);
            
            if (error.name === 'AbortError') {
                console.error('Ollama API 請求超時 (40 秒)');
                
                if (stream) {
                    return res.status(504).send('Timeout');
                }
                return res.status(504).json({
                    error: 'Gateway Timeout',
                    message: 'Ollama API 請求超時 (超過 40 秒)，請稍後再試。'
                });
            }
            
            throw error;
        }
        
    } catch (error) {
        console.error('伺服器錯誤:', error);
        
        if (!req.body.stream) {
            res.status(500).json({
                error: 'Internal Server Error',
                message: error.message
            });
        } else {
            res.status(500).send('Error');
        }
    }
});

// 啟動服務器
app.listen(PORT, () => {
    console.log(`
╔════════════════════════════════════════════╗
║     🤖 Ollama Bridge API 已啟動！          ║
╠════════════════════════════════════════════╣
║  端口：${PORT}                                
║  Model: ${OLLAMA_MODEL}                      
║  串流：✅ 已啟用                             
║  端點：http://localhost:${PORT}/api/generate  
╚════════════════════════════════════════════╝
    `);
});

module.exports = app;
