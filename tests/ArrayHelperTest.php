<?php

namespace rockunit;


use rock\template\helpers\ArrayHelper;

class ArrayHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider valueProvider
     *
     * @param $key
     * @param $expected
     * @param null $default
     */
    public function testGetValue($key, $expected, $default = null)
    {
        $array = [
            'name' => 'test',
            'date' => '31-12-2113',
            'post' => [
                'id' => 5,
                'author' => [
                    'name' => 'romeo',
                    'profile' => [
                        'title' => '1337',
                    ],
                ],
            ],
            'admin.firstname' => 'Sergey',
            'admin.lastname' => 'Galka',
            'admin' => [
                'lastname' => 'romeo',
            ],
        ];

        $this->assertEquals($expected, ArrayHelper::getValue($array, $key, $default));
    }


    public function valueProvider()
    {
        return [
            ['name', 'test'],
            ['noname', null],
            ['noname', 'test', 'test'],
            ['post.id', 5],
            [['post', 'id'], 5],
            ['post.id', 5, 'test'],
            ['nopost.id', null],
            ['nopost.id', 'test', 'test'],
            ['post.author.name', 'romeo'],
            ['post.author.noname', null],
            ['post.author.noname', 'test', 'test'],
            ['post.author.profile.title', '1337'],
            ['admin.firstname', 'Sergey'],
            ['admin.firstname', 'Sergey', 'test'],
            ['admin.lastname', 'Galka'],
            [
                function ($array, $defaultValue) {
                    return $array['date'] . $defaultValue;
                },
                '31-12-2113test',
                'test'
            ],
            [[], [
                'name' => 'test',
                'date' => '31-12-2113',
                'post' => [
                    'id' => 5,
                    'author' => [
                        'name' => 'romeo',
                        'profile' => [
                            'title' => '1337',
                        ],
                    ],
                ],
                'admin.firstname' => 'Sergey',
                'admin.lastname' => 'Galka',
                'admin' => [
                    'lastname' => 'romeo',
                ],
            ]],
        ];
    }

    public function testGetValueAsObject()
    {
        $object = new \stdClass();
        $subobject = new \stdClass();
        $subobject->bar = 'test';
        $object->foo = $subobject;
        $object->baz = 'text';
        $this->assertSame(ArrayHelper::getValue($object, 'foo.bar'), 'test');
        $this->assertSame(ArrayHelper::getValue($object, ['foo', 'bar']), 'test');
        $this->assertSame(ArrayHelper::getValue($object, 'baz'), 'text');
    }

    public function testIntersectByKeys()
    {
        $this->assertSame(ArrayHelper::intersectByKeys(['foo'=> 'foo', 'bar' => 'bar'], ['bar']), ['bar' => 'bar']);
    }

    public function testDiffByKeys()
    {
        $this->assertSame(ArrayHelper::diffByKeys(['foo'=> 'foo', 'bar' => 'bar'], ['bar']), ['foo' => 'foo']);
    }

    public function testMap()
    {
        $callback = function() {
            return 'test';
        };
        $this->assertSame(ArrayHelper::map(['foo' => 'foo', 'bar' => 'bar'], $callback, false, 1), ['foo' => 'test', 'bar' => 'bar']);

        // recurcive
        $this->assertSame(ArrayHelper::map(['foo' => 'foo', 'bar' => ['baz' => 'baz']], $callback, true), ['foo' => 'test', 'bar' => ['baz' => 'test']]);
    }
}