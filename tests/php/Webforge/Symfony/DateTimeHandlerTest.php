<?php
/**
 * Created by PhpStorm.
 * User: psc
 * Date: 18.05.2018
 * Time: 14:49
 */

namespace Webforge\Symfony;

class DateTimeHandlerTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @dataProvider iso6801Formats
     */
    public function testParse($isoDate, $expectedDate, $dateFormat = 'd.m.Y H:i:s')
    {
        $this->assertEquals($expectedDate, DateTimeHandler::parse($isoDate)->format($dateFormat), 'for isoDate: '.$isoDate);
    }

    public function iso6801Formats()
    {
        return [
            ['2018-05-19T11:43:41.564Z', '19.05.2018 11:43:41'],
            ['+02018-05-16T10:00:00.000Z', '16.05.2018 10:00:00'],
            //['+020187-05-16T10:00:00.000Z', '16.05.20187 00:00:00'],
            //['2009-06-30T18:30:00+02:00', '30.06.2009 18:30:00'] // how would we treat this?
        ];
    }
}
