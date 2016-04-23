<?php

namespace rockunit\template\twig;


use rock\components\Model;
use rock\template\Html;
use rock\template\Template;
use rock\template\twig\ViewRenderer;
use rockunit\template\TemplateCommon;

class ViewRendererTest extends TemplateCommon
{
    protected function calculatePath()
    {
        $this->path = __DIR__ . '/views';
    }

    protected function tearDown()
    {
        parent::tearDown();
        //FileHelper::deleteDirectory(ROCKUNIT_RUNTIME);
    }

    public function testAppGlobal()
    {
        $this->template->title = 'Test';
        $content = $this->template->render('@rockunit.views\layout.twig', [], null, true);
        $this->assertEquals(1, preg_match('#<title>Test</title>#', $content), 'Content does not contain charset:' . $content);
    }

    public function testLexerOptions()
    {
        $content = $this->template->getChunk('@rockunit.views/comments.twig');

        $this->assertFalse(strpos($content, 'CUSTOM_LEXER_TWIG_COMMENT'), 'Custom comment lexerOptions were not applied: ' . $content);
        $this->assertTrue(strpos($content, 'DEFAULT_TWIG_COMMENT') !== false, 'Default comment style was not modified via lexerOptions:' . $content);
    }

//    public function testForm()
//    {
//        $model = new Singer();
//        $content = $this->template->getChunk('@rockunit.views/form.twig', ['model' => $model]);
//        var_dump($content);
//        //$this->assertEquals(1, preg_match('#<form id="login-form" class="form-horizontal" action="/form-handler" method="post">.*?</form>#s', $content), 'Content does not contain form:' . $content);
//    }

    public function testCalls()
    {
        $model = new Singer();
        $content = $this->template->getChunk('@rockunit.views/calls.twig', ['model' => $model]);
        $this->assertFalse(strpos($content, 'silence'), 'silence should not be echoed when void() used: ' . $content);
        $this->assertTrue(strpos($content, 'echo') !== false, 'echo should be there:' . $content);
        $this->assertTrue(strpos($content, 'variable') !== false, 'variable should be there:' . $content);
    }

    public function testInheritance()
    {
        $content = $this->template->getChunk('@rockunit.views/extends2.twig');
        $this->assertTrue(strpos($content, 'Hello, I\'m inheritance test!') !== false, 'Hello, I\'m inheritance test! should be there:' . $content);
        $this->assertTrue(strpos($content, 'extends2 block') !== false, 'extends2 block should be there:' . $content);
        $this->assertFalse(strpos($content, 'extends1 block') !== false, 'extends1 block should not be there:' . $content);

        $content = $this->template->getChunk('@rockunit.views/extends3.twig');
        $this->assertTrue(strpos($content, 'Hello, I\'m inheritance test!') !== false, 'Hello, I\'m inheritance test! should be there:' . $content);
        $this->assertTrue(strpos($content, 'extends3 block') !== false, 'extends3 block should be there:' . $content);
        $this->assertFalse(strpos($content, 'extends1 block') !== false, 'extends1 block should not be there:' . $content);
    }

    public function testChangeTitle()
    {
        $this->template->title = 'Original title';

        $content = $this->template->getChunk('@rockunit.views/changeTitle.twig');
        $this->assertTrue(strpos($content, 'New title') !== false, 'New title should be there:' . $content);
        $this->assertFalse(strpos($content, 'Original title') !== false, 'Original title should not be there:' . $content);
    }

    public function testNullsInAr()
    {
        $order = new NullClass();
        $this->assertEmpty($this->template->getChunk('@rockunit.views\nulls.twig', ['order' => $order]));

        $order = new NotNullClass();
        $this->assertEquals('test', $this->template->getChunk('@rockunit.views\nulls.twig', ['order' => $order]));
    }

    public function testSimpleFilters()
    {
        $content = $this->template->getChunk('@rockunit.views\simpleFilters1.twig');
        $this->assertEquals($content, 'Gjvt');
        $content = $this->template->getChunk('@rockunit.views\simpleFilters2.twig');
        $this->assertEquals($content, 'val42');
        $content = $this->template->getChunk('@rockunit.views\simpleFilters3.twig');
        $this->assertEquals($content, 'Gjvt');
        $content = $this->template->getChunk('@rockunit.views\simpleFilters4.twig');
        $this->assertEquals($content, 'val42');
        $content = $this->template->getChunk('@rockunit.views\simpleFilters5.twig');
        $this->assertEquals($content, 'Gjvt');
    }

    public function testSimpleFunctions()
    {
        $content = $this->template->getChunk('@rockunit.views\simpleFunctions1.twig');
        $this->assertEquals($content, 'Gjvt');
        $content = $content = $this->template->getChunk('@rockunit.views\simpleFunctions2.twig');
        $this->assertEquals($content, 'val43');
        $content = $content = $this->template->getChunk('@rockunit.views\simpleFunctions3.twig');
        $this->assertEquals($content, 'Gjvt');
        $content = $content = $this->template->getChunk('@rockunit.views\simpleFunctions4.twig');
        $this->assertEquals($content, 'val43');
        $content = $content = $this->template->getChunk('@rockunit.views\simpleFunctions5.twig');
        $this->assertEquals($content, '6');
    }

    public function test_()
    {
        var_dump($this->template->render('@rockunit.views\layout.twig'));
    }

    /**
     * @inheritdoc
     */
    protected function getTemplate(array $config = [])
    {
        $config = [
            'engines' => [
                'twig' => [
                    'class' => ViewRenderer::className(),
                    'options' => [
                        'cache' => false,
                    ],
                    'globals' => [
                        'html' => Html::className(),
                        'pos_begin' => Template::POS_BEGIN,
                    ],
                    'functions' => [
                        //'t' => '\rock\i18n\i18n::t',
                        'json_encode' => '\rock\helpers\Json::encode',
                        new \Twig_SimpleFunction('rot13', 'str_rot13'),
                        new \Twig_SimpleFunction('add_*', function ($symbols, $val) {
                            return $val . $symbols;
                        }, ['is_safe' => ['html']]),
                        'callable_rot13' => function($string) {
                            return str_rot13($string);
                        },
                        'callable_add_*' => function ($symbols, $val) {
                            return $val . $symbols;
                        },
                        'callable_sum' => function ($a, $b) {
                            return $a + $b;
                        }
                    ],
                    'filters' => [
                        'string_rot13' => 'str_rot13',
                        new \Twig_SimpleFilter('rot13', 'str_rot13'),
                        new \Twig_SimpleFilter('add_*', function ($symbols, $val) {
                            return $val . $symbols;
                        }, ['is_safe' => ['html']]),
                        'callable_rot13' => function($string) {
                            return str_rot13($string);
                        },
                        'callable_add_*' => function ($symbols, $val) {
                            return $val . $symbols;
                        }
                    ],
                    'lexerOptions' => [
                        'tag_comment' => [ '{*', '*}' ],
                    ],
                ],
            ],
        ];
        return parent::getTemplate($config);
    }

}

class NotNullClass {
    public $foo = 'test';
}

class NullClass {

}

class Singer extends Model {
    public $firstName;
    public $lastName;
    public $test;
}