<?php

namespace rockunit;

use rock\template\date\DateTime;
use rock\template\date\Exception;
use rock\template\date\locale\Ru;

class DateTimeTest extends \PHPUnit_Framework_TestCase
{
    /** @var  DateTime */
    protected $dateTime;

    protected function setUp()
    {
        parent::setUp();
        $this->dateTime = new DateTime;
    }

    /**
     * @dataProvider providerData
     */
    public function testGetTimestamp($time)
    {
        $this->assertSame($this->dateTime->set($time)->getTimestamp(), 595296000);
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
        $this->assertSame($this->dateTime->format('j  n  Y'), date('j  n  Y'));
        $this->assertSame($this->dateTime->format(), date('Y-m-d'));
    }

    public function testDefaultFormat()
    {
        $this->assertSame($this->dateTime->serverDate(), date('Y-m-d'));
        $this->assertSame($this->dateTime->serverTime(), date('H:i:s'));
        $this->assertSame($this->dateTime->serverDatetime(), date('Y-m-d H:i:s'));

        // set default format
        $this->dateTime->setDeafultFormat('j  n  Y');
        $this->assertSame($this->dateTime->format(), date('j  n  Y'));

        // unknown format
        $this->setExpectedException(Exception::className());
        $this->dateTime->unknown();
    }

    public function testLocal()
    {
        $this->dateTime->setLocale('ru');
        $this->assertSame($this->dateTime->set('1988-11-12')->format('j  F  Y'), '12  ноября  1988');
        $this->assertSame($this->dateTime->set('1988-11-12')->format('j  M  Y'), '12  ноя  1988');
        $this->assertSame($this->dateTime->set('1988-11-12')->format('j  l  Y'), '12  суббота  1988');
        $this->assertSame($this->dateTime->set('1988-11-12')->format('j  D  Y'), '12  Сб  1988');
        $this->assertTrue($this->dateTime->getLocale() instanceof Ru);
    }

    public function testAddCustomFormat()
    {
        $datetime = new DateTime('1988-11-12');
        $datetime->addCustomFormat('shortDate', 'j / F / Y');
        $this->assertSame($datetime->shortDate(), '12 / November / 1988');
        $this->assertArrayHasKey('shortDate', $datetime->getCustomFormats());
    }

    public function testAddFormatOption()
    {
        $this->dateTime->addFormatOption('ago', function (DateTime $datetime) {
            return floor((time() - $datetime->getTimestamp()) / 86400) . ' days ago';
        });
        $ago = floor((time() - $this->dateTime->set('1988-11-12')->getTimestamp()) / 86400);
        $this->assertSame($this->dateTime->set('1988-11-12')->format('d F Y, ago'), "12 November 1988, {$ago} days ago");

        // duplicate
        $this->dateTime->addFormatOption('ago', function (DateTime $datetime) {
            return floor((time() - $datetime->getTimestamp()) / 86400) . ' days ago';
        });
    }

    public function testDiff()
    {
        $dateTime = $this->dateTime->set('1988-11-12');
        $this->assertSame($dateTime->diff(time())->w, (int)floor($dateTime->diff(time())->days / 7));

        $dateInterval = $this->dateTime->diff('1988-11-12');
        $this->assertSame($dateInterval->w, (int)floor($dateInterval->days / 7) * -1);

        $dateInterval = $this->dateTime->diff('1988-11-12', true);
        $this->assertSame($dateInterval->w, (int)floor($dateInterval->days / 7));
    }

    /**
     * @dataProvider providerIsTrue
     */
    public function testIsDateTrue($value)
    {
        $this->assertTrue(DateTime::is($value));
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
        $this->assertFalse(DateTime::is($value));
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
        $this->assertTrue(DateTime::isTimestamp($value));
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
        $this->assertFalse(DateTime::isTimestamp($value));
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

    public function testTimezone()
    {
        $this->assertNotEquals(
            $this->dateTime->set('now', 'America/Chicago')->serverDatetime(),
            (new DateTime('now', new \DateTimeZone('Europe/Volgograd')))->serverDatetime()
        );
    }

}