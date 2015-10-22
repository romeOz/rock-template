<?php

namespace rockunit\snippets;


use League\Flysystem\Adapter\Local;
use rock\base\Alias;
use rock\file\FileManager;
use rock\image\ImageProvider;
use rock\snippets\ThumbSnippet;
use rock\template\Template;
use rockunit\template\TemplateCommon;

class ThumbTest extends TemplateCommon
{
    protected function calculatePath()
    {
        $this->path = __DIR__ . '/data';
    }

    public function test()
    {
        $config = [
            'adapter' => [
                'class' => FileManager::className(),
                'adapter' => new Local(Alias::getAlias('@rockunit/data/imagine')),
            ],
            'adapterCache' => [
                'class' => FileManager::className(),
                'adapter' => new Local(Alias::getAlias('@rockunit/runtime/cache')),
            ],
        ];

        $template = new Template();
        $template->snippets = [
            'thumb' => [
                'class' => ThumbSnippet::className(),
                'imageProvider' => new ImageProvider($config)
            ]
        ];

        $this->assertSame(null, $template->getSnippet('thumb'));

        $params = ['w' => 50, 'h' => 50];
        $this->assertEmpty($template->getSnippet('thumb', $params));

        $params['src'] = 'large.jpg';
        $this->assertSame('/assets/cache/50x50/large.jpg', $template->getSnippet('thumb', $params));
        $this->assertTrue(file_exists(Alias::getAlias('@rockunit/runtime/cache/50x50/large.jpg')));

        // rock engine
        $actual = static::removeSpace($template->replace(
            '[[thumb
                ?src = `large.jpg`
                ?w = `50`
                ?h = `100`
            ]]'
        ));
        $this->assertSame('/assets/cache/50x100/large.jpg', $actual);

    }
}
