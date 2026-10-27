const puppeteer = require('puppeteer-core');

(async () => {
    const browser = await puppeteer.launch({
        executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe',
        headless: 'new',
        args: ['--window-size=1400,1000'],
    });
    const page = await browser.newPage();
    page.setDefaultTimeout(120000);
    page.setDefaultNavigationTimeout(120000);
    const errors = [];
    page.on('console', (msg) => { if (msg.type() === 'error') errors.push(msg.text()); });
    page.on('pageerror', (err) => errors.push('PAGEERROR: ' + err.message));

    await page.goto('http://localhost:5173/login', { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 2000));
    await page.type('#lp-username', 'wynsedgwick@photocv.com');
    await page.type('#lp-password', 'secret123');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle2' }).catch(() => {}),
        page.click('button[type="submit"]'),
    ]);
    await new Promise(r => setTimeout(r, 4000));

    await page.goto('http://localhost:5173/plans', { waitUntil: 'networkidle2', timeout: 120000 });
    await new Promise(r => setTimeout(r, 15000));
    await page.screenshot({ path: 'C:/Users/trainee3/AppData/Local/Temp/claude/c--Hamza-photocv-react/ab162648-96bf-4a6d-b6e2-7e203e1b2cf5/scratchpad/shot_plans_retry.png', fullPage: true });

    console.log('ERRORS:', JSON.stringify(errors, null, 2));
    await browser.close();
})();
