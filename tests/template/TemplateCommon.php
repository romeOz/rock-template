<?php

namespace rockunit\template;


use rock\base\Alias;
use rock\helpers\Instance;
use rock\template\Template;
use rockunit\common\CommonTestTrait;

abstract class TemplateCommon extends \PHPUnit_Framework_TestCase
{
    use CommonTestTrait;

    protected $path;

    protected $filters = [];
    protected $snippets = [];
    /** @var  Template */
    protected $template;

    abstract protected function calculatePath();

    protected function setUp()
    {
        parent::setUp();
        $this->calculatePath();
        Alias::setAlias('rockunit.tpl', $this->path);

        $config  = [
            'autoEscape' => Template::ESCAPE | Template::TO_TYPE
        ];
        $this->template = new Template($config);
        $this->template->removeAllPlaceholders();
    }

    public function removeSpace($value)
    {
        return preg_replace('/\\s+/', '', $value);
    }
} 