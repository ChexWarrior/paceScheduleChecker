<?php

use PHPUnit\Framework\TestCase;
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

    public function parseCourseInfoDataProvider() {
        $row1 = file_get_contents(sprintf("%s/row1.html", self::HTML_TEST_PATH));

        return [
            [$row1, '', []],
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
     * @dataProvider parseCourseInfoDataProvider
     */
    public function testParseCourseInfo(string $html, string $selector, array $expectedResults) {
        $checker = new Checker();
        $results = $checker->parseCourseInfo($html, $selector);

        $this->assertEmpty(array_diff($expectedResults, $results));
    }
}
