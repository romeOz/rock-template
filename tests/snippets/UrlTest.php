<?php

namespace rockunit\snippets;


use rock\snippets\UrlSnippet;
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
            'http://site.com/categories/?view=all&page=1#name',
            $this->template->replace(
                '[[url
                    ?modify=`{"0" : "http://site.com/categories/?view=all", "page" : 1, "#" : "name"}`
                    ?scheme=`abs`
                ]]'
            )
        );

        // modify url + remove args + add args
        $this->assertSame(
            'http://site.com/categories/?page=1',
            $this->template->getSnippet('url',
                [
                    'modify' => ['http://site.com/categories/?view=all', '!view', 'page' => 1],
                    'scheme' => UrlSnippet::ABS
                ]
            )
        );

        // modify url + remove all args
        $template = new Template();
        $this->assertSame(
            'http://site.com/categories/',
            $template->getSnippet('url',
                [
                    'modify' => ['http://site.com/categories/?view=all#name', '!', '!#'],
                    'scheme' => UrlSnippet::ABS
                ]
            )
        );

        $this->assertSame(
            'http://site.com/items/save',
            $template->getSnippet('url',
                [
                    'modify' => ['/items/save'],
                    'scheme' => UrlSnippet::ABS
                ]
            )
        );

        // modify current url
        $this->assertSame(
            'http://site.com/',
            $this->template->getSnippet('url',
                [
                    'scheme' => UrlSnippet::ABS
                ]
            )
        );
    }
}