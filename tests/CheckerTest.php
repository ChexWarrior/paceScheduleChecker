<?php

use PHPUnit\Framework\TestCase;
use chexwarrior\Checker;

class CheckerTest extends TestCase
{
    public function parseCourseInfoDataProvider() {
        $schedule1Html = file_get_contents('tests/html/schedule-1.html');

        return [
            [$schedule1Html, 'Caesar', ['key' => 12211, 'value' => 'NONE']],
            [$schedule1Html, 'Andy', ['key' => 23222, 'value' => 10]],
        ];
    }

    /**
     * @dataProvider parseCourseInfoDataProvider
     */
    public function testParseCourseInfo(string $html, string $courseName, array $expectedResults) {
        $checker = new Checker([]);
        $results = $checker->parseCourseInfo($html, $courseName);
        // var_dump($results);
        $this->assertArrayHasKey($expectedResults['key'], $results);
        $this->assertContains($expectedResults['value'], $results);
    }
}
