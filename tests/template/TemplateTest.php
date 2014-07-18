<?php

namespace rockunit\template;


use rock\template\date\Date;
use rock\template\Exception;
use rock\template\helpers\String;
use rock\template\Template;
use rock\template\url\Url;
use rockunit\template\filters\TestFilters;
use rockunit\template\snippets\NullSnippet;
use rockunit\template\snippets\TestSnippet;

class TemplateTest extends TemplateCommon
{
    public $aliases;

    protected function setUp()
    {
        parent::setUp();
        static::clearRuntime();
        $this->aliases = Template::$aliases;
    }

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        $_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'] = 'site.com';
    }


    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        static::clearRuntime();
    }

    protected function tearDown()
    {
        parent::tearDown();
        Template::$aliases = $this->aliases;
    }

    protected function calculatePath()
    {
        $this->path = __DIR__ . '/data';
    }

    public function testAlias()
    {
        Template::$aliases = [];
        $this->assertFalse(Template::getAlias('@rock', [], false));

        Template::setAlias('@rock', '/rock/framework');
        $this->assertEquals('/rock/framework', Template::getAlias('@rock'));
        $this->assertEquals('/rock/framework/test/file', Template::getAlias('@rock/test/file'));
        Template::setAlias('@rock/runtime', '/rock/runtime');
        $this->assertEquals('/rock/framework', Template::getAlias('@rock'));
        $this->assertEquals('/rock/framework/test/file', Template::getAlias('@rock/test/file'));
        $this->assertEquals('/rock/runtime', Template::getAlias('@rock/runtime'));
        $this->assertEquals('/rock/runtime/file', Template::getAlias('@rock/runtime/file'));

        Template::setAlias('@rock.test', '@rock/test');
        $this->assertEquals('/rock/framework/test', Template::getAlias('@rock.test'));

        Template::setAlias('@rock', null);
        $this->assertFalse(Template::getAlias('@rock', [], false));
        $this->assertEquals('/rock/runtime/file', Template::getAlias('@rock/runtime/file'));

        Template::setAlias('@some/alias', '/www');
        $this->assertEquals('/www', Template::getAlias('@some/alias'));

        // namespace
        Template::setAlias('@rock.ns', '\rock\core');
        $this->assertEquals('\rock\core', Template::getAlias('@rock.ns'));

        Template::setAliases(['@web' => '/assets', '@app' => '/apps/common']);
        $this->assertEquals('/assets', Template::getAlias('@web'));
        $this->assertEquals('/apps/common', Template::getAlias('@app'));
    }

    public function testPlaceholder()
    {
        // magic methods
        $this->template->text = 'foo';
        $this->assertTrue(isset($this->template->text));
        $this->assertSame($this->template->text, 'foo');
        unset($this->template->text);
        $this->assertFalse(isset($this->template->text));
        $this->template->addPlaceholder('bar', 'test', true);
        $this->assertTrue(isset($this->template->bar));
        $this->assertSame($this->template->bar, 'test');

        // get all local
        $this->template->text = 'foo';
        $this->assertSame($this->template->getAllPlaceholders(), ['text' => 'foo']);

        // get all global
        $this->template->addPlaceholder('bar', 'test', true);
        $this->assertTrue($this->template->hasPlaceholder('bar', true));
        $this->assertSame($this->template->getAllPlaceholders(false, true), ['bar' => 'test']);

        // remove global  placeholder
        $this->template->removePlaceholder('test', true);

        // remove multi global placeholder
        $this->template->removeMultiPlaceholders(['bar'], true);
        $this->assertFalse($this->template->hasPlaceholder('bar', true));

        // null name
        $this->template->removePlaceholder(null);
    }

    public function testResource()
    {
        $this->template->addResource('foo', 'foo');
        $this->template->addResource('bar', 'bar');
        $this->template->addResource('baz', 'baz');
        $this->assertTrue(isset($this->template->foo));
        $this->assertSame($this->template->getAllResources(true, ['foo', 'bar'], ['foo']), ['bar' => 'bar']);

        // remove resource
        $this->template->removeResource('bar');
        $this->assertFalse($this->template->hasResource('bar'));
    }

    public function testRender()
    {
        $this->template->addMultiPlaceholders(['foo'=> ['bar' => '<b>text_bar</b>']], true);
        $this->template->addMultiResources(['baz'=> ['bar' => '<b>text_baz</b>']]);
        $this->assertSame($this->template->render($this->path . '/layout', ['text' => 'world']), file_get_contents($this->path . '/_layout.html'));
    }

    public function testRenderUnknownFileException()
    {
        $this->setExpectedException(Exception::className());
        $this->template->render($this->path . '/unknown');
    }

    public function testRenderMetaTags()
    {
        $config = [
            'head' => '<!DOCTYPE html>
            <!--[if !IE]>--><html class="no-js"><!--<![endif]-->',
            'title' => function(){
                    return 'Demo';
                },
            'metaTags' => function(){
                    return [
                        '<meta charset="UTF-8">',
                        'language' => '<meta http-equiv="Content-Language" content="en">',
                        'robots' => '<meta name="robots" content="all">',
                        'description' => '<meta name="description" content="about">',
                    ];
                },
            'linkTags' => [
                '<link type="image/x-icon" href="/favicon.ico?10" rel="Shortcut Icon">',
                '<link type="application/rss+xml" href="/feed.rss" title="rss"  rel="alternate">'
            ],
            'cssFiles' => [
                Template::POS_HEAD => [
                    '<link href="http://site.com/assets/css/main.css" rel="stylesheet" media="screen, projection">'
                ],
                Template::POS_END => [
                    '<!--[if !(IE) | (gt IE 8) ]>--><link href="http://site.com/assets/css/footer.css" rel="stylesheet" media="screen, projection"><!--<![endif]-->'
                ]
            ],
            'jsFiles' => [
                Template::POS_HEAD => [
                    '<!--[if lt IE 9]><script src="http://site.com/assets/head.js"></script><![endif]-->'
                ],
                Template::POS_END => [
                    '<script src="http://site.com/assets/end.js"></script>'
                ]
            ],
        ];

        // Rock engine
        $this->assertSame(
            static::removeSpace((new Template($config))->render($this->path . '/meta', ['about' => 'demo'])),
            static::removeSpace(file_get_contents($this->path . '/_meta.html'))
        );

        // PHP engine
        $config['engine'] = Template::PHP;
        $config['fileExtension'] = 'php';
        $this->assertSame(
            static::removeSpace((new Template($config))->render($this->path . '/meta', ['about' => 'demo'])),
            static::removeSpace(file_get_contents($this->path . '/_meta.html'))
        );

        // register
        $template = new Template;
        $template->engine = Template::PHP;
        $template->head = '<!DOCTYPE html>
            <!--[if !IE]>--><html class="no-js"><!--<![endif]-->';
        $template->title = 'Demo';
        $template->registerMetaTag(['charset' => 'UTF-8']);
        $template->registerMetaTag(['http-equiv' => 'Content-Language', 'content' => 'en'], 'language');
        $template->registerMetaTag(['name' => 'robots', 'content' => 'all'], 'robots');
        $template->registerMetaTag(['name' => 'description', 'content' => 'about'], 'description');
        $template->registerLinkTag(['rel' => 'Shortcut Icon', 'type' => 'image/x-icon', 'href' => '/favicon.ico?10']);
        $template->registerLinkTag(['rel' => 'alternate', 'type' => 'application/rss+xml', 'title' => 'rss', 'href' => '/feed.rss']);
        $template->registerCssFile('/assets/css/main.css', ['media'=>'screen, projection']);
        $template->registerCssFile(
            '/assets/css/footer.css',
            [
                'position' => Template::POS_END,
                'media'=>'screen, projection',
                'wrapperTpl' => '@INLINE<!--[if !(IE) | (gt IE 8) ]>-->[[!+output]]<!--<![endif]-->'
            ]
        );

        $template->registerJsFile(
            '/assets/head.js',
            [
                'position' => Template::POS_HEAD,
                'wrapperTpl' => '@INLINE<!--[if lt IE 9]>[[!+output]]<![endif]-->'
            ]
        );
        $template->registerJsFile('/assets/end.js');
        $this->assertSame(
            static::removeSpace($template->render($this->path . '/meta', ['about' => 'demo'])),
            static::removeSpace(file_get_contents($this->path . '/_meta.html'))
        );
    }

    public function testHasChunk()
    {
        $this->assertTrue($this->template->hasChunk($this->path . '/layout'));
    }

    public function testConditionFilter()
    {
        $this->assertSame($this->template->getChunk('@rockunit.tpl/condition_filter', ['title' => '<b>test</b>', 'number' => 3]), file_get_contents($this->path . '/_condition_filter.html'));

        // unknown param
        $this->setExpectedException(Exception::className());
        $this->template->replace('[[+content:if&foo=`null`&then=`[[!+title]]`]]');
    }

    public function testIfException()
    {
        $this->setExpectedException(Exception::className());
        $this->template->replace('[[+title:if]]', ['title'=> 'Hello World']);
    }

    public function testFilters()
    {
        // php-function filter
        $this->assertSame($this->template->replace('[[+title:substr&start=`6`&length=`2`:strlen]]', ['title'=> 'hello world']), '2');
        $this->template->removeAllPlaceholders();

        // replaceTpl
        $this->assertSame($this->template->replace('[[+content:replaceTpl&title=`[[+title]]`:upper]]', ['content' => '[[+title]]', 'title' => 'hello']), 'HELLO');
        $this->template->removeAllPlaceholders();

        // unserialize
        $this->assertSame($this->template->replace('[[!+title:unserialize&key=`foo.bar`]]', ['title'=> json_encode(['baz' => 'baz_text', 'foo' => ['bar' => 'bar_text']])]), 'bar_text');
        $this->template->removeAllPlaceholders();

        // unserialize + input null
        $this->assertSame($this->template->replace('[[!+title:unserialize&key=`foo.bar`]]', ['title'=> null]), '');
        $this->template->removeAllPlaceholders();

        // unserialize + output array
        $this->assertSame($this->template->replace('[[!+title:unserialize&key=`foo`]]', ['title'=> serialize(['baz' => 'baz_text', 'foo' => ['bar' => 'bar_text']])]), '');
        $this->template->removeAllPlaceholders();

        // truncate
        $this->assertSame($this->template->replace('[[+title:truncate&length=`5`]]', ['title'=> 'Hello world']), 'Hello...');
        $this->template->removeAllPlaceholders();

        // truncate + input null
        $this->assertSame($this->template->replace('[[+title:truncate]]', ['title'=> null]), '');
        $this->template->removeAllPlaceholders();

        // truncate words
        $this->assertSame($this->template->replace('[[+title:truncateWords&length=`6`]]', ['title'=> 'Hello world']), 'Hello...');
        $this->template->removeAllPlaceholders();

        // truncate words + input null
        $this->assertSame($this->template->replace('[[+title:truncateWords]]', ['title'=> null]), '');
        $this->template->removeAllPlaceholders();

        // lower
        $this->assertSame($this->template->replace('[[+title:lower]]', ['title'=> 'Hello World']), 'hello world');
        $this->template->removeAllPlaceholders();

        // upper
        $this->assertSame($this->template->replace('[[+title:upper]]', ['title'=> 'Hello World']), 'HELLO WORLD');
        $this->template->removeAllPlaceholders();

        // upper first character
        $this->assertSame($this->template->replace('[[+title:upperFirst]]', ['title'=> 'hello world']), 'Hello world');
        $this->template->removeAllPlaceholders();

        // trim pattern
        $this->assertSame($this->template->replace('[[+title:trimPattern&pattern=`/l{2}/`]]', ['title'=> 'Hello World']), 'Heo World');
        $this->template->removeAllPlaceholders();

        // contains success
        $this->assertSame($this->template->replace('[[+title:contains&is=`Wo`&then=`[[+title]]`]]', ['title'=> 'Hello World']), 'Hello World');
        $this->template->removeAllPlaceholders();

        // contains fail
        $this->assertSame($this->template->replace('[[+title:contains&is=`wo`&then=`[[+title]]`]]', ['title'=> 'Hello World']), '');
        $this->template->removeAllPlaceholders();

        // isParity success
        $this->assertSame($this->template->replace('[[+num:isParity&then=`success`]]', ['num'=> 2]), 'success');
        $this->template->removeAllPlaceholders();

        // isParity fail
        $this->assertSame($this->template->replace('[[+num:isParity&then=`success`&else=`fail`]]', ['num'=> '3']), 'fail');
        $this->template->removeAllPlaceholders();

        // to positive fail
        $this->assertSame($this->template->replace('[[+num:positive]]', ['num'=> '7']), '7');
        $this->template->removeAllPlaceholders();

        // to positive fail
        $this->assertSame($this->template->replace('[[+num:positive]]', ['num'=> '-7']), '0');
        $this->template->removeAllPlaceholders();

        // encode
        $this->assertSame($this->template->replace('[[!+title:encode]]', ['title'=> '<b>Hello World</b>']), String::encode('<b>Hello World</b>'));
        $this->template->removeAllPlaceholders();

        // decode
        $this->assertSame($this->template->replace('[[+title:decode]]', ['title'=> '<b>Hello World</b>']), '<b>Hello World</b>');
        $this->template->removeAllPlaceholders();

        // modify url
        $replace = '[[+url:modifyUrl
                        &args=`{"page" : 1}`
                        &beginPath=`/parts`
                        &endPath=`/news/`
                        &anchor=`name`
                        &const=`32`
                     ]]';
        $this->assertSame($this->template->replace($replace, ['url'=> '/categories/?view=all']), 'http://site.com/parts/categories/news/?page=1#name');
        $this->template->removeAllPlaceholders();

        // modify url + remove args + add args
        $replace = '[[+url:modifyUrl
                        &removeArgs=`["view"]`
                        &addArgs=`{"page" : 1}`
                        &const=`32`
                     ]]';
        $this->assertSame($this->template->replace($replace, ['url'=> '/categories/?view=all']), 'http://site.com/categories/?page=1');
        $this->template->removeAllPlaceholders();

        // modify url + remove all args
        $replace = '[[+url:modifyUrl
                        &removeAllArgs=`true`
                        &removeAnchor=`true`
                        &const=`32`
                     ]]';
        $this->assertSame($this->template->replace($replace, ['url'=> '/categories/?view=all#name']), 'http://site.com/categories/');
        $this->template->removeAllPlaceholders();

        // modify url + input null
        $replace = '[[+url:modifyUrl]]';
        $this->assertSame($this->template->replace($replace, ['url'=> '']), '#');
        $this->template->removeAllPlaceholders();

        // modify date
        $replace = '[[+date:modifyDate&format=`dmyhm`]]';
        $this->assertSame($this->template->replace($replace, ['date'=> '2012-02-12 15:01']), '12 February 2012 15:01');
        $this->template->removeAllPlaceholders();

        // modify date
        $replace = '[[+date:modifyDate&format=`dmy`]]';
        $this->assertSame($this->template->replace($replace, ['date'=> '2012-02-12 15:01']), '12 February 2012');
        $this->template->removeAllPlaceholders();

        // modify date + default format
        $replace = '[[+date:modifyDate]]';
        $this->assertSame($this->template->replace($replace, ['date'=> '2012-02-12 15:01']), '2012-02-12 15:01:00');
        $this->template->removeAllPlaceholders();

        // modify date + input null
        $replace = '[[+date:modifyDate]]';
        $this->assertSame($this->template->replace($replace, ['date'=> 'null']), '');
        $this->template->removeAllPlaceholders();

        // arrayToJson
        $replace = '[[+array:arrayToJson]]';
        $this->assertSame($this->template->replace($replace, ['array'=> ['foo' => 'test']]), json_encode(['foo' => 'test']));
        $this->template->removeAllPlaceholders();

        // arrayToJson + input null
        $replace = '[[+array:toJson]]';
        $this->assertSame($this->template->replace($replace, ['array'=> '']), '');
        $this->template->removeAllPlaceholders();

        // jsonToArray + serialize
        $replace = '[[!+array:toArray:serialize]]';
        $this->assertSame($this->template->replace($replace, ['array'=> json_encode(['foo' => 'test'])]), serialize(['foo' => 'test']));
        $this->template->removeAllPlaceholders();

        // multiplication
        $this->assertSame($this->template->replace('[[+num * `4`]]', ['num'=> 3]), '12');
        $this->template->removeAllPlaceholders();

        // repeat multiplication
        $this->assertSame($this->template->replace('[[+num * `4` + `2`:formula&operator=`*`&operand=`3`]]', ['num'=> 3]), '42');
        $this->template->removeAllPlaceholders();

        // exponential expression
        $this->assertSame($this->template->replace('[[+num ** `2`]]', ['num'=> '3']), '9');
        $this->template->removeAllPlaceholders();

        // division
        $this->assertSame($this->template->replace('[[+num / `2`]]', ['num'=> 10]), '5');
        $this->template->removeAllPlaceholders();

        // modulus
        $this->assertSame($this->template->replace('[[+num mod `2`]]', ['num'=> 3]), '1');
        $this->template->removeAllPlaceholders();

        // negation
        $this->assertSame($this->template->replace('[[+num - `3`]]', ['num'=> 10]), '7');
        $this->template->removeAllPlaceholders();

        // addition
        $this->assertSame($this->template->replace('[[+num + `5`]]', ['num'=> 7]), '12');
        $this->template->removeAllPlaceholders();

        // bit or
        $this->assertSame($this->template->replace('[[+num | `8`]]', ['num'=> 2]), '10');
        $this->template->removeAllPlaceholders();

        // bit and
        $this->assertSame($this->template->replace('[[+num & `10`]]', ['num'=> 2]), '2');
        $this->template->removeAllPlaceholders();

        // bit xor
        $this->assertSame($this->template->replace('[[+num ^ `10`]]', ['num'=> 2]), '8');
        $this->template->removeAllPlaceholders();

        // bit shift the bits to the left
        $this->assertSame($this->template->replace('[[+num << `7`]]', ['num'=> 2]), '256');
        $this->template->removeAllPlaceholders();

        // bit shift the bits to the right
        $this->assertSame($this->template->replace('[[+num >> `1`]]', ['num'=> 2]), '1');
        $this->template->removeAllPlaceholders();

        $this->template->replace('[[+num:formula&operator=`<<`]]', ['num'=> 2], '2');

        $this->setExpectedException(Exception::className());
        $this->template->replace('[[+num:formula&operator=`<!<!<`&operand=`4`]]', ['num'=> 2]);
    }

    public function testFilterHandlers()
    {
        $config = [
            'filters' => [
                'foo' => [
                    'class' => TestFilters::className(),
                    'handlers' => [
                        function(){
                            return new Url();
                        },
                        new Date()
                    ]
                ]
            ]
        ];
        $this->assertSame((new Template($config))->replace('[[+value:foo]]', ['value' => 'test']), 'test');
    }

    public function testOutputArrayException()
    {
        $replace = '[[!+array:jsonToArray]]';
        $this->setExpectedException(Exception::className());
        $this->template->replace($replace, ['array'=> json_encode(['foo' => 'test'])]);
    }

    public function testContainsException()
    {
        $this->setExpectedException(Exception::className());
        $this->template->replace('[[+title:contains]]', ['title'=> 'Hello World']);
    }

    public function testIsParityException()
    {
        $this->setExpectedException(Exception::className());
        $this->template->replace('[[+num:isParity]]', ['num'=> 2]);
    }

    public function testUnknownFilter()
    {
        $this->setExpectedException(Exception::className());
        $this->template->replace('[[+num:foo&operator=`<<`]]', ['num'=> 2], '2');
    }

    public function testAutoEscape()
    {
        $this->template->autoEscape = false;
        $this->assertSame($this->template->replace('[[+title?autoEscape=`2`]]', ['title'=> '<b>Hello World</b>']), 'Hello World');
        $this->template->removeAllPlaceholders();

        $this->template->autoEscape = Template::ESCAPE | Template::TO_TYPE;
        $this->assertSame($this->template->replace('[[+title?autoEscape=`false`]]', ['title'=> '<b>Hello World</b>']), '<b>Hello World</b>');
    }

    public function testGetNamePrefix()
    {
        // null
        $this->assertEmpty($this->template->getNamePrefix(null));
        $this->assertSame(
            $this->template->getNamePrefix('@INLINE<b>foo</b>'),
            array (
                'prefix' => 'INLINE',
                'value' => '<b>foo</b>',
            )
        );
    }

    public function testRemovePrefix()
    {
        // null
        $this->assertEmpty($this->template->removePrefix(null));
        $this->assertSame($this->template->removePrefix('@INLINE<b>foo</b>'), '<b>foo</b>');
    }

    public function testSnippet()
    {
        $className = TestSnippet::className();
        $this->assertSame($this->template->getSnippet($className, ['param' => '<b>test snippet</b>']), String::encode('<b>test snippet</b>'));
        $this->assertSame($this->template->getSnippet(new TestSnippet(), ['param' => '<b>test snippet</b>']), String::encode('<b>test snippet</b>'));
        $this->assertSame($this->template->replace('[['.$className.'?param=`<b>test snippet</b>`]]'), String::encode('<b>test snippet</b>'));
        $this->assertSame($this->template->replace('[[!'.$className.'?param=`<b>test snippet</b>`]]'), '<b>test snippet</b>');
    }

    public function testNullSnippet()
    {
        $this->assertEmpty($this->template->getSnippet(NullSnippet::className()));
    }

    public function testUnknownSnippet()
    {
        $this->setExpectedException(Exception::className());
        $this->template->getSnippet('Unknown');
    }

    public function testExtensions()
    {
        $this->template->extensions = [
        'extension' => function (array $keys, array $params = []) {
                if (current($keys) === 'get' && isset($params['param'])) {
                    return $params['param'];
                }
                return 'fail';
            },
        ];

        $this->assertSame($this->template->replace('[[#extension.get?param=`<b>test extension</b>`]]'), String::encode('<b>test extension</b>'));
        $this->assertSame($this->template->replace('[[!#extension.get?param=`<b>test extension</b>`]]'), '<b>test extension</b>');
    }

    public function testRockCacheExists()
    {
        if (!interface_exists('\rock\cache\CacheInterface') || !class_exists('\League\Flysystem\Filesystem')) {
            $this->markTestSkipped('Rock cache not installed.');
        }
    }

    /**
     * @depends testRockCacheExists
     */
    public function testCacheSnippet()
    {
        $cache = $this->getCache();
        $className = get_class(new TestSnippet);
        $this->template->cache = $cache;
        $this->assertSame($this->template->replace('[[!'.$className.'?param=`<b>test snippet</b>`?cacheKey=`'.$className.'`]]'), '<b>test snippet</b>');
        $this->assertTrue($cache->has($className));
        $this->assertSame($cache->get($className), '<b>test snippet</b>');
        $this->assertSame($this->template->replace('[[!'.$className.'?param=`<b>test snippet</b>`?cacheKey=`'.$className.'`]]'), '<b>test snippet</b>');
    }

    /**
     * @depends testRockCacheExists
     */
    public function testCacheLayout()
    {
        $cache = $this->getCache();
        $this->template->cache = $cache;
        $this->template->addMultiPlaceholders(['foo'=> ['bar' => '<b>text_bar</b>']], true);
        $this->template->addMultiResources(['baz'=> ['bar' => '<b>text_baz</b>']]);
        $placeholders = [
            'text' => 'world',
            'cacheKey' => 'key_layout'
        ];
        $this->assertSame($this->template->render($this->path . '/layout', $placeholders), file_get_contents($this->path . '/_layout.html'));
        $this->assertTrue($cache->has('key_layout'));
        $this->assertSame($this->template->render($this->path . '/layout', $placeholders), file_get_contents($this->path . '/_layout.html'));
    }

    public function testRenderAsPHP()
    {
        $this->template->engine = Template::PHP;
        $this->template->fileExtension = 'php';
        $this->template->addMultiPlaceholders(['foo'=> ['bar' => '<b>text_bar</b>']], true);
        $this->template->addMultiResources(['baz'=> ['bar' => '<b>text_baz</b>']], true);
        $this->assertSame($this->template->render($this->path . '/layout', ['text' => 'world']), file_get_contents($this->path . '/_layout.html'));
        $this->assertSame($this->template->getChunk($this->path . '/subchunk', ['title'=> 'test']), '<b>subchunk</b>test');
    }

    protected function getCache()
    {
        $adapter = new \rock\cache\filemanager\FileManager(
            [
                'adapter' =>
                    function () {
                        return new \League\Flysystem\Adapter\Local(Template::getAlias('@runtime/cache'));
                    },
                'cache' => function () {
                        $local = new \League\Flysystem\Adapter\Local(Template::getAlias('@runtime'));
                        $cache = new \League\Flysystem\Cache\Adapter($local, 'cache.tmp');

                        return $cache;
                    }
            ]
        );
        return new \rock\cache\CacheFile([
                                               'enabled' => true,
                                               'adapter' => $adapter,
                                           ]);
    }
}