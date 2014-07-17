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
        $this->assertSame(
            $this->template->replace('[[If
                                            ?subject=`:foo > 1 && :foo < 3`
                                            ?operands=`{"foo" : "[[+foo]]"}`
                                            ?then=`[[+result]]`
                                            ?else=`fail`
                                            ?addPlaceholders=`["result"]`
                                        ]]',
                                                   ['foo'=> 2, 'result' => 'success']
                          ),
            'success'
        );
        $this->assertSame(
            $this->template->replace('[[\rock\template\snippets\IfSnippet
                                            ?subject=`:foo > 1 && :foo < 3`
                                            ?operands=`{"foo" : "[[+foo]]"}`
                                            ?then=`[[+result]]`
                                            ?else=`<b>fail</b>`
                                            ?addPlaceholders=`["result"]`
                                        ]]',
                                     ['foo'=> 5, 'result' => 'success']
            ),
            htmlentities('<b>fail</b>')
        );
    }
}
 