#!/usr/bin/env node
const yargs = require('yargs/yargs')
const {hideBin} = require('yargs/helpers')
const fs = require('fs');
const puppeteer = require('puppeteer')
const varPath = __dirname
const argv = yargs(hideBin(process.argv)).argv
const codeFilePath = argv.path
const codeFileLang = argv.lang

function escape(rawCode) {
    return String(rawCode)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    ;
}


function readFile() {

    if (codeFilePath.length < 1) {
        throw 'No file path set'
    }
    return fs.readFileSync(codeFilePath, 'utf8')
}

//
// function highlight(sourceCode, lang = "php") {
//     const language = Prism.languages[lang];
//     return Prism.highlight(sourceCode, language, lang);
// }

const codeContent = escape(readFile());
codeHtml = fs.readFileSync(__dirname + '/codeTemplate.html', 'utf8')
codeHtml = codeHtml
    .replace('<code></code>', '<code>' + codeContent + '</code>')
    .replace('language-php', 'language-' + codeFileLang)
const htmlPath = varPath + '/highlighted-code.html'
fs.writeFileSync(htmlPath, codeHtml);

// https://stackoverflow.com/a/59655506/14671323
(async () => {
    const browser = await puppeteer.launch();
    const page = await browser.newPage();
    await page.setViewport({
        height: 1,
        width: 600,
        deviceScaleFactor: 1
    });
    page
        .on('console', message =>
            console.log(`${message.type().substr(0, 3).toUpperCase()} ${message.text()}`))
        .on('pageerror', ({message}) => console.log(message))
        .on('response', response =>
            console.log(`${response.status()} ${response.url()}`))
        .on('requestfailed', request =>
            console.log(`${request.failure().errorText} ${request.url()}`))

    await page.goto('file://' + htmlPath);
    const imageBuffer = await page.screenshot({fullPage: true});

    await browser.close();
    await fs.writeFile(varPath + '/highlighted-code.png', imageBuffer, function (err) {
        if (err) throw err
    });
})();