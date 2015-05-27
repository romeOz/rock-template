<?php

namespace rockunit\snippets;


use rock\snippets\Pagination;
use rock\template\TemplateException;
use rock\template\Template;
use rock\url\Url;
use rockunit\template\TemplateCommon;

class PaginationTest extends TemplateCommon
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
    }

    protected function calculatePath()
    {
        $this->path = __DIR__ . '/data';
    }

    public function testGet()
    {
        // null or []
        $template = new Template();
        $template->snippets = [
            'pagination' => [
                'class' => Pagination::className(),
            ]
        ];

        $this->assertSame(null, $template->getSnippet('pagination'));

        $params = [
          'call' => function(){
                  return \rock\helpers\Pagination::get(0, null, 10, SORT_DESC);
              }
        ];
        $this->assertEmpty($this->template->getSnippet('pagination', $params));

        // with args + anchor
        $params = [
            'array' => \rock\helpers\Pagination::get(7, null, 5, SORT_DESC),
            'url' => Url::set()->addArgs(['view' => 'all', 'sort' => 'desc'])->addAnchor('name'),
        ];

        $expected = static::removeSpace(file_get_contents(__DIR__ . '/data/_pagination_args.html'));
        $actual = static::removeSpace($this->template->getSnippet('pagination', $params));
        $this->assertSame($expected, $actual);

        // rock engine
        $actual = static::removeSpace($template->replace('
            [[pagination
                ?array = `'.json_encode(\rock\helpers\Pagination::get(7, null, 5, SORT_DESC)).'`
                ?url = `{"class" : "\\\rock\\\url\\\Url", "query": "view=all&sort=desc", "fragment" : "name"}`
            ]]
        '));
        $this->assertSame($expected, $actual);

        // not args
        $params = [
            'array' => \rock\helpers\Pagination::get(7, null, 5, SORT_DESC),
        ];
        $this->assertSame(
            static::removeSpace(file_get_contents(__DIR__ . '/data/_pagination_not_args.html')),
            static::removeSpace($this->template->getSnippet('pagination', $params))
        );
    }

    public function unknownCallException()
    {
        $params = [
            'call' => 'Foo.method'
        ];
        $this->setExpectedException(TemplateException::className());
        $this->template->getSnippet('pagination', $params);
    }
}
 