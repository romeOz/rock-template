<?php
use rock\helpers\Pagination;
use rock\template\Template;

include_once(__DIR__ . '/vendor/autoload.php');


Template::setAlias('@runtime', __DIR__ . '/runtime');
Template::setAlias('@demo.views', __DIR__ . '/views');


$config = [
    'head' => "<!DOCTYPE html>\n<html lang=\"en\">",
    'title' => 'Demo by Rock engine',
    'metaTags' => [
        '<meta charset="UTF-8">',
        '<meta content="width=device-width, initial-scale=1" name="viewport">',
        'description' => '<meta name="description" content="rock engine">',
    ],
    'linkTags' => [
        '<link type="image/x-icon" href="/assets/ico/favicon.ico?10" rel="icon">',
    ],
    'cssFiles' => [
        Template::POS_HEAD => [
            '<link href="/assets/css/bootstrap.min.css" rel="stylesheet">',
            '<link href="/assets/css/highlight/github.css" rel="stylesheet">',
            '<link href="/assets/css/demo.css" rel="stylesheet">'
        ],
    ],
    'jsFiles' => [
        Template::POS_END => [
            '<script src="/assets/js/jquery.min.js"></script>',
            '<script src="/assets/js/bootstrap.min.js"></script>',
            '<script src="/assets/js/highlight.pack.js"></script>',
            '<script src="/assets/js/demo.js"></script>',
        ]
    ],
];

$template = new Template($config);
$template->addPlaceholder('active.rock', true, true);

$list = [
    [
        'name' => 'Tom',
        'email' => 'tom@site.com',
        'about' => '<b>biography</b>'
    ],
    [
        'name' => 'Chuck',
        'email' => 'chuck@site.com'
    ]
];
$currentPage = isset($_GET['num']) ? (int)$_GET['num'] : null;
echo $template->render(
    '@demo.views/layout',
    [
        'title' => 'Demo by Rock engine',
        'demo' =>
            [
                'url' => '/categories/?view=all',
                'date' => '2014-02-12 15:01',
                'num' => 3,
                'title' => 'Hello world',
                'list' => $list,
                'pagination' =>  Pagination::get(count($list), $currentPage, 1, SORT_DESC)
            ]
    ]
);