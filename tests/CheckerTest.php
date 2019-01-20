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
                        <tr><td><div>Caesar</div></td></tr>
                        <tr><td><div>Andy</div></td></tr>
                    </tbody>
                </table>
            </body>
        </html>
HTML;
        $checker->parseCourseInfo($html, 'Caesar');
    }
}
