const puppeteer = require("puppeteer");

async function generatePdf() {
    try {
        const browser = await puppeteer.launch({
            headless: true,
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });
        const page = await browser.newPage();
        const html = process.argv[2];
    
        await page.setContent(html);
        await page.waitForSelector("canvas");
        await page.pdf({
            path: process.argv[3],
            format: "A4",
            printBackground: true,
            margin: { top: '16px', left: '32px' }
        });
        await browser.close();
    } catch (error) {
        console.error('Error running Puppeteer:', error);
    }
}

generatePdf();
