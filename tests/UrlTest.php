<?php

namespace rockunit;


use rock\template\url\Url;

class UrlTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        $_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'] = 'site.com';
    }

    public function testGetCurrentUrl()
    {
        // relative
        $url = new Url();
        $this->assertSame($url->getRelativeUrl(),'/');

        // http
        $url = new Url();
        $this->assertSame($url->getHttpUrl(),'http://site.com/');

        // https
        $url = new Url();
        $this->assertSame($url->getHttpsUrl(),'https://site.com/');

        // absolute
        $url = new Url();
        $this->assertSame($url->getAbsoluteUrl(),'http://site.com/');

        $this->assertSame(
            $url->setArgs(['page' => 1])
                ->addBeginPath('/parts')
                ->addEndPath('/news/')
                ->addAnchor('name')
                ->getHttpsUrl(),
            'https://site.com/parts/news/?page=1#name'
        );

        // build + strip_tags
        $url = new Url();
        $this->assertSame(
            $url
                ->addBeginPath('/parts')
                ->addEndPath('/<b>news</b>/')
                ->addAnchor('name')
                ->removeAllArgs()
                ->setArgs(['page' => 1])
                ->addArgs(['view'=> 'all'])
                ->getRelativeUrl(),
            '/parts/news/?page=1&view=all#name'
        );

        // build + remove args
        $url = new Url();
        $this->assertSame(
            $url
                ->setArgs(['page' => 1])
                ->addBeginPath('/parts')
                ->addEndPath('/news/')
                ->addAnchor('name')
                ->removeAllArgs()
                ->getRelativeUrl(),
            '/parts/news/#name'
        );

        // build + add args
        $url = new Url();
        $this->assertSame(
            $url
                ->addBeginPath('/parts')
                ->addEndPath('/news/')
                ->addAnchor('name')
                ->removeAllArgs()
                ->addArgs(['view'=> 'all'])
                ->getRelativeUrl(),
            '/parts/news/?view=all#name'
        );
    }

    public function testGetCustomUrl()
    {
        // relative
        $url = new Url();
        $url->set('http://site.com/?page=2#name');
        $this->assertSame($url->getRelativeUrl(),'/?page=2#name');

        // https
        $url = new Url();
        $url->set('http://site.com/?page=2#name');
        $this->assertSame($url->getHttpsUrl(),'https://site.com/?page=2#name');

        // http
        $url = new Url();
        $url->set('https://site.com/?page=2#name');
        $this->assertSame($url->getHttpUrl(),'http://site.com/?page=2#name');

        // build + add args + self host
        $url = new Url();
        $url->set('http://site2.com/?page=2#name');
        $this->assertSame(
            $url
                ->addBeginPath('/parts')
                ->addEndPath('/news/')
                ->addAnchor('name')
                ->addArgs(['view'=> 'all'])
                ->getAbsoluteUrl(true),
            'http://site.com/parts/news/?page=2&view=all#name'
        );

        // build + remove args
        $url = new Url();
        $url->set('http://site2.com/?page=2#name');
        $this->assertSame(
            $url
                ->addBeginPath('/parts')
                ->addEndPath('/news/')
                ->addArgs(['view'=> 'all'])
                ->removeArgs(['page'])
                ->getAbsoluteUrl(),
            'http://site2.com/parts/news/?view=all#name'
        );
    }
}
 