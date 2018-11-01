<?php

require 'vendor/autoload.php';

use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp\Client;

const SCHEDULE_URL = 'https://appsrv.pace.edu/ScheduleExplorerLive/index.cfm';

$semester = $argv[1];
$level = $argv[2];
$major = $argv[3];
$course = $argv[4];

$client = new Client();
$response = $client->request('POST', SCHEDULE_URL, [
  'form_params' => [
    'level' => $level,
    'term' => $semester,
    'subject' => $major,
  ],
]);

$body = $response->getBody();

echo $body;
