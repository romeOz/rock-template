<?php

namespace rockunit;


use rock\template\date\Date;
use rock\template\date\DateTime;

class DateTest extends \PHPUnit_Framework_TestCase
{
    /** @var  Date */
    protected $date;

    protected function setUp()
    {
        parent::setUp();
        $this->date = new Date;
    }

    /**
     * @dataProvider providerData
     */
    public function testGetTimestamp($time)
    {
        $this->assertSame($this->date->set($time)->getTimestamp(), 595296000);
    }

    public function providerData()
    {
        return [
            ['1988-11-12'],
            [595296000],
            ['595296000']
        ];
    }

    public function testFormat()
    {
        $this->assertSame($this->date->format('j  n  Y'), date('j  n  Y'));
    }

    public function testLocal()
    {
        $this->date->setLocale('ru');
        $this->assertSame($this->date->set('1988-11-12')->format('j  F  Y'), '12  ноября  1988');
    }

    public function testAddFormat()
    {
        $this->date->addFormat('shortDate', 'j / F / Y');
        $this->assertSame($this->date->set('1988-11-12')->shortDate(), '12 / November / 1988');
    }

    public function testAddFormatOption()
    {
        $this->date->addFormatOption('ago', function (DateTime $datetime) {
            return floor((time() - $datetime->getTimestamp()) / 86400) . ' days ago';
        });
        $ago = floor((time() - $this->date->set('1988-11-12')->getTimestamp()) / 86400);
        $this->assertSame($this->date->set('1988-11-12')->format('d F Y, ago'), "12 November 1988, {$ago} days ago");
    }

    public function testDiff()
    {
        $dateTime = $this->date->set('1988-11-12');
        $this->assertSame($dateTime->diff(time())->w, (int)floor($dateTime->diff(time())->days / 7));

        $dateInterval = $this->date->diff('1988-11-12');
        $this->assertSame($dateInterval->w, (int)floor($dateInterval->days / 7) * -1);

        $dateInterval = $this->date->diff('1988-11-12', true);
        $this->assertSame($dateInterval->w, (int)floor($dateInterval->days / 7));
    }

    /**
     * @dataProvider providerIsTrue
     */
    public function testIsDateTrue($value)
    {
        $this->assertTrue(Date::is($value));
    }

    public function providerIsTrue()
    {
        return [
            ['1988-11-12'],
            ['595296000'],
            ['-595296000'],
            [595296000],
            [-595296000],
            [3.14],
            ['3.14']
        ];
    }

    /**
     * @dataProvider providerIsFalse
     */
    public function testIsDateFalse($value)
    {
        $this->assertFalse(Date::is($value));
    }

    public function providerIsFalse()
    {
        return [
            ['foo'],
            [''],
            [null],
            [true],
            [false],
        ];
    }

    /**
     * @dataProvider providerIsTimestampTrue
     */
    public function testIsTimestampTrue($value)
    {
        $this->assertTrue(Date::isTimestamp($value));
    }

    public function providerIsTimestampTrue()
    {
        return [
            ['595296000'],
            ['-595296000'],
            [595296000],
            [-595296000],
        ];
    }

    /**
     * @dataProvider providerIsTimestampFalse
     */
    public function testIsTimestampFalse($value)
    {
        $this->assertFalse(Date::isTimestamp($value));
    }

    public function providerIsTimestampFalse()
    {
        return [
            ['foo'],
            [''],
            [null],
            [true],
            [false],
            ['1988-11-12'],
            ['3.14'],
            [3.14],
        ];
    }

}