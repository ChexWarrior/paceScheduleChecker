const puppeteer = require('puppeteer');
const process = require('process');
const scheduleURL = 'https://appsrv.pace.edu/ScheduleExplorerLive/index.cfm';
const args = process.argv;

if(args.length < 4) {
  console.log('You must pass a valid Mailgun API key and domain!');
  process.exit(1);
}

const mailgun = require('mailgun-js')({
  apiKey: args[2],
  domain: args[3]
});

const sendMessage = function(content) {
  // console.log(content);
  let data = {
    from: 'Schedule Checker <schedCheck@scheduleChecker.biz>',
    to: 'aplehm@gmail.com',
    subject: 'Schedule Checker Results',
    text: content
  };

  mailgun.messages().send(data, (error, body) => {
    console.log(body);
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

  /** 
   find all rows with Artifical Intelligence as title column value...
   xpath that finds row which contains a td which contains a div that has the text Artificial intelligence
   '//td/div[text() = "Artificial Intelligence"]/../..'
    */
  console.log('Grab all Artificial Intelligence class rows!');
  const rows = await page.$x('//td/div[text() = "Artificial Intelligence"]/../..');
  for(let row of rows) {
    // TODO: How is this working exactly?
    const id = await page.evaluate(row => {
      return row.getAttribute('id')
    }, row);

    console.log(id);
    const status = await page.$eval(`#${id}`, (row) => {
      let seatsColumn = row.querySelector('td[class*="Seats"] div');
      return seatsColumn.innerHTML.trim();
    });

    if(status != 'CLOSED') {
      sendMessage(`
      <p>An AI class seems to have opened up!</p>
      <a href="${scheduleURL}">Click here!</a>
      `);
    }
  }

  await browser.close();
})();