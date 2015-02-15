<?php

namespace rockunit\snippets;


use rockunit\template\TemplateCommon;

class FormulaTest extends TemplateCommon
{
    protected function calculatePath()
    {
        $this->path = __DIR__ . '/data';
    }

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        static::clearRuntime();
    }


    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        static::clearRuntime();
    }

    public function testGet()
    {
        $actual = $this->template->replace('[[Formula
                        ?subject=`:num - 1`
                        ?operands=`{"num" : "[[+num]]"}`
                    ]]',
            ['num'=> 8]
        );
        $this->assertSame(7, $actual);

        // null
        $this->assertSame('', $this->template->replace('[[Formula]]'));

        $this->assertSame(7, $this->template->getSnippet('Formula', ['subject' => ':num - 1', 'operands' => ['num' => 8]]));

        // string
        $this->assertSame(-1, $this->template->getSnippet('Formula', ['subject' => ':num - 1', 'operands' => ['num' => 'foo']]));
    }
}
 