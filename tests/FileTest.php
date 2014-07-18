<?php

namespace rockunit;


use rock\template\helpers\File;
use rockunit\common\CommonTrait;

class FileTest extends \PHPUnit_Framework_TestCase
{
    use CommonTrait;

    protected function setUp()
    {
        parent::setUp();
        static::clearRuntime();
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->setUp();
    }


    public function testCreateDir()
    {
        $this->assertTrue(File::createDirectory('@runtime/tmp'));
        $this->assertTrue(File::createDirectory('@runtime/tmp'));
    }
}
 