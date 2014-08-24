<?php

namespace rockunit\snippets;

use rock\template\execute\EvalExecute;
use rock\template\snippets\IfSnippet;
use rock\template\Template;
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

        $template = new Template();
        $template->snippets = [
            'IfThen' => [
                'class' => IfSnippet::className(),
                'execute' => function () {return new EvalExecute();}
            ]
        ];
        $this->assertSame(
            $template->getSnippet(
                'IfThen',
                [
                    'subject' => ':foo === "text"',
                    'operands' => ["foo" => 'text'],
                    'then' => '<b>success</b>',
                    'else' => 'fail'
                ],
                false
            ),
            '<b>success</b>'
        );
    }
}
 