<?php

namespace rockunit\snippets;


use rockunit\template\TemplateCommon;

class IfSnippetTest extends TemplateCommon
{
    protected function calculatePath()
    {
        $this->path = __DIR__ . '/data';
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        static::clearRuntime();
    }

    public function testGet()
    {
        $actual =             $this->template->replace('[[if
                                            ?subject=`:foo > 1 && :foo < 3`
                                            ?operands=`{"foo" : "[[+foo]]"}`
                                            ?then=`[[+$parent.result]][[+result]]`
                                            ?else=`fail`
                                        ]]',
            ['foo'=> 2, 'result' => 'success']
        );
        $this->assertSame('success', $actual);

        $actual =             $this->template->replace('[[if
                                            ?subject=`:foo > 1 && :foo < 3`
                                            ?operands=`{"foo" : "[[+foo]]"}`
                                            ?then=`[[+result]]`
                                            ?else=`<b>fail</b>`
                                            ?addPlaceholders=`["result"]`
                                        ]]',
            ['foo'=> 5, 'result' => 'success']
        );
        $this->assertSame(htmlentities('<b>fail</b>'), $actual);
    }
}
 