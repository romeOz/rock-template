<?php

namespace rockunit\template;


use League\Flysystem\Adapter\Local;
use rock\base\Alias;
use rock\file\FileManager;
use rock\helpers\StringHelper;
use rock\image\ImageProvider;
use rock\template\TemplateException;
use rock\template\Template;
use rockunit\template\snippets\NullSnippet;
use rockunit\template\snippets\TestSnippet;

/**
 * @group template
 */
class TemplateTest extends TemplateCommon
{
    protected function calculatePath()
    {
        $this->path = __DIR__ . '/data';
    }

    public function testPlaceholderAsMagic()
    {
        $this->template['foo'] = 'foo text';
        $this->assertTrue(isset( $this->template['foo']));
        $this->assertSame('foo text',  $this->template['foo']);
        unset( $this->template['foo']);
        $this->assertFalse(isset( $this->template['foo']));
    }

    public function testPlaceholder()
    {
        // add
        $this->template['foo'] = 'foo text';
        $this->template->addPlaceholder('$root.bar', 'bar text');
        $this->template->addPlaceholder('$parent.baz', 'baz text');

        // exists
        $this->assertTrue($this->template->existsPlaceholder('bar'));
        $this->assertTrue($this->template->existsPlaceholder('$parent.bar'));
        $this->assertTrue($this->template->existsPlaceholder('$root.bar'));

        // get all
        $this->assertSame(['foo' => 'foo text', 'bar' => 'bar text', 'baz' => 'baz text'], $this->template->getAllPlaceholders());

        // remove
        $this->template->removePlaceholder('bar');
        $this->assertFalse($this->template->existsPlaceholder('bar'));
        $this->assertFalse($this->template->existsPlaceholder('$parent.bar'));
        $this->assertFalse($this->template->existsPlaceholder('$root.bar'));
        $this->assertSame(['foo' => 'foo text', 'baz' => 'baz text'], $this->template->getAllPlaceholders());

        // remove multi
        $this->template->removeMultiPlaceholders(['baz']);
        $this->assertFalse($this->template->existsPlaceholder('baz'));
        $this->assertSame(['foo' => 'foo text'], $this->template->getAllPlaceholders());

        // null name
        $this->template->removePlaceholder(null);
    }

    public function testParent()
    {
        $expected = static::removeSpace(file_get_contents($this->path . '/parent/_html.html'));
        $actual = static::removeSpace($this->template->render($this->path . '/parent/foo', ['name' => 'Tom']));
        $this->assertSame($expected, $actual);

        $expected = static::removeSpace(file_get_contents($this->path . '/parent/_php.html'));
        $actual = static::removeSpace($this->template->render($this->path . '/parent/foo.php', ['name' => 'Tom']));
        $this->assertSame($expected, $actual);
    }

    public function testConst()
    {
        $this->template->addConst('foo', 'foo text');
        $this->assertSame('test: foo text', $this->template->replace('test: [[++foo]]'));
        $this->assertTrue($this->template->existsConst('foo'));
        $this->assertSame('foo text', $this->template->getConst('foo'));

        $this->template->addConst('foo', 'bar text', false, true);
        $this->assertSame('bar text', $this->template->getConst('foo'));
    }

    public function testAlias()
    {
        Alias::setAlias('@bar', '/bar');
        $this->assertSame('path: /bar', $this->template->replace('path: [[@@bar]]'));
    }

    /**
     * @throws TemplateException
     * @depends testConst
     */
    public function testConstThrowException()
    {
        $this->setExpectedException(TemplateException::className());
        $this->template->addConst('foo', 'foo text');
    }

    public function testRenderAsRock()
    {
        $template = $this->template;
        $template->addMultiPlaceholders(['foo' => ['bar' => '<b>text_bar</b>'], 'baz' => ['bar' => '<b>text_baz</b>']]);
        $this->assertSame(file_get_contents($this->path . '/_layout.html'), $template->render($this->path . '/layout', ['text' => 'world']));
    }

    public function testRenderUnknownFileException()
    {
        $this->setExpectedException(TemplateException::className());
        $this->template->render($this->path . '/unknown');
    }

