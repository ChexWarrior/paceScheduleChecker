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
     * @property array $scheduleRequests An array of requests to be made to schedule explorer page,
     *                                   each one is a unique combination of Term, Level, Subject
     *                                   and Courses
     */
    private $scheduleRequests;

    private $message = '';

    /**
     * @property GuzzleHttp\Client $client Object that will make http requests to schedule page
     */
    private $client;

    public function __construct(array $requests) {
        $this->scheduleRequests = $requests;
        $this->client = new Client([
            'base_uri' => SCHEDULE_URL,
        ]);
    }

    private function getCourseInfo(array $params) {
        try {
            $response = $this->client->post([
                'form_params' => $params,
            ]);
        } catch (ClientException | ServerException $e) {
            return null;
        }

        return $response;
    }

    private function parseCourseInfo(string $html) {

    }

    private function updateMessage(bool $result) {

    }

    public function checkCourses(array $requests) {
        array_walk($requests, function($request) {
            $response = $this->getCourseInfo($request);
            $html = $response->getBody()->getContents();
            $result = $this->parseCourseInfo($html);
            $this->message = $this->updateMessage($result);
        });
    }
}
