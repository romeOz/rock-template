<?php

namespace rockunit\snippets;


use rockunit\template\TemplateCommon;

class ForSnippetTest extends TemplateCommon
{
    protected function calculatePath()
    {
        $this->path = __DIR__ . '/data';
    }

    public function testGet()
    {
        $actual = $this->template->replace(
            '[[!for?count=`2`
                ?tpl=`@INLINE<b>[[+$parent.title]][[+title]]</b>`
                ?addPlaceholders=`["$parent.title"]`
                ?wrapperTpl=`@INLINE<p>[[!+output]]</p>`
            ]]',
            ['title' => 'hello world']
        );
        $this->assertSame(
            '<p><b>hello worldhello world</b><b>hello worldhello world</b></p>',
            $actual
        );
    }
}
 