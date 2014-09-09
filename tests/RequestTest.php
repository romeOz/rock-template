<?php

namespace rockunit;


use rock\template\request\Request;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'] = 'site.com';
    }

    protected function tearDown()
    {
        parent::tearDown();
        static::setUpBeforeClass();
    }

    public function testRequest()
    {
        $this->assertSame((new Request())->getBasePort(), 80);

        $url = new Request();
        $url->setBasePort(443);
        $this->assertSame($url->getBasePort(), 443);

        $this->assertSame((new Request())->getSecurePort(), 443);

        $url = new Request();
        $url->setSecurePort(444);
        $this->assertSame($url->getSecurePort(), 444);
    }
}
 