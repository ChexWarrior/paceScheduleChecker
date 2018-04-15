const puppeteer = require('puppeteer');
const process = require('process');
const scheduleURL = 'https://appsrv.pace.edu/ScheduleExplorerLive/index.cfm';
const args = process.argv;

if(args.length < 4) {
  console.log('You must pass a valid Mailgun API key and domain!');
  process.exit(1);
}

const mailgun = require('mailgun-js')({
  apiKey: args[3],
  domain: args[4]
});

// let data = {
//   from: 'Schedule Checker <schedCheck@highHolyZeum>',
//   to: 'aplehm@gmail.com',
//   subject: 'Schedule Checker Results',
//   text: 'Hello!!'
// };

// mailgun.messages().send(data, (error, body) => {
//   console.log(body);
// });

(async () => {
  const browser = await puppeteer.launch({
    executablePath: '/usr/bin/google-chrome'
  });

  const page = await browser.newPage();
  await page.setViewport({
    width: 1280,
    height: 800
  });

  console.log('Go to schedule page...');
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

  console.log('Display all graduate CS classes for Fall 2018...');
  const submitBtn = await page.$('#submitbutton');
  await submitBtn.click();
  await page.waitFor('#yuidatatable1');
  await page.screenshot({
    path: 'allCSClasses.png'
  });

  // check how many rows there are for AI

  await browser.close();
})();