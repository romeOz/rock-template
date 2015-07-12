<?php

namespace rockunit\snippets;

use rock\helpers\Pagination;
use rockunit\template\TemplateCommon;

class ListViewTest extends TemplateCommon
{
    protected function calculatePath()
    {
        $this->path = __DIR__ . '/data';
    }

    public function testGetAsArray()
    {
        $params = [
            'array' => $this->getAll(),
        ];
        // null tpl
        $this->assertSame($this->template->getSnippet('listView', $params), json_encode($params['array']));

        // tpl + wrapper tpl
        $params['tpl'] = "@INLINE<h1>[[+name]]</h1>\n<p>[[+email]]</p>\n[[!+about]]\n[[+currentItem]]";
        $params['wrapperTpl'] = "@INLINE[[!+output]]\n[[+countItems]]";
        $this->assertSame($this->removeSpace($this->template->getSnippet('listView', $params)), $this->removeSpace(file_get_contents($this->path . '/snippet_as_array.html')));

        // pagination
        $params['pagination']['array'] = Pagination::get(count($params['array']), 1, 1, SORT_DESC);
        $params['pagination']['pageParam'] = 'num';
        $params['pagination']['toPlaceholder'] = '$parent.pagination';
        $this->assertSame($this->removeSpace(file_get_contents($this->path . '/snippet_as_array.html')), $this->removeSpace($this->template->getSnippet('listView', $params)));
        $this->assertNotEmpty($this->template->getPlaceholder('pagination'));
        $this->assertNotEmpty($this->template->getPlaceholder('$parent.pagination'));
        $this->assertNotEmpty($this->template->getPlaceholder('$root.pagination'));
        $this->assertEmpty($this->template->getPlaceholder('$parent.$parent.pagination'));
    }

    public function testGetAsSingleArray()
    {
        $params['array'] = ['foo', 'bar'];
        $params['tpl'] = "@INLINE<li>[[!+output]][[+currentItem]]</li>";
        $params['wrapperTpl'] = "@INLINE<ul>[[!+output]]</ul>";
        $this->assertSame('<ul><li>foo1</li><li>bar2</li></ul>', $this->removeSpace($this->template->getSnippet('listView', $params)));
    }

    public function testGetAsMethod()
    {
        $class = 'listView';
        // null tpl
        $this->assertSame(
            json_encode($this->getAll()),
            trim($this->template->replace('[[' . $class . '?call=`' . __CLASS__ . '.getAll`]]'))
        );

        // array is empty
        $this->assertSame(
            '',
            trim($this->template->replace('[[listView?array=`[]`]]'))
        );

        // array is empty  + custom error message
        $this->assertSame(
            'empty',
            trim($this->template->replace('[[listView?array=`[]`?errorText=`empty`]]'))
        );

        // tpl + wrapper tpl
        $this->assertSame(
            $this->removeSpace(file_get_contents($this->path . '/snippet_as_array.html')),
            $this->removeSpace($this->template->replace(
                '[[listView
                    ?call=`' . __CLASS__ . '.getAll`
                    ?tpl=`' . $this->path . '/item`
                    ?wrapperTpl=`' . $this->path . '/wrapper`
                ]]'
            ))
        );

        // pagination
        $this->assertSame(
            $this->removeSpace(file_get_contents($this->path . '/snippet_as_array.html')),
            $this->removeSpace($this->template->replace(
                '[[listView
                    ?call=`' . __CLASS__ . '.getAll`
                    ?tpl=`' . $this->path . '/item`
                    ?wrapperTpl=`' . $this->path . '/wrapper`
                    ?pagination=`{"call" : "' . addslashes(__CLASS__) . '.getPagination", "toPlaceholder" : "$parent.pagination"}`
                ]]'
            ))
        );

        $this->assertNotEmpty($this->template->getPlaceholder('pagination'));
        $this->assertNotEmpty($this->template->getPlaceholder('$parent.pagination'));
        $this->assertNotEmpty($this->template->getPlaceholder('$root.pagination'));
        $this->assertEmpty($this->template->getPlaceholder('$parent.$parent.pagination'));
    }


    public function testRender()
    {
        $this->template->snippets['prepareSnippet'] = [
            'class' => \rockunit\snippets\data\PrepareSnippet::className()
        ];
        $this->assertSame(
            $this->removeSpace($this->template->render('@rockunit.tpl/layout', [], new \rockunit\snippets\data\FooController)),
            $this->removeSpace(file_get_contents($this->path . '/_layout.html'))
        );
    }

    public function testCache()
    {
        if (!interface_exists('\rock\cache\CacheInterface') || !class_exists('\League\Flysystem\Filesystem')) {
            $this->markTestSkipped('Rock cache not installed.');
            return;
        }
        $cache = static::getCache();
        $this->template->cache = $cache;

        $this->assertSame(
            $this->removeSpace(file_get_contents($this->path . '/snippet_as_array.html')),
            $this->removeSpace($this->template->replace(
                '[[listView
                    ?call=`' . __CLASS__ . '.getAll`
                    ?tpl=`' . $this->path . '/item`
                    ?wrapperTpl=`' . $this->path . '/wrapper`
                    ?pagination=`{"call" : "' . addslashes(__CLASS__) . '.getPagination", "toPlaceholder" : "$parent.pagination"}`
                    ?cacheKey=`list`
                ]]'
            ))
        );
        $this->assertTrue($cache->exists('list'));

        // cache toPlaceholder
        $this->template->removeAllPlaceholders();
        $this->assertSame(
            $this->removeSpace(file_get_contents($this->path . '/snippet_as_array.html')),
            $this->removeSpace($this->template->replace(
                '[[listView
                    ?call=`' . __CLASS__ . '.getAll`
                    ?tpl=`' . $this->path . '/item`
                    ?wrapperTpl=`' . $this->path . '/wrapper`
                    ?pagination=`{"call" : "' . addslashes(__CLASS__) . '.getPagination", "toPlaceholder" : "$parent.pagination"}`
                    ?cacheKey=`list`
                ]]'
            ))
        );
        $this->assertTrue($cache->exists('list'));
        $this->assertNotEmpty($this->template->getPlaceholder('pagination'));
        $this->assertNotEmpty($this->template->getPlaceholder('$parent.pagination'));
        $this->assertNotEmpty($this->template->getPlaceholder('$root.pagination'));
        $this->assertEmpty($this->template->getPlaceholder('$parent.$parent.pagination'));
    }

    public function testCacheExpire()
    {
        if (!interface_exists('\rock\cache\CacheInterface') || !class_exists('\League\Flysystem\Filesystem')) {
            $this->markTestSkipped('Rock cache not installed.');
            return;
        }

        static::clearRuntime();
        $cache = static::getCache();
        $this->template->cache = $cache;
        $expected = $this->removeSpace(file_get_contents($this->path . '/snippet_as_array.html'));
        $actual = $this->removeSpace($this->template->replace(
            '[[listView
                ?call=`' . __CLASS__ . '.getAll`
                ?tpl=`' . $this->path . '/item`
                ?wrapperTpl=`' . $this->path . '/wrapper`
                ?pagination=`{"call" : "' . addslashes(__CLASS__) . '.getPagination", "toPlaceholder" : "$parent.pagination"}`
                ?cacheKey=`list`
                ?cacheExpire=`1`
            ]]'
        ));
        $this->assertSame($expected, $actual);
        $this->assertTrue($cache->exists('list'));
        sleep(4);
        $this->assertFalse($cache->exists('list'));
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
        return Pagination::get(count(static::getAll()), 1, 1, SORT_DESC);
    }
}
 