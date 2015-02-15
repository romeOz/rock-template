<?php

namespace rockunit\snippets;


use rock\date\DateTime;
use rockunit\template\TemplateCommon;

class DateTest extends TemplateCommon
{
    protected function calculatePath()
    {
        $this->path = __DIR__ . '/data';
    }

    public function testGet()
    {
        $actual = $this->template->replace('[[Date
                        ?date=`2012-02-12 15:01`
                        ?format=`j F Y H:i`
                    ]]'
        );
        $this->assertSame('12 February 2012 15:01', $actual);

        $actual = $this->template->replace('[[Date
                        ?date=`2012-02-12 15:01`
                        ?format=`j n`
                    ]]'
        );
        $this->assertSame('12 2', $actual);

        // default format
        $this->assertSame(
            '2012-02-12 15:01:00',
            $this->template->getSnippet('Date', ['date' => '2012-02-12 15:01'])
        );

        //timezone
        $this->assertSame(
            (new DateTime('now', 'America/Chicago'))->isoDatetime(),
            $this->template->getSnippet('Date', ['timezone' => 'America/Chicago'])
        );

        $this->assertSame(
            '2012-02-12 05:01:00',
            $this->template->getSnippet('Date', ['date' => '2012-02-12 15:01', 'timezone' => 'America/Chicago'])
        );
    }
}
 