<?php

namespace rockunit\template;


use rock\base\Alias;
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
        Alias::setAlias('rockunit.views', $this->path);
        $this->template = $this->getTemplate();
    }

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        static::clearCache();
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        static::clearCache();
    }

    /**
     * @param array $config
     * @return Template
     */
    protected function getTemplate(array $config = [])
    {
        $config = array_merge([
            'chroots' => ['@template.views', '@rockunit.views'],
            'sanitize' => Template::SANITIZE_ESCAPE | Template::SANITIZE_TO_TYPE
        ], $config);
        return new Template($config);
    }

    public function removeSpace($value)
    {
        return preg_replace('/\\s+/', '', $value);
    }
} 