    public function testRenderMetaTags()
    {
        $config = [
            'head' => '<!DOCTYPE html>
            <!--[if !IE]>--><html class="no-js"><!--<![endif]-->',
            'title' => 'Demo',
            'metaTags' => [
                '<meta charset="UTF-8">',
                'language' => '<meta http-equiv="Content-Language" content="en">',
                'robots' => '<meta name="robots" content="all">',
                'description' => '<meta name="description" content="about">',
            ],
            'linkTags' => [
                '<link type="image/x-icon" href="/favicon.ico?10" rel="Shortcut Icon">',
                '<link type="application/rss+xml" href="/feed.rss" title="rss"  rel="alternate">'
            ],
            'css' => ['<style>.title {color: #354a57;}</style>'],
            'cssFiles' => [
                Template::POS_HEAD => [
                    '<link href="//site.com/assets/css/main.css" rel="stylesheet" media="screen, projection">'
                ],
                Template::POS_END => [
                    '<!--[if !(IE) | (gt IE 8)]><link href="//site.com/assets/css/footer.css" rel="stylesheet" media="screen, projection"><![endif]-->'
                ]
            ],
            'jsFiles' => [
                Template::POS_HEAD => [
                    '<!--[if lt IE 9]><script src="//site.com/assets/head.js"></script><![endif]-->'
                ],
                Template::POS_BEGIN => [
                    '<!--[if lt IE 9]><script src="//site.com/assets/begin.js"></script><![endif]-->'
                ],
                Template::POS_END => [
                    '<script src="//site.com/assets/end.js"></script>'
                ]
            ],
            'js' => [
                Template::POS_HEAD => [
                    'head = "test"'
                ],
                Template::POS_BEGIN => [
                    'begin = "test"'
                ],
                Template::POS_END => [
                    'end = "test"'
                ]
            ]
        ];

        // Rock engine
        $this->assertSame(
            static::removeSpace(file_get_contents($this->path . '/_meta.html')),
            static::removeSpace($this->getTemplate($config)->render($this->path . '/meta.html', ['about' => 'demo']))
        );
        // Rock engine as default
        $this->assertSame(
            static::removeSpace($this->getTemplate($config)->render($this->path . '/meta', ['about' => 'demo'])),
            static::removeSpace(file_get_contents($this->path . '/_meta.html'))
        );

        // PHP engine
        $this->assertSame(
            static::removeSpace($this->getTemplate($config)->render($this->path . '/meta.php', ['about' => 'demo'])),
            static::removeSpace(file_get_contents($this->path . '/_meta.html'))
        );

        // register
        $template = $this->getTemplate();
        $template->setHead('<!DOCTYPE html>
            <!--[if !IE]>--><html class="no-js"><!--<![endif]-->');
        $template->setTitle('Demo');
        $template->registerMetaTag(['charset' => 'UTF-8']);
        $template->registerMetaTag(['http-equiv' => 'Content-Language', 'content' => 'en'], 'language');
        $template->registerMetaTag(['name' => 'robots', 'content' => 'all'], 'robots');
        $template->registerMetaTag(['name' => 'description', 'content' => 'about'], 'description');
        $template->registerLinkTag(['rel' => 'Shortcut Icon', 'type' => 'image/x-icon', 'href' => '/favicon.ico?10']);
        $template->registerLinkTag(['rel' => 'alternate', 'type' => 'application/rss+xml', 'title' => 'rss', 'href' => '/feed.rss'], 'rss');
        $template->registerCssFile('/assets/css/main.css', ['media' => 'screen, projection']);
        $template->registerCssFile(
            '/assets/css/footer.css',
            [
                'position' => Template::POS_END,
                'media' => 'screen, projection',
                'condition' => '!(IE) | (gt IE 8)',
            ]
        );

        $template->registerJsFile(
            '/assets/head.js',
            [
                'position' => Template::POS_HEAD,
                'wrapperTpl' => '@INLINE<!--[if lt IE 9]>[[!+output]]<![endif]-->'
            ]
        );
        $template->registerJsFile(
            '/assets/begin.js',
            [
                'position' => Template::POS_BEGIN,
                'condition' => 'lt IE 9',
            ]
        );
        $template->registerJsFile('/assets/end.js');
        $template->registerJs('head = "test"');
        $template->registerJs('begin = "test"', Template::POS_BEGIN);
        $template->registerJs('end = "test"', Template::POS_END);
        $template->registerCss('.title {color: #354a57;}');
        $this->assertSame(
            static::removeSpace(file_get_contents($this->path . '/_meta.html')),
            static::removeSpace($template->render($this->path . '/meta.php', ['about' => 'demo']))
        );
    }

    public function testRelativePathTpl()
    {
        $this->assertSame('hellohello', $this->template->getChunk('@rockunit.views\relative\chunk'));
        $this->assertSame('hellohello', $this->template->getChunk('@rockunit.views\relative\chunk.php'));
    }

    /**
     * @expectedException \rock\template\TemplateException
     * @dataProvider providerRelativePathException
     * @param string $path
     */
    public function testRelativePathException($path)
    {
        $this->template->replace($path);
    }

    public function providerRelativePathException()
    {
        return [
            ['[[$/home/romeo/subchunk]]'],
            ['[[$../../../../../../../subchunk]]']
        ];
    }

    public function testHasChunk()
    {
        $this->assertTrue($this->template->existsChunk($this->path . '/layout'));
        $this->assertTrue($this->template->existsChunk($this->path . '/layout.php'));
    }

    public function testConditionFilter()
    {
        $this->assertSame($this->template->getChunk('@rockunit.views/condition_filter.html', ['title' => '<b>test</b>', 'number' => 3, 'num' => 0]), file_get_contents($this->path . '/_condition_filter.html'));

        // unknown param
        $this->setExpectedException(TemplateException::className());
        $this->template->replace('[[+content:if&foo=`null`&then=`[[!+title]]`]]');
    }

    public function testIfException()
    {
        $this->setExpectedException(TemplateException::className());
        $this->template->replace('[[+title:if]]', ['title' => 'Hello World']);
    }

    public function testFilters()
    {
        // php-function filter
        $this->assertSame(2, $this->template->replace('[[+title:substr&start=`6`&length=`2`:strlen]]', ['title' => 'hello world']));
        $this->template->removeAllPlaceholders();

        // replaceTpl
        $this->assertSame('HELLO', $this->template->replace('[[+content:replaceTpl&title=`[[+title]]`:upper]]', ['content' => '[[+title]]', 'title' => 'hello']));
        $this->template->removeAllPlaceholders();

        // size as string
        $this->assertSame(3, $this->template->replace('[[!+title:size]]', ['title' => 'абв']));
        $this->template->removeAllPlaceholders();

        // unserialize
        $this->assertSame('bar_text', $this->template->replace('[[!+title:unserialize&key=`foo.bar`]]', ['title' => json_encode(['baz' => 'baz_text', 'foo' => ['bar' => 'bar_text']])]));
        $this->template->removeAllPlaceholders();

        // unserialize + input array + size
        $this->assertSame(1, $this->template->replace('[[!+title:toArray:size]]', ['title' => '{"bar" : {"subbar" : "test"}}']));
        $this->template->removeAllPlaceholders();

        // unserialize + input null
        $this->assertSame('', $this->template->replace('[[!+title:unserialize&key=`foo.bar`]]', ['title' => null]));
        $this->template->removeAllPlaceholders();

        // truncate
        $this->assertSame('Hello...', $this->template->replace('[[+title:truncate&length=`5`]]', ['title' => 'Hello world']));
        $this->template->removeAllPlaceholders();

        // truncate + input null
        $this->assertSame('', $this->template->replace('[[+title:truncate]]', ['title' => null]));
        $this->template->removeAllPlaceholders();

        // truncate words
        $this->assertSame('Hello...', $this->template->replace('[[+title:truncateWords&length=`6`]]', ['title' => 'Hello world']));
        $this->template->removeAllPlaceholders();

        // truncate words + input null
        $this->assertSame('', $this->template->replace('[[+title:truncateWords]]', ['title' => null]));
        $this->template->removeAllPlaceholders();

        // lower
        $this->assertSame('hello world', $this->template->replace('[[+title:lower]]', ['title' => 'Hello World']));
        $this->template->removeAllPlaceholders();

        // upper
        $this->assertSame('HELLO WORLD', $this->template->replace('[[+title:upper]]', ['title' => 'Hello World']));
        $this->template->removeAllPlaceholders();

        // upper first character
        $this->assertSame('Hello world', $this->template->replace('[[+title:upperFirst]]', ['title' => 'hello world']));
        $this->template->removeAllPlaceholders();

        // trim pattern
        $this->assertSame('Heo World', $this->template->replace('[[+title:trimPattern&pattern=`/l{2}/`]]', ['title' => 'Hello World']));
        $this->template->removeAllPlaceholders();

        // trim pattern as empty
        $this->assertSame('', $this->template->replace('[[+title:trimPattern&pattern=`/l{2}/`]]', ['title' => '']));
        $this->template->removeAllPlaceholders();

        // contains success
        $this->assertSame('Hello World', $this->template->replace('[[+title:contains&is=`Wo`&then=`[[+title]]`]]', ['title' => 'Hello World']));
        $this->template->removeAllPlaceholders();

        // contains fail
        $this->assertSame('', $this->template->replace('[[+title:contains&is=`woo`&then=`[[+title]]`]]', ['title' => 'Hello World']));
        $this->template->removeAllPlaceholders();

        // isParity success
        $this->assertSame('success', $this->template->replace('[[+num:isParity&then=`success`]]', ['num' => 2]));
        $this->template->removeAllPlaceholders();

        // isParity fail
        $this->assertSame('fail', $this->template->replace('[[+num:isParity&then=`success`&else=`fail`]]', ['num' => '3']));
        $this->template->removeAllPlaceholders();

        // to positive
        $this->assertSame(7, $this->template->replace('[[+num:positive]]', ['num' => '7']));
        $this->template->removeAllPlaceholders();

        // to positive
        $this->assertSame(0, $this->template->replace('[[+num:positive]]', ['num' => '-7']));
        $this->template->removeAllPlaceholders();

        // encode
        $this->assertSame(StringHelper::encode('<b>Hello World</b>'), $this->template->replace('[[!+title:encode]]', ['title' => '<b>Hello World</b>']));
        $this->template->removeAllPlaceholders();

        // decode
        $this->assertSame('<b>Hello World</b>', $this->template->replace('[[+title:decode]]', ['title' => '<b>Hello World</b>']));
        $this->template->removeAllPlaceholders();

        // modify url
        $replace = '[[+url:modifyUrl
                        &modify=`{"0" : "!", "page" : 1, "#" : "name"}`
                        &scheme=`abs`
                     ]]';
        $this->assertSame('//site.com/categories/?page=1#name', $this->template->replace($replace, ['url' => '/categories/?view=all']));
        $this->template->removeAllPlaceholders();

