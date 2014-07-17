<?php

namespace rockunit\snippets;



use rock\template\helpers\Pagination;
use rock\template\snippets\ListView;
use rock\template\Template;
use rockunit\template\TemplateCommon;

class ListViewTest extends TemplateCommon
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

    public function testGetAsArray()
    {
        $params = [
            'array' => $this->getAll(),
        ];
        // null tpl
        $this->assertSame($this->template->getSnippet(ListView::className(), $params), json_encode($params['array']));

        // tpl + wrapper tpl
        $params['tpl'] = "@INLINE<h1>[[+name]]</h1>\n<p>[[+email]]</p>\n[[!+about]]\n[[+currentItem]]";
        $params['wrapperTpl'] = "@INLINE[[!+output]]\n[[+countItems]]";
        $this->assertSame($this->removeSpace($this->template->getSnippet(ListView::className(), $params)), $this->removeSpace(file_get_contents($this->path . '/snippet_as_array.html')));

        // navigation
        $params['nav']['array'] = Pagination::get(count($params['array']), 1, SORT_DESC, 1);
        $params['nav']['pageVar'] = 'num';
        $params['nav']['toPlaceholder'] = 'navigation';
        $this->assertSame($this->removeSpace($this->template->getSnippet(ListView::className(), $params)), $this->removeSpace(file_get_contents($this->path . '/snippet_as_array.html')));
        $this->assertNotEmpty($this->template->getPlaceholder('navigation', false, true));
    }


    public function testGetAsMethod()
    {
        // null tpl
        $this->assertSame(
            trim($this->template->replace('
                [[\rock\template\snippets\ListView?call=`'.__CLASS__.'.getAll`]]
            ')),
            json_encode($this->getAll())
        );

        // array is empty
        $this->assertSame(
            trim($this->template->replace('
                [[ListView?array=`[]`]]
            ')),
            'content is empty'
        );

        // array is empty  + custom error message
        $this->assertSame(
            trim($this->template->replace('
                [[ListView?array=`[]`?errorText=`empty`]]
            ')),
            'empty'
        );

        // tpl + wrapper tpl
        $this->assertSame(
            $this->removeSpace($this->template->replace('
                [[ListView
                    ?call=`'.__CLASS__.'.getAll`
                    ?tpl=`'. $this->path . '/item`
                    ?wrapperTpl=`'. $this->path . '/wrapper`
                ]]
            ')),
            $this->removeSpace(file_get_contents($this->path . '/snippet_as_array.html'))
        );

        // navigation
        $this->assertSame(
            $this->removeSpace($this->template->replace('
                [[ListView
                    ?call=`'.__CLASS__.'.getAll`
                    ?tpl=`'. $this->path . '/item`
                    ?wrapperTpl=`'. $this->path . '/wrapper`
                    ?nav=`{"call" : "'.addslashes(__CLASS__).'.getPagination", "toPlaceholder" : "navigation"}`
                ]]
            ')),
            $this->removeSpace(file_get_contents($this->path . '/snippet_as_array.html'))
        );

        $this->assertNotEmpty($this->template->getPlaceholder('navigation', false, true));
    }


    public function testRender()
    {
        $this->assertSame(
            $this->removeSpace($this->template->render('@rockunit.tpl/layout', [], new \rockunit\snippets\data\FooController)),
            $this->removeSpace(file_get_contents($this->path . '/_layout.html'))
        );
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
        $cache = $this->getCache();
        $this->template->cache = $cache;

        $this->assertSame(
            $this->removeSpace($this->template->replace('
                [[ListView
                    ?call=`'.__CLASS__.'.getAll`
                    ?tpl=`'. $this->path . '/item`
                    ?wrapperTpl=`'. $this->path . '/wrapper`
                    ?nav=`{"call" : "'.addslashes(__CLASS__).'.getPagination", "toPlaceholder" : "navigation"}`
                    ?cacheKey=`list`
                ]]
            ')),
            $this->removeSpace(file_get_contents($this->path . '/snippet_as_array.html'))
        );
        $this->assertTrue($cache->has('list'));

        // cache toPlaceholder
        $this->template->removeAllPlaceholders(true);
        $this->assertSame(
            $this->removeSpace($this->template->replace('
                [[ListView
                    ?call=`'.__CLASS__.'.getAll`
                    ?tpl=`'. $this->path . '/item`
                    ?wrapperTpl=`'. $this->path . '/wrapper`
                    ?nav=`{"call" : "'.addslashes(__CLASS__).'.getPagination", "toPlaceholder" : "navigation"}`
                    ?cacheKey=`list`
                ]]
            ')),
            $this->removeSpace(file_get_contents($this->path . '/snippet_as_array.html'))
        );
        $this->assertTrue($cache->has('list'));
        $this->assertNotEmpty($this->template->getPlaceholder('navigation', false, true));
    }

    /**
     * @depends testRockCache
     */
    public function testCacheExpire()
    {
        static::clearRuntime();
        $cache = $this->getCache();
        $this->template->cache = $cache;
        $this->assertSame(
            $this->removeSpace($this->template->replace('
                [[ListView
                    ?call=`'.__CLASS__.'.getAll`
                    ?tpl=`'. $this->path . '/item`
                    ?wrapperTpl=`'. $this->path . '/wrapper`
                    ?nav=`{"call" : "'.addslashes(__CLASS__).'.getPagination", "toPlaceholder" : "navigation"}`
                    ?cacheKey=`list`
                    ?cacheExpire=`1`
                ]]
            ')),
            $this->removeSpace(file_get_contents($this->path . '/snippet_as_array.html'))
        );
        $this->assertTrue($cache->has('list'));
        sleep(2);
        $this->assertFalse($cache->has('list'));
    }

    public static function getAll()
    {
        return [
            [
                'name' => 'Tom',
                'email' => 'tom@site.com',
                'about' => '<b>biography</b>'
            ],
            [
                'name' => 'Chuck',
                'email' => 'chuck@site.com'
            ]
        ];
    }

    public static function getPagination()
    {
        return Pagination::get(count(static::getAll()), 1, SORT_DESC, 1);
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
 