<?php

namespace rockunit\snippets;


use rockunit\template\TemplateCommon;

class DateTest extends TemplateCommon
{
    protected function calculatePath()
    {
        $this->path = __DIR__ . '/data';
    }

    public function testGet()
    {
        $this->assertSame(
            $this->template->replace('[[Date
                        ?date=`2014-02-12 15:01`
                        ?format=`dmyhm`
                    ]]'
            ),
            '12 February 15:01'
        );

        $this->assertSame(
            $this->template->replace('[[Date
                        ?date=`2014-02-12 15:01`
                        ?format=`j n`
                    ]]'
            )
            ,
            '12 2'
        );
    }
}
 