        // modify url + remove args + add args
        $replace = '[[+url:modifyUrl
                        &modify=`{"0" : "!view","page" : 1}`
                        &scheme=`abs`
                     ]]';
        $this->assertSame('//site.com/categories/?page=1', $this->template->replace($replace, ['url' => '/categories/?view=all']));
        $this->template->removeAllPlaceholders();

        // modify url + remove all args
        $replace = '[[+url:modifyUrl
                        &modify=`["!", "!#"]`
                        &scheme=`abs`
                     ]]';
        $this->assertSame('//site.com/categories/', $this->template->replace($replace, ['url' => '/categories/?view=all#name']));
        $this->template->removeAllPlaceholders();

        // modify url + input null
        $replace = '[[+url:modifyUrl]]';
        $this->assertSame('#', $this->template->replace($replace, ['url' => '']));
        $this->template->removeAllPlaceholders();

        // modify date
        $replace = '[[+date:modifyDate&format=`j F Y H:i`]]';
        $this->assertSame('12 February 2012 15:01', $this->template->replace($replace, ['date' => '2012-02-12 15:01']));
        $this->template->removeAllPlaceholders();

        // modify date + locale
        $replace = '[[+date:modifyDate&format=`j F Y H:i`&locale=`ru`]]';
        $this->assertSame('12 февраля 2012 15:01', $this->template->replace($replace, ['date' => '2012-02-12 15:01']));
        $this->template->removeAllPlaceholders();

        // modify date + default format
        $replace = '[[+date:modifyDate]]';
        $this->assertSame('2012-02-12 15:01:00', $this->template->replace($replace, ['date' => '2012-02-12 15:01']));
        $this->template->removeAllPlaceholders();

        // modify date + default format + timezone
        $replace = '[[+date:modifyDate&timezone=`America/Chicago`]]';
        $this->assertSame('2012-02-12 09:01:00', $this->template->replace($replace, ['date' => '2012-02-12 15:01']));
        $this->template->removeAllPlaceholders();

        // modify date + input null
        $replace = '[[+date:modifyDate]]';
        $this->assertSame('', $this->template->replace($replace, ['date' => 'null']));
        $this->template->removeAllPlaceholders();

        // arrayToJson
        $replace = '[[+array:arrayToJson]]';
        $this->assertSame(json_encode(['foo' => 'test']), $this->template->replace($replace, ['array' => ['foo' => 'test']]));
        $this->template->removeAllPlaceholders();

        // arrayToJson + input null
        $replace = '[[+array:toJson]]';
        $this->assertSame('', $this->template->replace($replace, ['array' => '']));
        $this->template->removeAllPlaceholders();

        // jsonToArray + serialize
        $replace = '[[!+array:toArray:serialize]]';
        $this->assertSame(serialize(['foo' => 'test']), $this->template->replace($replace, ['array' => json_encode(['foo' => 'test'])]));
        $this->template->removeAllPlaceholders();

        // multiplication
        $this->assertSame(12, $this->template->replace('[[+num * `4`]]', ['num' => 3]));
        $this->template->removeAllPlaceholders();

        // repeat multiplication
        $this->assertSame(42, $this->template->replace('[[+num * `4` + `2`:formula&operator=`*`&operand=`3`]]', ['num' => 3]));
        $this->template->removeAllPlaceholders();

        // exponential expression
        $this->assertSame(9, $this->template->replace('[[+num ** `2`]]', ['num' => '3']));
        $this->template->removeAllPlaceholders();

        // division
        $this->assertSame(5, $this->template->replace('[[+num / `2`]]', ['num' => 10]));
        $this->template->removeAllPlaceholders();

        // modulus
        $this->assertSame(1, $this->template->replace('[[+num mod `2`]]', ['num' => 3]));
        $this->template->removeAllPlaceholders();

        // negation
        $this->assertSame(7, $this->template->replace('[[+num - `3`]]', ['num' => 10]));
        $this->template->removeAllPlaceholders();

        // addition
        $this->assertSame(12, $this->template->replace('[[+num + `5`]]', ['num' => 7]));
        $this->template->removeAllPlaceholders();

        // bit or
        $this->assertSame(10, $this->template->replace('[[+num | `8`]]', ['num' => 2]));
        $this->template->removeAllPlaceholders();

        // bit and
        $this->assertSame(2, $this->template->replace('[[+num & `10`]]', ['num' => 2]));
        $this->template->removeAllPlaceholders();

        // bit xor
        $this->assertSame(8, $this->template->replace('[[+num ^ `10`]]', ['num' => 2]));
        $this->template->removeAllPlaceholders();

        // bit shift the bits to the left
        $this->assertSame(256, $this->template->replace('[[+num << `7`]]', ['num' => 2]));
        $this->template->removeAllPlaceholders();

        // bit shift the bits to the right
        $this->assertSame(1, $this->template->replace('[[+num >> `1`]]', ['num' => 2]));
        $this->template->removeAllPlaceholders();

        // thumb
        $config = [
            'adapter' => [
                'class' => FileManager::className(),
                'adapter' => new Local(Alias::getAlias('@rockunit/data/imagine')),
            ],
            'adapterCache' => [
                'class' => FileManager::className(),
                'adapter' => new Local(Alias::getAlias('@rockunit/runtime/cache')),
            ],
        ];
        $placeholders = [
            'imageProvider' => new ImageProvider($config),
            'src' => 'large.jpg'
        ];
        $this->assertSame('/assets/cache/45x40/large.jpg', $this->template->replace('[[+src:thumb&h=`40`&w=`45`&imageProvider=`[[!+imageProvider]]`]]', $placeholders));
        $this->template->removeAllPlaceholders();

        $this->assertSame(2, $this->template->replace('[[+num:formula&operator=`<<`]]', ['num' => 2]));

        $this->setExpectedException(TemplateException::className());
        $this->template->replace('[[+num:formula&operator=`<!<!<`&operand=`4`]]', ['num' => 2]);
    }

    public function testAutomaticConversionArrayToJSON()
    {
        $array = ['foo' => 'test'];
        $this->assertSame(serialize($array), $this->template->replace('[[+array]]', ['array' => $array]));
    }

    public function testAutomaticConversionObjectToSerialize()
    {
        $object = new Foo();
        $this->assertSame(serialize($object), $this->template->replace('[[+object]]', ['object' => $object]));
    }

    public function testContainsException()
    {
        $this->setExpectedException(TemplateException::className());
        $this->template->replace('[[+title:contains]]', ['title' => 'Hello World']);
    }

    public function testIsParityException()
    {
        $this->setExpectedException(TemplateException::className());
        $this->template->replace('[[+num:isParity]]', ['num' => 2]);
    }

    public function testUnknownFilter()
    {
        $this->setExpectedException(TemplateException::className());
        $this->template->replace('[[+num:foo&operator=`<<`]]', ['num' => 2]);
    }

    public function testAutoEscape()
    {
        $this->template->setSanitize(Template::SANITIZE_DISABLE);
        $this->assertSame('Hello World', $this->template->replace('[[+title?sanitize=`2`]]', ['title' => '<b>Hello World</b>']));
        $this->template->removeAllPlaceholders();

        $this->template->setSanitize(Template::SANITIZE_ESCAPE | Template::SANITIZE_TO_TYPE);
        $this->assertSame('<b>Hello World</b>', $this->template->replace('[[+title?sanitize=`0`]]', ['title' => '<b>Hello World</b>']));
    }

    public function testGetNamePrefix()
    {
        // null
        $this->assertEmpty($this->template->getNamePrefix(null));
        $this->assertSame(
            [
                'prefix' => 'INLINE',
                'value' => '<b>foo</b>',
            ],
            $this->template->getNamePrefix('@INLINE<b>foo</b>')
        );
    }

    public function testRemovePrefix()
    {
        // null
        $this->assertEmpty($this->template->removePrefix(null));
        $this->assertSame('<b>foo</b>', $this->template->removePrefix('@INLINE<b>foo</b>'));
    }

    public function testSnippet()
    {
        $this->template->setSnippets(['test' => ['class' =>  TestSnippet::className()]]);
        $this->assertSame(StringHelper::encode('<b>test snippet</b>'), $this->template->getSnippet('test', ['param' => '<b>test snippet</b>']));
        $this->assertSame(StringHelper::encode('<b>test snippet</b>'), $this->template->getSnippet(new TestSnippet(), ['param' => '<b>test snippet</b>']));
        $this->assertSame(StringHelper::encode('<b>test snippet</b>'), $this->template->replace('[[test?param=`<b>test snippet</b>`]]'));
        $this->assertSame('<b>test snippet</b>', $this->template->replace('[[!test?param=`<b>test snippet</b>`]]'));

        // as callable
        $this->template->setSnippets([
           'test' => function () {
                return [
                    'class' => TestSnippet::className()
                ];
            }
        ]);
        $this->assertSame(StringHelper::encode('<b>test snippet</b>'), $this->template->getSnippet('test', ['param' => '<b>test snippet</b>']));
        $this->assertSame(StringHelper::encode('<b>test snippet</b>'), $this->template->getSnippet(new TestSnippet(), ['param' => '<b>test snippet</b>']));
        $this->assertSame(StringHelper::encode('<b>test snippet</b>'), $this->template->replace('[[test?param=`<b>test snippet</b>`]]'));
        $this->assertSame('<b>test snippet</b>', $this->template->replace('[[!test?param=`<b>test snippet</b>`]]'));
    }

    public function testNullSnippet()
    {
        $this->template->setSnippets(['nullSnippet' => ['class' => NullSnippet::className()]]);
        $this->assertEmpty($this->template->getSnippet('nullSnippet'));
    }

    public function testUnknownSnippet()
    {
        $this->setExpectedException(TemplateException::className());
        $this->template->getSnippet('Unknown');
    }

    public function testExtensions()
    {
        $this->template->setExtensions([
            'extension' => function (array $keys, array $params = []) {
                if (current($keys) === 'get' && isset($params['param'])) {
                    return $params['param'];
                }
                return 'fail';
            },
        ]);

        $this->assertSame(StringHelper::encode('<b>test extension</b>'), $this->template->replace('[[#extension.get?param=`<b>test extension</b>`]]'));
        $this->assertSame('<b>test extension</b>', $this->template->replace('[[!#extension.get?param=`<b>test extension</b>`]]'));
    }

    public function testNotSerializePlaceholder()
    {
        $this->template->setExtensions([
            'extension' => function (array $keys, array $params = []) {

                if (current($keys) === 'get' && isset($params['param'])) {
                    return $params['param']['bar'];
                }
                return 'fail';
            },
            'foo' => function () {
                return ['bar' => '<b>bar text</b>'];
            },
        ]);
        $this->assertSame('<b>text</b>', $this->template->replace('[[!#extension.get?param=`!+foo`]]', ['foo' => ['bar' => '<b>text</b>']]));
        $this->assertSame(StringHelper::encode('<b>bar text</b>'), $this->template->replace('[[!#extension.get?param=`#foo.get`]]'));
    }

    public function testCacheSnippet()
    {
        if (!interface_exists('\rock\cache\CacheInterface')) {
            $this->markTestSkipped('Rock cache not installed.');
            return;
        }
        static::clearRuntime();

        $cache = static::getCache();
        $className = TestSnippet::className();
        $this->template->cache = $cache;
        $this->template->setSnippets(['test' => ['class' => TestSnippet::className()]]);

        // Rock engine
        $this->assertSame('<b>test snippet</b>', $this->template->replace('[[!test?param=`<b>test snippet</b>`?cacheKey=`' . $className . '`]]'));
        $this->assertTrue($cache->exists($className));
        $this->assertSame('<b>test snippet</b>', $cache->get($className));
        $this->assertSame('<b>test snippet</b>', $this->template->replace('[[!test?param=`<b>test snippet</b>`?cacheKey=`' . $className . '`]]'));

        // PHP engine
        $this->assertSame('<b>test snippet</b>', $this->template->getSnippet('test', ['cacheKey' => $className]));
        static::clearRuntime();
    }

    public function testCacheLayout()
    {
        if (!interface_exists('\rock\cache\CacheInterface')) {
            $this->markTestSkipped('Rock cache not installed.');
        }
        static::clearCache();

        $cache = static::getCache();
        $this->template->cache = $cache;
        $this->template->addMultiPlaceholders(['foo' => ['bar' => '<b>text_bar</b>'], 'baz' => ['bar' => '<b>text_baz</b>']]);
        $placeholders = [
            'text' => 'world',
            'cacheKey' => 'key_layout'
        ];
        $this->assertSame(file_get_contents($this->path . '/_layout.html'), $this->template->render($this->path . '/layout', $placeholders));
        $this->assertTrue($cache->exists('key_layout'));
        $this->assertSame(file_get_contents($this->path . '/_layout.html'), $this->template->render($this->path . '/layout', $placeholders));
        static::clearCache();
    }

    public function testRenderAsPHP()
    {
        $this->template->addMultiPlaceholders(['foo' => ['bar' => '<b>text_bar</b>'], 'baz' => ['bar' => '<b>text_baz</b>']]);
        $this->assertSame(file_get_contents($this->path . '/_layout.html'), $this->template->render($this->path . '/layout.php', ['text' => 'world']));
        $this->assertSame('<b>subchunk</b>test', $this->template->getChunk($this->path . '/subchunk.php', ['title' => 'test']));
    }
}

class Foo
{
    public $foo = 'foo';
    private $bar = 'bar';

    public function getFoo()
    {
        return $this->getBar();
    }

    private function getBar()
    {
        return $this->bar;
    }
}