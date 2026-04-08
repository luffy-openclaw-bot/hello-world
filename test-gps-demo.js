const { chromium } = require('playwright');

(async () => {
    console.log('🧪 開始測試 GPS 打卡 Demo 頁面...\n');
    
    // 啟動瀏覽器
    const browser = await chromium.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    const page = await browser.newPage({
        viewport: { width: 1280, height: 800 }
    });
    
    // 截圖函數
    async function takeScreenshot(name) {
        await page.screenshot({ 
            path: `test-results/gps-demo-${name}.png`,
            fullPage: true
        });
        console.log(`📸 截圖已保存：gps-demo-${name}.png`);
    }
    
    try {
        // === 測試 1: 首頁加載 ===
        console.log('✅ 測試 1: 首頁加載');
        await page.goto('https://luffy-openclaw-bot.github.io/hello-world/gps-checkin-demo.html');
        await page.waitForLoadState('networkidle');
        
        // 檢查標題
        const title = await page.title();
        console.log(`   頁面標題：${title}`);
        
        // 檢查場景選擇頁面是否顯示
        const caseSelectPage = await page.$('#caseSelectPage.active');
        if (caseSelectPage) {
            console.log('   ✅ 首頁（場景選擇）正常顯示');
        } else {
            console.log('   ❌ 首頁未正確顯示');
        }
        
        // 檢查三個場景選項是否存在
        const caseOptions = await page.$$('div[onclick^="selectCase"]');
        console.log(`   場景選項數量：${caseOptions.length}`);
        
        if (caseOptions.length === 3) {
            console.log('   ✅ 三個場景選項都存在');
        } else {
            console.log('   ❌ 場景選項數量不對');
        }
        
        await takeScreenshot('01-homepage');
        
        // === 測試 2: 員工登入模式 ===
        console.log('\n✅ 測試 2: 員工登入模式');
        await caseOptions[0].click(); // 點擊第一個選項（員工登入版）
        await page.waitForTimeout(500);
        
        const loginPage = await page.$('#loginPage.active');
        if (loginPage) {
            console.log('   ✅ 登入頁面正常顯示');
        } else {
            console.log('   ❌ 登入頁面未顯示');
        }
        
        await takeScreenshot('02-login-page');
        
        // === 測試 3: 登入功能 ===
        console.log('\n✅ 測試 3: 登入功能');
        await page.fill('#email', 'employee@gps-checkin.com');
        await page.fill('#password', 'employee123');
        await page.click('#loginForm button[type="submit"]');
        await page.waitForTimeout(1000);
        
        const checkinPage = await page.$('#checkinPage.active');
        if (checkinPage) {
            console.log('   ✅ 登入成功，進入打卡頁面');
        } else {
            console.log('   ❌ 登入失敗');
        }
        
        await takeScreenshot('03-checkin-page');
        
        // === 測試 4: GPS 定位 ===
        console.log('\n✅ 測試 4: GPS 定位');
        await page.waitForTimeout(2000); // 等待 GPS 定位
        
        const locationInfo = await page.$('#locationInfo');
        if (locationInfo) {
            const locationText = await locationInfo.textContent();
            console.log(`   位置信息：${locationText.substring(0, 50)}...`);
            
            if (locationText.includes('緯度') || locationText.includes('經度')) {
                console.log('   ✅ GPS 定位成功');
            } else {
                console.log('   ⚠️ GPS 定位中...');
            }
        }
        
        // === 測試 5: 打卡功能 ===
        console.log('\n✅ 測試 5: 上班打卡功能');
        await page.waitForTimeout(1000); // 等待 GPS 完成
        
        const checkinBtn = await page.$('#checkinBtn:not([disabled])');
        if (checkinBtn) {
            console.log('   ✅ 打卡按鈕已啟用');
            await checkinBtn.click();
            await page.waitForTimeout(1500);
            
            // 檢查打卡成功消息
            const messageDiv = await page.$('#checkinMessage:not(.hidden)');
            if (messageDiv) {
                const messageText = await messageDiv.textContent();
                console.log(`   打卡消息：${messageText}`);
                console.log('   ✅ 打卡成功');
            } else {
                console.log('   ⚠️ 未見到打卡成功消息');
            }
            
            await takeScreenshot('04-checkin-success');
        } else {
            console.log('   ❌ 打卡按鈕未啟用');
        }
        
        // === 測試 6: 返回場景選擇 ===
        console.log('\n✅ 測試 6: 返回場景選擇');
        await page.click('button:has-text("返回主頁")');
        await page.waitForTimeout(500);
        
        const backToCaseSelect = await page.$('#caseSelectPage.active');
        if (backToCaseSelect) {
            console.log('   ✅ 成功返回場景選擇頁面');
        } else {
            console.log('   ❌ 返回失敗');
        }
        
        await takeScreenshot('05-back-to-home');
        
        // === 測試 7: 管理員模式 ===
        console.log('\n✅ 測試 7: 管理員模式');
        const allCaseOptions = await page.$$('div[onclick^="selectCase"]');
        await allCaseOptions[2].click(); // 點擊第三個選項（管理員模式）
        await page.waitForTimeout(500);
        
        const dashboardPage = await page.$('#dashboardPage.active');
        if (dashboardPage) {
            console.log('   ✅ Dashboard 頁面正常顯示');
            
            // 檢查統計卡片
            const statCards = await page.$$('div.toyo-card p.text-4xl');
            console.log(`   統計卡片數量：${statCards.length}`);
            
            // 檢查打卡記錄表格
            const table = await page.$('table');
            if (table) {
                console.log('   ✅ 打卡記錄表格存在');
            }
            
            await takeScreenshot('06-admin-dashboard');
        } else {
            console.log('   ❌ Dashboard 頁面未顯示');
        }
        
        // === 測試 8: 免登模式 ===
        console.log('\n✅ 測試 8: 免登模式（設備註冊）');
        await page.click('button:has-text("返回主頁")');
        await page.waitForTimeout(500);
        
        const caseOptions2 = await page.$$('div[onclick^="selectCase"]');
        await caseOptions2[1].click(); // 點擊第二個選項（免登版）
        await page.waitForTimeout(500);
        
        const devicePage = await page.$('#deviceRegisterPage.active');
        if (devicePage) {
            console.log('   ✅ 設備註冊頁面正常顯示');
            
            // 檢查設備信息
            const deviceInfo = await page.$('text=設備已註冊');
            if (deviceInfo) {
                console.log('   ✅ 設備信息存在');
            }
            
            await takeScreenshot('07-device-register');
        } else {
            console.log('   ❌ 設備註冊頁面未顯示');
        }
        
        console.log('\n✅ 所有測試完成！\n');
        
    } catch (error) {
        console.error('❌ 測試失敗:', error.message);
        await takeScreenshot('error');
    } finally {
        await browser.close();
    }
})();
