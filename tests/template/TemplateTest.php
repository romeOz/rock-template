<?php

namespace rockunit\template;


use rock\template\Exception;
use rock\template\helpers\String;
use rock\template\Template;
use rockunit\template\snippets\TestSnippet;

class TemplateTest extends TemplateCommon
{
    protected function calculatePath()
    {
        $this->path = __DIR__ . '/data';
    }
    public function testRender()
    {
        $this->template->addMultiPlaceholders(['foo'=> ['bar' => '<b>text_bar</b>']], true);
        $this->template->addMultiResources(['baz'=> ['bar' => '<b>text_baz</b>']], true);
        $this->assertSame($this->template->render($this->path . '/layout', ['text' => 'world']), file_get_contents($this->path . '/_layout.html'));
    }

    public function testConditionFilter()
    {
        $this->assertSame($this->template->getChunk('@rockunit.tpl/condition_filter', ['title' => '<b>test</b>', 'number' => 3]), file_get_contents($this->path . '/_condition_filter.html'));
    }


    public function testFilters()
    {
        // php-function filter
        $this->assertSame($this->template->replace('[[+title:substr&start=`6`&length=`2`:strlen]]', ['title'=> 'hello world']), '2');
        $this->template->removeAllPlaceholders();

        // unserialize
        $this->assertSame($this->template->replace('[[!+title:unserialize&key=`foo.bar`]]', ['title'=> json_encode(['baz' => 'baz_text', 'foo' => ['bar' => 'bar_text']])]), 'bar_text');
        $this->template->removeAllPlaceholders();

        // truncate
        $this->assertSame($this->template->replace('[[+title:truncate&length=`5`]]', ['title'=> 'Hello world']), 'Hello...');
        $this->template->removeAllPlaceholders();

        // truncate words
        $this->assertSame($this->template->replace('[[+title:truncateWords&length=`6`]]', ['title'=> 'Hello world']), 'Hello...');
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

        // encode
        $this->assertSame($this->template->replace('[[!+title:encode]]', ['title'=> '<b>Hello World</b>']), String::encode('<b>Hello World</b>'));
        $this->template->removeAllPlaceholders();

        // decode
        $this->assertSame($this->template->replace('[[+title:decode]]', ['title'=> '<b>Hello World</b>']), '<b>Hello World</b>');
        $this->template->removeAllPlaceholders();

        // modify url
        $_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'] = 'site.com';
        $replace = '[[+url:modifyUrl
                        &args=`{"page" : 1}`
                        &beginPath=`/parts`
                        &endPath=`/news/`
                        &anchor=`name`
                        &const=`32`
                     ]]';
        $this->assertSame($this->template->replace($replace, ['url'=> '/categories/?view=all']), 'http://site.com/parts/categories/news/?page=1#name');
        $this->template->removeAllPlaceholders();

        // modify date
        $replace = '[[+date:modifyDate&format=`dmyhm`]]';
        $this->assertSame($this->template->replace($replace, ['date'=> '2014-02-12 15:01']), '12 February 15:01');
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

    public function testSnippet()
    {
        $className = get_class(new TestSnippet);
        $this->assertSame($this->template->getSnippet($className, ['param' => '<b>test snippet</b>']), String::encode('<b>test snippet</b>'));
        $this->assertSame($this->template->getSnippet(new TestSnippet(), ['param' => '<b>test snippet</b>']), String::encode('<b>test snippet</b>'));
        $this->assertSame($this->template->replace('[['.$className.'?param=`<b>test snippet</b>`]]'), String::encode('<b>test snippet</b>'));
        $this->assertSame($this->template->replace('[[!'.$className.'?param=`<b>test snippet</b>`]]'), '<b>test snippet</b>');
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

    public function testRockCache()
    {
        if (!interface_exists('\rock\cache\CacheInterface') || !class_exists('\League\Flysystem\Filesystem')) {
            $this->markTestSkipped('Rock cache not installed.');
        }
    }

    /**
     * @depends testRockCache
     */
    public function testCache()
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
        $cache = new \rock\cache\CacheFile([
          'enabled' => true,
          'adapter' => $adapter,
        ]);
        $cache->flush();
        static::clearRuntime();

        $className = get_class(new TestSnippet);
        $this->template->cache = $cache;
        $this->assertSame($this->template->replace('[[!'.$className.'?param=`<b>test snippet</b>`?cacheKey=`'.$className.'`]]'), '<b>test snippet</b>');
        $this->assertTrue($cache->has($className));
        $this->assertSame($cache->get($className), '<b>test snippet</b>');

        $cache->flush();
        static::clearRuntime();
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
}