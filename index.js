const puppeteer = require('puppeteer');
const process = require('process');
const fs = require('fs');
const scheduleURL = 'https://appsrv.pace.edu/ScheduleExplorerLive/index.cfm';
const args = process.argv;
const logPath = '/var/log/paceScheduleChecker/paceScheduleChecker.log';

if(args.length < 4) {
  console.log('You must pass a valid Mailgun API key and domain!');
  process.exit(1);
}

const mailgun = require('mailgun-js')({
  apiKey: args[2],
  domain: args[3]
});

const getTimeStamp = function() {
  let d = new Date();

  return `${d.getMonth()}/${d.getDay()}/${d.getFullYear()} ${d.getHours()}:${d.getMinutes()}`;
};

const writeToLog = function (message, logPath, type = 'INFO') {
  let timestamp = getTimeStamp();
  console.log(`${timestamp} - ${type}: ${message}`);
  fs.appendFileSync(logPath, `\n${timestamp} - ${type}: ${message}`);
}

const sendMessage = function(content) {
  // console.log(content);
  let data = {
    from: 'Schedule Checker <schedCheck@scheduleChecker.biz>',
    to: 'aplehm@gmail.com',
    subject: 'Schedule Checker Results',
    text: content
  };

  mailgun.messages().send(data, (error, body) => {
    if(error) { 
      writeToLog(error, logPath, 'ERROR');
    } else {
      writeToLog('Email sent!', logPath);
    }
  });
};

(async () => {
  const browser = await puppeteer.launch({
    executablePath: '/usr/bin/google-chrome'
  });

  const page = await browser.newPage();
  await page.setViewport({
    width: 1280,
    height: 800
  });

  
  writeToLog('Go to schedule page...', logPath);
  await page.goto(scheduleURL);

  await page.$eval('#checkterm', (el) => {
    el.value = '201870';
    return el.value;
  });

  await page.$eval('#level', (el) => {
    el.value = 'Graduate';
    return el.value;
  });

  await page.$eval('#subject', (el) => {
    el.value = 'CS';
    return el.value;
  });

  writeToLog('Display all graduate CS classes for Fall 2018...', logPath);
  const submitBtn = await page.$('#submitbutton');
  await submitBtn.click();
  await page.waitFor('#yuidatatable1');
  // await page.screenshot({
  //   path: 'allCSClasses.png'
  // });

  writeToLog('Grab all Artificial Intelligence class rows!', logPath);
  const rows = 
    await page.$x('//td/div[text() = "Artificial Intelligence"]/../..');

  for(let row of rows) {
    // TODO: How is this working exactly?
    let id = await page.evaluate(row => {
      return row.getAttribute('id');
    }, row);

    let status = await page.$eval(`#${id}`, (row) => {
      let seatsColumn = row.querySelector('td[class*="Seats"] div');
      return seatsColumn.innerHTML.trim();
    });

    let crn = await page.$eval(`#${id}`, (row) => {
      let crnColumn = row.querySelector('td[class*="CRN"] div a');
      return crnColumn.innerHTML.trim();
    });

    writeToLog(`Found matching row with ID: ${id} and CRN: ${crn}`, logPath);

    if(status != 'CLOSED') {
      sendMessage(`An AI class seems to have opened up!
      Course CRN: ${crn}
      Schedule Page Link: ${scheduleURL}
      `);
    } else {
      writeToLog(`Class is closed...`, logPath);
    }
  }

  await browser.close();
})();