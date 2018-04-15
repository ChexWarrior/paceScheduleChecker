const puppeteer = require('puppeteer');
const scheduleURL = 'https://appsrv.pace.edu/ScheduleExplorerLive/index.cfm';

(async () => {
  const browser = await puppeteer.launch({
    executablePath: '/usr/bin/google-chrome'
  });

  const page = await browser.newPage();
  await page.setViewport({
    width: 1280,
    height: 800
  });

  await page.goto(scheduleURL);

  await page.waitForFunction(function() {
    let semesterDropdown = document.querySelector('#checkterm');
    let studentTypeDropdown = document.querySelector('#level');
    let subjectDropdown = document.querySelector('#subject');

    // fall 2018
    semesterDropdown.value = '201870';
    studentTypeDropdown.value = 'Graduate';
    subjectDropdown.value = 'CS';

    return true;
  });

  const submitBtn = await page.$('#submitbutton');
  await submitBtn.click();

  await page.waitFor('#yuidatatable1');

  await page.screenshot({
    path: 'example.png'
  });

  await browser.close();
})();