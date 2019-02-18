<?php

use PHPUnit\Framework\TestCase;
use Symfony\Component\DomCrawler\Crawler;
use chexwarrior\Checker;

class CheckerTest extends TestCase
{
    const HTML_TEST_PATH = 'tests/html';

    public function courseSelectorDataProvider() {
        return [
            [null, null, null, ''],
            ['33411', '404', 'Underwater Basketweaving', '33411'],
            [null, '404', 'Underwater Basketweaving', '404'],
            [null, null, 'Underwater Basketweaving', 'Underwater Basketweaving'],
        ];
    }

    public function parseCourseRowDataProvider() {
        $row1 = file_get_contents(sprintf("%s/row1.html", self::HTML_TEST_PATH));

        return [
            [
                $row1,
                [
                    'CRN' => '20387',
                    'Subject' => 'FOR',
                    'CourseNumber' => '620',
                    'Title' => 'Analytical Spectroscopy',
                    'ScheduleType' => 'LEC',
                    'Credits' => '4',
                    'Campus' => 'NYC',
                    'SectionComments' => '',
                    'Days' => 'T',
                    'Time' => '06:10pm-08:10pm',
                    'Capacity' => '4',
                    'SeatsAvailable' => '',
                    'Instructor' => 'Zhaohua Dai',
                    'MoreInfo' => 'Linked Prerequisites',
                ]
            ],
        ];
    }

    /**
     * @dataProvider courseSelectorDataProvider
     */
    public function testCourseSelectorHasExpectedOutput(?string $crn, ?string $num, ?string $title, string $result) {
        $checker = new Checker();
        $sel = $checker->createCourseSelector($crn, $num, $title);

        $this->assertStringContainsString($result, $sel);
    }

    /**
     * @dataProvider parseCourseRowDataProvider
     */
    public function testParseCourseRow(string $html, array $expectedResults) {
        $checker = new Checker();
        $results = $checker->parseCourseRow(new Crawler($html));

        array_walk($expectedResults, function($value, $key) use ($results) {
            $this->assertArrayHasKey($key, $results);
            $this->assertEquals($value, $results[$key]);
        });
    }
}
