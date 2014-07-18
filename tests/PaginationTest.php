<?php

namespace rockunit;


use rock\template\helpers\Pagination;

class PaginationTest extends \PHPUnit_Framework_TestCase
{
    public function testGetAsSortASC()
    {
        // count "0"
        $this->assertSame(Pagination::get(0), []);

        $actual = array (
            'pageVar' => 'page',
            'pageCount' => 1,
            'pageCurrent' => 1,
            'pageStart' => 1,
            'pageEnd' => 1,
            'pageDisplay' =>
                array (
                    0 => 1,
                ),
            'pageFirst' => NULL,
            'pageLast' => NULL,
            'offset' => 0,
            'limit' => 10,
            'countMore' => 0,
        );
        $this->assertSame(Pagination::get(7,1),$actual);

        // first page
        $actual = array (
            'pageVar' => 'page',
            'pageCount' => 2,
            'pageCurrent' => 1,
            'pageStart' => 1,
            'pageEnd' => 2,
            'pageDisplay' =>
                array (
                    0 => 1,
                    1 => 2,
                ),
            'pageNext' => 2,
            'pageFirst' => NULL,
            'pageLast' => 2,
            'offset' => 0,
            'limit' => 5,
            'countMore' => 2,
        );
        $this->assertSame(Pagination::get(7,null,5), $actual);
        $this->assertSame(Pagination::get(7,0,5), $actual);
        $this->assertSame(Pagination::get(7,-1,5), $actual);
        $this->assertSame(Pagination::get(7,'foo',5), $actual);

        // next page
        $actual = array (
            'pageVar' => 'page',
            'pageCount' => 2,
            'pageCurrent' => 1,
            'pageStart' => 1,
            'pageEnd' => 2,
            'pageDisplay' =>
                array (
                    0 => 1,
                    1 => 2,
                ),
            'pageNext' => 2,
            'pageFirst' => NULL,
            'pageLast' => 2,
            'offset' => 0,
            'limit' => 5,
            'countMore' => 2,
        );
        $this->assertSame(Pagination::get(7, 1, 5), $actual);

        // page last
        $actual = array (
            'pageVar' => 'page',
            'pageCount' => 2,
            'pageCurrent' => 2,
            'pageStart' => 1,
            'pageEnd' => 2,
            'pageDisplay' =>
                array (
                    0 => 1,
                    1 => 2,
                ),
            'pagePrev' => 1,
            'pageFirst' => 1,
            'pageLast' => NULL,
            'offset' => 5,
            'limit' => 5,
            'countMore' => 0,
        );
        $this->assertSame(Pagination::get(7, 7, 5), $actual);

    }

    public function testGetAsSortDESC()
    {
        // first page
        $actual = array (
            'pageVar' => 'page',
            'pageCount' => 2,
            'pageCurrent' => 2,
            'pageStart' => 2,
            'pageEnd' => 1,
            'pageDisplay' =>
                array (
                    0 => 2,
                    1 => 1,
                ),
            'pageNext' => 1,
            'pageFirst' => NULL,
            'pageLast' => 1,
            'offset' => 0,
            'limit' => 5,
            'countMore' => 2,
        );
        $this->assertSame(Pagination::get(7, null, 5, SORT_DESC), $actual);
        $this->assertSame(Pagination::get(7, 0, 5, SORT_DESC), $actual);
        $this->assertSame(Pagination::get(7, -1, 5, SORT_DESC), $actual);
        $this->assertSame(Pagination::get(7, 'foo', 5, SORT_DESC), $actual);

        // next page
        $actual = array (
            'pageVar' => 'page',
            'pageCount' => 2,
            'pageCurrent' => 2,
            'pageStart' => 2,
            'pageEnd' => 1,
            'pageDisplay' =>
                array (
                    0 => 2,
                    1 => 1,
                ),
            'pageNext' => 1,
            'pageFirst' => NULL,
            'pageLast' => 1,
            'offset' => 0,
            'limit' => 5,
            'countMore' => 2,
        );
        $this->assertSame(Pagination::get(7, 6, 5, SORT_DESC), $actual);

        // last page
        $actual = array (
            'pageVar' => 'page',
            'pageCount' => 2,
            'pageCurrent' => 1,
            'pageStart' => 2,
            'pageEnd' => 1,
            'pageDisplay' =>
                array (
                    0 => 2,
                    1 => 1,
                ),
            'pagePrev' => 2,
            'pageFirst' => 2,
            'pageLast' => NULL,
            'offset' => 5,
            'limit' => 5,
            'countMore' => 0,
        );
        $this->assertSame(Pagination::get(7, 1, 5, SORT_DESC), $actual);
    }
}