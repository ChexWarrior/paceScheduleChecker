<?php

use PHPUnit\Framework\TestCase;
use chexwarrior\Checker;

class CheckerTest extends TestCase
{
    public function courseSelectorDataProvider() {
        return [
            [null, null, null, ''],
            ['33411', '404', 'Underwater Basketweaving', '33411'],
            [null, '404', 'Underwater Basketweaving', '404'],
            [null, null, 'Underwater Basketweaving', 'Underwater Basketweaving'],
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

    // public function parseCourseInfoDataProvider() {
    //     $schedule1Html = file_get_contents('tests/html/schedule-1.html');

    //     return [
    //         [$schedule1Html, 'Caesar', ['key' => 12211, 'value' => 'NONE']],
    //         [$schedule1Html, 'Andy', ['key' => 23222, 'value' => 10]],
    //     ];
    // }

    // /**
    //  * @dataProvider parseCourseInfoDataProvider
    //  */
    // public function testParseCourseInfo(string $html, string $courseName, array $expectedResults) {
    //     $checker = new Checker([]);
    //     $results = $checker->parseCourseInfo($html, $courseName);
    //     $this->assertArrayHasKey($expectedResults['key'], $results);
    //     $this->assertContains($expectedResults['value'], $results);
    // }
}
