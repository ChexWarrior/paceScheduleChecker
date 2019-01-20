<?php

use PHPUnit\Framework\TestCase;
use chexwarrior\Checker;

class CheckerTest extends TestCase
{
    public function testParsingCourseInfo() {
        $checker = new Checker([]);

        $html = <<<HTML
        <!DOCTYPE html>
        <html>
            <body>
                <table id="#yuidatatable1">
                    <thead></thead>
                    <tbody class="yui-dt-data">
                        <tr><td>12211</td><td><div>Caesar</div></td><td>NONE</td></tr>
                        <tr><td>23222</td><td><div>Andy</div></td><td>10</td></tr>
                    </tbody>
                </table>
            </body>
        </html>
HTML;
        $results = $checker->parseCourseInfo($html, 'Caesar');
        $this->assertArrayHasKey(12211, $results);
        $this->assertContains('NONE', $results);
    }
}
