<?php

namespace rockunit;


use rock\template\helpers\Html;

class HtmlTest extends \PHPUnit_Framework_TestCase
{
    public function testEncode()
    {
        // encode
        $this->assertSame(Html::encode('<b>foo</b>'), '&lt;b&gt;foo&lt;/b&gt;');

        // decode
        $this->assertSame(Html::decode(Html::encode('<b>foo</b>')), '<b>foo</b>');
    }
}
 