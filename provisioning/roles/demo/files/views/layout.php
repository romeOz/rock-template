<?php

/** @var \rock\template\Template $this */

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

$params = [
    'array' => $list,
    'tpl' => '@demo.views/chunks/item',
    'wrapperTpl' => '@INLINE<div>[[!+output]]</div>',
    'pagination' => [
        'array' => \rock\helpers\Pagination::get(count($list), $currentPage, 1, SORT_DESC),
        'pageVar' => 'num',
        'pageAnchor' => 'pagination'
    ]
];

?>
<?=$this->getChunk('@demo.views/chunks/top_menu')?>
<div class="container main" role="main">
    <div class="demo-header">
        <h1 class="demo-title"><?=$this->title?></h1>
        <p class="lead demo-description">The example template.</p>
    </div>
    <div class="demo-main">
        <div class="demo-post-title">
            Snippets
        </div>
        <div class="demo-post-meta">
            ListView + Pagination
        </div>
        Contents index.php:
        <pre><code class="php"><!--
-->// set alias
\rock\template\Template::setAlias('@views', '/path/to/views')

$list = [
    [
        'name' => 'Tom',
        'email' => 'tom@site.com',
        'about' => '&lt;b&gt;biography&lt;/b&gt;'
    ],
    [
        'name' => 'Chuck',
        'email' => 'chuck@site.com'
    ]
];
$pagination = \rock\template\helpers\Pagination::get(count($list), null, 1, SORT_DESC);

// render template
echo (new \rock\template\Template)->render('@views/layout.php', ['list' => $list, 'pagination' => $pagination]);<!--
        --></code></pre>
        Contents layout.php:
        <pre><code class="php"><!--
 -->&lt;?php
/** @var \rock\template\Template $this */

$params = [
    'array' => $this->list,
    'tpl' => '@views/chunks/item',
    'wrapperTpl' => '@INLINE&lt;div&gt;[[!+output]]&lt;/div&gt;',
    'pagination' => [
        'array' => $this->pagination,
        'pageVar' => 'num',
    ]
];
?&gt;
&lt;?=$this->getSnippet('ListView', $params)?&gt;<!--
                --></code></pre>
        Result<a name="pagination"></a>:
        <pre><code class="html"><?=$this->getSnippet('ListView', $params)?></code></pre>
    </div>
</div>
<?=$this->getChunk('@demo.views/chunks/footer')?>