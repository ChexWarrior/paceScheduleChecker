<?php

require 'vendor/autoload.php';

use chexwarrior\Checker;

// Get configuration from file (JSON)

// Create course checker
$courseChecker = new Checker();

// For each request in checker scrape and parse info
array_walk($courseChecker->getScheduleRequests(), function ($request) {
    $html = $courseChecker->getCourseInfo($request);
    $results = $courseChecker->parseCouseInfo($html);

    if (!empty($results)) {
        // Update message
    }
});

// Send notification

// Sleep

