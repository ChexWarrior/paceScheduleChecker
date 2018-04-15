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
  await page.screenshot({
    path: 'example.png'
  });

  await browser.close();
})();