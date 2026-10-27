const puppeteer = require('puppeteer-core');

(async () => {
    const browser = await puppeteer.launch({
        executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe',
        headless: 'new',
        args: ['--window-size=1400,1000'],
    });
    const page = await browser.newPage();
    page.setDefaultTimeout(90000);
    page.setDefaultNavigationTimeout(90000);
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
    await new Promise(r => setTimeout(r, 2000));
    await page.type('#lp-username', 'wynsedgwick@photocv.com');
    await page.type('#lp-password', 'secret123');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle2' }).catch(() => {}),
        page.click('button[type="submit"]'),
    ]);
    await new Promise(r => setTimeout(r, 4000));

    const routes = ['/notice', '/post', '/notice/1', '/notice/1/edit', '/plans', '/plans/add?type=location', '/plans/add?type=dayout', '/insights', '/settings/photo'];
    for (const route of routes) {
        currentRoute = route;
        try {
            await page.goto('http://localhost:5173' + route, { waitUntil: 'networkidle2', timeout: 90000 });
            await new Promise(r => setTimeout(r, 6000));
            await page.screenshot({ path: `C:/Users/trainee3/AppData/Local/Temp/claude/c--Hamza-photocv-react/ab162648-96bf-4a6d-b6e2-7e203e1b2cf5/scratchpad/shot_${route.replace(/[\/?=&]/g, '_')}.png` });
        } catch (e) {
            errors[currentRoute] = errors[currentRoute] || [];
            errors[currentRoute].push('NAV ERROR: ' + e.message);
        }
    }

    // Signup is unauthenticated - open a fresh page
    currentRoute = '/signup/photo';
    try {
        const page2 = await browser.newPage();
        page2.setDefaultTimeout(90000);
        page2.on('console', (msg) => { if (msg.type() === 'error') { errors['/signup/photo'] = errors['/signup/photo'] || []; errors['/signup/photo'].push(msg.text()); } });
        page2.on('pageerror', (err) => { errors['/signup/photo'] = errors['/signup/photo'] || []; errors['/signup/photo'].push('PAGEERROR: ' + err.message); });
        await page2.goto('http://localhost:5173/signup/photo', { waitUntil: 'networkidle2', timeout: 90000 });
        await new Promise(r => setTimeout(r, 4000));
        await page2.screenshot({ path: 'C:/Users/trainee3/AppData/Local/Temp/claude/c--Hamza-photocv-react/ab162648-96bf-4a6d-b6e2-7e203e1b2cf5/scratchpad/shot_signup_photo.png' });
        await page2.close();
    } catch (e) {
        errors['/signup/photo'] = errors['/signup/photo'] || [];
        errors['/signup/photo'].push('NAV ERROR: ' + e.message);
    }

    console.log('=== CONSOLE ERRORS BY ROUTE ===');
    console.log(JSON.stringify(errors, null, 2));

    await browser.close();
})();
