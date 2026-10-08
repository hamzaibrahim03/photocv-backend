const puppeteer = require('puppeteer-core');

const CHROME_PATH = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';

async function main() {
    const url = process.argv[2];
    const outPath = process.argv[3];
    if (!url || !outPath) {
        console.error('Usage: node shot.js <url> <outPath>');
        process.exit(1);
    }

    const browser = await puppeteer.launch({
        executablePath: CHROME_PATH,
        headless: 'new',
        args: ['--no-sandbox', '--disable-gpu'],
    });
    try {
        const page = await browser.newPage();
        await page.setViewport({ width: 1280, height: 800 });
        await page.goto(url, { waitUntil: 'networkidle2', timeout: 30000 });
        // networkidle2 tolerates up to 2 in-flight requests, so the page's
        // own data fetch can still be pending here. Wait for its loading
        // state to actually clear instead of guessing a fixed delay.
        await page.waitForFunction(
            () => !document.body.innerText.includes('Loading, please wait'),
            { timeout: 25000 }
        ).catch(() => {});
        await new Promise((r) => setTimeout(r, 800));
        await page.screenshot({ path: outPath, type: 'png' });
    } finally {
        await browser.close();
    }
}

main().catch((err) => {
    console.error(err);
    process.exit(1);
});
