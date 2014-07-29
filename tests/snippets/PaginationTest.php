<?php

namespace rockunit\snippets;


use rock\template\Exception;
use rock\template\snippets\Pagination;
use rock\template\Template;
use rock\template\url\Url;
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
            'Pagination' => [
                'class' => Pagination::className(),
            ]
        ];

        $this->assertSame($template->getSnippet('Pagination'), null);

        $params = [
          'call' => function(){
                  return \rock\template\helpers\Pagination::get(0, null, 10, SORT_DESC);
              }
        ];
        $this->assertEmpty($this->template->getSnippet(Pagination::className(), $params));

        // with args + anchor
        $params = [
            'array' => \rock\template\helpers\Pagination::get(7, null, 5, SORT_DESC),
            'pageArgs' => 'view=all&sort=desc',
            'pageAnchor' => 'name'

        ];

        $this->assertSame(
            static::removeSpace($this->template->getSnippet(Pagination::className(), $params)),
            static::removeSpace(file_get_contents(__DIR__ . '/data/_pagination_args.html'))
        );

        // not args
        $params = [
            'array' => \rock\template\helpers\Pagination::get(7, null, 5, SORT_DESC),
        ];
        $this->assertSame(
            static::removeSpace($this->template->getSnippet(Pagination::className(), $params)),
            static::removeSpace(file_get_contents(__DIR__ . '/data/_pagination_not_args.html'))
        );
    }

    public function unknownCallException()
    {
        $params = [
            'call' => 'Foo.method'
        ];
        $this->setExpectedException(Exception::className());
        $this->template->getSnippet(Pagination::className(), $params);
    }
}
 