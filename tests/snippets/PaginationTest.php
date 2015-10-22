<?php

namespace rockunit\snippets;


use rock\snippets\PaginationSnippet;
use rock\template\TemplateException;
use rock\template\Template;
use rockunit\template\TemplateCommon;

class PaginationTest extends TemplateCommon
{
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
                'class' => PaginationSnippet::className(),
            ]
        ];

        $this->assertSame(null, $template->getSnippet('pagination'));

        $params = [
            'call' => function () {
                return \rock\helpers\Pagination::get(0, null, 10, SORT_DESC);
            }
        ];
        $this->assertEmpty($this->template->getSnippet('pagination', $params));

        // with args + anchor
        $params = [
            'array' => \rock\helpers\Pagination::get(7, null, 5, SORT_DESC),
            'url' => ['view' => 'all', 'sort' => 'desc', '#' => 'name'],
        ];

        $expected = static::removeSpace(file_get_contents(__DIR__ . '/data/_pagination_args.html'));
        $actual = static::removeSpace($this->template->getSnippet('pagination', $params));
        $this->assertSame($expected, $actual);

        // rock engine
        $actual = static::removeSpace($template->replace(
            '[[pagination
                ?array = `' . json_encode(\rock\helpers\Pagination::get(7, null, 5, SORT_DESC)) . '`
                ?url = `{"view" : "all", "sort": "desc", "#" : "name"}`
            ]]'
        ));
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
 