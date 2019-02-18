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
     * each one is a unique combination of Term, Level, Subject and Courses
     */
    private $scheduleRequests;

    /**
     * @property GuzzleHttp\Client $client Object that will make http requests to schedule page
     */
    private $client;

    /**
     * @property array $rowAttributes An array of the columns each course row is made of
     */
    private $rowAttributes = [
        'CRN' => 'CRN',
        'Subject' => 'Subject',
        'CourseNumber' => 'CourseNumber',
        'Title' => 'Title',
        'ScheduleType' => 'ST',
        'Credits' => 'Credits',
        'Campus' => 'Campus',
        'SectionComments' => 'newcol',
        'Days' => 'Days',
        'Time' => 'Time',
        'Capacity' => 'cap',
        'SeatsAvailable' => 'Seats',
        'Instructor' => 'Instructor',
        'MoreInfo' => 'info',
    ];

    public function __construct(array $requests = []) {
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

    /**
     * Creates an XPath selector based on info passed, will use crn first, then course
     * number and finally title
     * @param string $title Course title
     * @param string $crn Course crn (is unique for each class)
     * @param string $num Course number
     * @return string Returns an XPath selector on success and an empty string on failure
     */
    public function createCourseSelector(?string $crn, ?string $num, ?string $title): string {
        $selector = '//tbody[@class="yui-dt-data"]/tr/td/div[contains(text(), "%s")]/../..';

        if (!empty($crn)) {
            return sprintf($selector, $crn);
        }

        if (!empty($num)) {
            return sprintf($selector, $num);
        }

        if (!empty($title)) {
            return sprintf($selector, $title);
        }

        return '';
    }

    public function parseCourseRow(Crawler $row): array {
        $results = [];
        array_walk($this->rowAttributes, function ($colId, $colName) use ($row, &$results) {
            $text = trim($row->filterXPath(sprintf('//td[contains(@class, "yui-dt0-col-%s")]', $colId))->text(), " \t\n\r\0\x0B" . chr(0xC2) . chr(0xA0));
            $text = preg_replace('/\s+/', ' ', $text);

            if ($colId == 'Time') {
                $matches = [];
                preg_match('/([0-9]+:[0-9]+(am|pm)-[0-9]+:[0-9]+(am|pm))/', $text, $matches);
                $text = $matches[1];
            }

            $results[$colName] = $text;
        });

        return $results;
    }

    public function parseCourseInfo(string $html, string $selector): array {
        $results = [];
        $crawler = new Crawler($html);
        $crawler = $crawler->filterXPath($selector);
        $crawler->each(function(Crawler $tableRow, int $i) use (&$results) {
            $cols = $this->parseCourseRow($tableRow);
            $results[$cols['CRN']] = $cols;
        });

        return $results;
    }
}
