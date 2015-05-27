<?php

namespace rockunit\snippets;


use rock\snippets\Url;
use rock\template\Template;
use rockunit\template\TemplateCommon;

class UrlTest extends TemplateCommon
{
    protected function calculatePath()
    {
        $this->path = __DIR__ . '/data';
    }

    public function testGet()
    {
        $this->assertSame(
            'http://site.com/parts/categories/news/?view=all&page=1#name',
            $this->template->replace('[[url
                        ?url=`http://site.com/categories/?view=all`
                        ?addArgs=`{"page" : 1}`
                        ?beginPath=`/parts`
                        ?endPath=`/news/`
                        ?anchor=`name`
                        ?scheme=`abs`
                    ]]'
            )
        );

        // replacing URL
        $this->assertSame(
            'http://site.com/?view=all',
            $this->template->replace('[[url
                        ?url=`http://site.com/news/?view=all`
                        ?replace=`["news/", ""]`
                        ?scheme=`abs`
                    ]]'
            )
        );

        // modify url + remove args + add args
        $this->assertSame(
            'http://site.com/categories/?page=1',
            $this->template->getSnippet('url',
                [
                    'url' => 'http://site.com/categories/?view=all',
                    'removeArgs' => ['view'],
                    'args' => ['page' => 1],
                    'scheme' => Url::ABS
                ]
            )
        );

        // modify url + remove all args
        $template = new Template();
        $this->assertSame(
            'http://site.com/categories/',
            $template->getSnippet('url',
                [
                    'url' => 'http://site.com/categories/?view=all#name',
                    'removeAllArgs' => true,
                    'removeAnchor' => true,
                    'scheme' => Url::ABS
                ]
            )
        );

        // modify self url + input null
        $this->assertSame(
            'http://site.com/',
            $this->template->getSnippet('url',
                [
                    'removeAllArgs' => true,
                    'scheme' => Url::ABS
                ]
            )
        );
    }
}