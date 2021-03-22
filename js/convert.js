#!/usr/bin/env node
const yargs = require('yargs/yargs')
const {hideBin} = require('yargs/helpers')
const fs = require('fs');
const puppeteer = require('puppeteer')
const varPath = __dirname
const argv = yargs(hideBin(process.argv)).argv
const screenWidth = 562;
const scaleFactor = 8;
const codeFilePath = argv.path
const codeFileLang = argv.lang

function escape(rawCode) {
    return rawCode.replace(/[&<"]/g, function(m) {
        switch (m) {
            case '&':
                return '&amp;';
            case '<':
                return '&lt;';
            case '"':
                return '&quot;';
            // default:
            //     return '&#039;';
        }
    });
    // return encodeURI(rawCode);
    // return String(rawCode)
    //     .replace(/</g, '&lt;')
    //     .replace(/>/g, '&gt;')
    //     .replace(/&/g, '&amp;')
    //     .replace(/"/g, '&quot;')
    //     .replace(/'/g, '&#039;');
    // ;
}


function readFile() {

    if (codeFilePath.length < 1) {
        throw 'No file path set'
    }
    return fs.readFileSync(codeFilePath, 'utf8')
}

function createCodeHtmlFile() {
    let codeHtml = fs.readFileSync(__dirname + '/codeTemplate.html', 'utf8')
    codeHtml = codeHtml
        .replace('<code></code>', '<code>' + codeContent + '</code>')
        .replace('language-php', 'language-' + codeFileLang)
    const htmlPath = varPath + '/generated-code.html'
    fs.writeFileSync(htmlPath, codeHtml);
    return htmlPath;
}

function createTerminalHtmlFile() {
    let terminalHtml = fs.readFileSync(__dirname + '/terminalTemplate.html', 'utf8')
    terminalHtml = terminalHtml
        .replace('</code>', codeContent + '</code>')
        .replace('php -f command.php', argv.command)
    const htmlPath = varPath + '/generated-terminal.html'
    fs.writeFileSync(htmlPath, terminalHtml);
    return htmlPath;
}

const codeContent = escape(readFile());
const htmlPath = (codeFileLang === 'terminal')
    ? createTerminalHtmlFile()
    : createCodeHtmlFile();

// https://stackoverflow.com/a/59655506/14671323
(async () => {
    const browser = await puppeteer.launch();
    const page = await browser.newPage();
    await page.setViewport({
        height: 1,
        width: screenWidth,
        deviceScaleFactor: scaleFactor
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
    await fs.writeFile(varPath + '/generated-image.png', imageBuffer, function (err) {
        if (err) throw err
    });
})();