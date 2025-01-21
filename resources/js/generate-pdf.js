// const puppeteer = require("puppeteer");

// async function generatePdf() {
//     try {
//         console.log(puppeteer.executablePath());

//         const browser = await puppeteer.launch({
//             headless: true,
//             args: ['--no-sandbox', '--disable-setuid-sandbox']
//         });
//         const page = await browser.newPage();
//         const html = process.argv[2];
    
//         await page.setContent(html);
//         // await page.waitForSelector("canvas");
//         await page.pdf({
//             path: process.argv[3],
//             format: "A4",
//             printBackground: true,
//             margin: { top: '16px', left: '32px' }
//         });
//         await browser.close();
//     } catch (error) {
//         console.error('OUTPUT FILE PATH: ', error);
//         console.error('Error running Puppeteer:', error);
//     }
// }

// generatePdf();

const puppeteer = require('puppeteer');
const path = require('path');

(async () => {
	try {
    console.log("===> (DEBUG) JOKO WIDODO");
console.log(process.cwd());

   console.log('Default executable path:', puppeteer.executablePath());

    // Launch the browser
    console.log("===> (DEBUG) B");

    // const browser = await puppeteer.launch({executablePath: path.resolve(__dirname, 'chrome')});
    // const browser = await puppeteer.launch({executablePath: '/root/.cache/puppeteer/chrome/linux-132.0.6834.83/chrome-linux64/chrome', args: ['--no-sandbox', '--disable->
    const browser = await puppeteer.launch({args: ['--no-sandbox', '--disable-setuid-sandbox']});
 console.log("===> (DEBUG) A");

    const page = await browser.newPage();

    console.log("===> (DEBUG) B");

    // Go to a website
    await page.goto('https://www.google.com');

    // Print the title of the page
    const title = await page.title();
    console.log('Page Title:', title);

    // Close the browser
    await browser.close();
} catch (error) {
   console.log(error);
}})();


