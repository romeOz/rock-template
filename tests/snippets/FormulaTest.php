<?php

namespace rockunit\snippets;


use rockunit\template\TemplateCommon;

class FormulaTest extends TemplateCommon
{
    protected function calculatePath()
    {
        $this->path = __DIR__ . '/data';
    }

    public function testGet()
    {
        $actual = $this->template->replace(
            '[[formula
                ?subject=`:num - 1`
                ?operands=`{"num" : "[[+num]]"}`
            ]]',
            ['num'=> 8]
        );
        $this->assertSame(7, $actual);

        // null
        $this->assertSame('', $this->template->replace('[[formula]]'));

        $this->assertSame(7, $this->template->getSnippet('formula', ['subject' => ':num - 1', 'operands' => ['num' => 8]]));

        // string
        $this->assertSame(-1, $this->template->getSnippet('formula', ['subject' => ':num - 1', 'operands' => ['num' => 'foo']]));
    }
}
 