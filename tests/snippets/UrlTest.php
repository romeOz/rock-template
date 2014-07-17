<?php

namespace rockunit\snippets;

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
            $this->template->replace('[[Url
                        ?url=`http://site.com/categories/?view=all`
                        ?args=`{"page" : 1}`
                        ?beginPath=`/parts`
                        ?endPath=`/news/`
                        ?anchor=`name`
                        ?const=`32`
                    ]]'
            ),
            'http://site.com/parts/categories/news/?view=all&page=1#name'
        );
    }
}
 