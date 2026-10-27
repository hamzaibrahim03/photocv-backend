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
    const errors = {};
    let currentRoute = 'init';
    page.on('console', (msg) => {
        if (msg.type() === 'error') {
            errors[currentRoute] = errors[currentRoute] || [];
            errors[currentRoute].push(msg.text());
        }
    });
    page.on('pageerror', (err) => {
        errors[currentRoute] = errors[currentRoute] || [];
        errors[currentRoute].push('PAGEERROR: ' + err.message);
    });

    await page.goto('http://localhost:5173/login', { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 3000));
    await page.type('#lp-username', 'wynsedgwick@photocv.com');
    await page.type('#lp-password', 'secret123');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle2' }).catch(() => {}),
        page.click('button[type="submit"]'),
    ]);
    await new Promise(r => setTimeout(r, 5000));

    const routes = ['/notice', '/post', '/notice/20', '/notice/20/edit', '/plans', '/insights', '/settings/photo'];
    for (const route of routes) {
        currentRoute = route;
        errors[currentRoute] = [];
        try {
            await page.goto('http://localhost:5173' + route, { waitUntil: 'networkidle2', timeout: 120000 });
            await new Promise(r => setTimeout(r, 10000));
            if (errors[currentRoute].length === 0) delete errors[currentRoute];
        } catch (e) {
            errors[currentRoute].push('NAV ERROR: ' + e.message);
        }
    }

    console.log('=== FINAL CONSOLE ERRORS BY ROUTE ===');
    console.log(JSON.stringify(errors, null, 2));
    await browser.close();
})();
