<?php

namespace chexwarrior;

use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ConnectException;

/**
 * Flow
 *
 *  - Term
 *  -- Level
 *  --- Subject
 *  ---- Courses
 *
 *  Check for each courses specified
 *  If a change is found add that to notification
 */

class Checker
{
    private const SCHEDULE_URL = 'https://appsrv.pace.edu/ScheduleExplorerLive';

    /**
     * @property array $scheduleRequests An array of requests to be made to schedule explorer page, each one is a unique combination of Term, Level, Subject and Courses
     */
    private $scheduleRequests;

    /**
     * @property GuzzleHttp\Client $client Object that will make http requests to schedule page
     */
    private $client;

    public function __construct(array $requests) {
        $this->scheduleRequests = $requests;
        $this->client = new Client([
            'base_uri' => self::SCHEDULE_URL,
        ]);
    }

    public function getScheduleRequests() {
        return $this->scheduleRequests;
    }

    public function getCourseInfo(array $params) {
        try {
            $response = $this->client->post([
                'form_params' => $params,
            ]);
        } catch (ClientException | ServerException $e) {
            return null;
        }

        return $response->getBody()->getContents();
    }

    public function parseCourseInfo(string $html, string $courseName) {
        $results = [];
        $trSelector = sprintf(
            '//tbody[@class="yui-dt-data"]/tr/td/div[contains(text(), "%s")]/../..', $courseName
        );

        $crawler = new Crawler($html);
        $crawler = $crawler->filterXPath($trSelector);

        foreach ($crawler as $tableRow) {
            $crn = $tableRow->firstChild->textContent;
            $seatsAvailable = $tableRow->lastChild->textContent;
            $results[$crn] = $seatsAvailable;
        }

        return $results;
    }
}
