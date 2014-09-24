Render
================

Inline render
-----------------
> Only for Rock engine.

```php
echo (new Template)->replace('Hello <b>[[+foo]]</b>', ['foo' => 'world!!!']);
```

Render layout
-----------------

Depending on the extensions, you can use an appropriate engine (multi-engines). The template engine supports two types of engines: Rock engine (`.html`) and PHP engine (`.php`).

> In the absence of the extension, used engine default (`Rock engine`). You can specify explicitly: `new Template(['defaultEngine' => Template::ENGINE_PHP])`.

```php
echo (new Template)->render('/path/to/layout', ['foo' => 'world!!!']);
```

With specifying the context:

```php
class FooController
{
    use rock\template\Template;

    public function actionIndex()
    {
        echo (new Template)->render('/path/to/layout', [], $this);
    }

    public function getAll()
    {
        return [
            [
                'name' => 'Tom',
                'email' => 'tom@site.com',
                'about' => 'biography'
            ],
            [
                'name' => 'Chuck',
                'email' => 'chuck@site.com'
            ]
        ];
    }
}
```

Contents layout.html:

```html
[[ListView
    ?call=`context.getAll`
    ?tpl=`@views/chunks/item`
]]
```

Configure render
-----------------

As methods:

```php
$template = new Template;
$template->title = 'Test';
$template->registerMetaTag(['charset' => 'UTF-8']);
$template->registerMetaTag(['name' => 'description', 'content' => 'about'], 'description');
$template->registerLinkTag(['rel' => 'Shortcut Icon', 'type' => 'image/x-icon', 'href' => '/favicon.ico']);
$template->registerCssFile('/assets/css/main.css');
$template->registerJsFile('/assets/js/main.js');

echo $template->render('/path/to/layout');
```

or as array:

```php
$config = [
    'title' => 'Test',
    'metaTags' => function(){
            return [
                '<meta charset="UTF-8">',
                'description' => '<meta name="description" content="about">',
            ];
        },
    'linkTags' => [
        '<link type="image/x-icon" href="/favicon.ico" rel="Shortcut Icon">',
    ],
    'cssFiles' => [
        Template::POS_HEAD => [
            '<link href="/assets/css/main.css" >'
        ],
    ],
    'jsFiles' => [
        Template::POS_END => [
            '<script src="/assets/js/main.js"></script>'
        ]
    ],
];

echo (new Template($config))->render('/path/to/layout');
```

Result:

```html
<!DOCTYPE html>
<html>
<head>
    <title>Test</title>
    <meta charset="UTF-8">
    <meta name="description" content="about">
    <link type="image/x-icon" href="/favicon.ico">
    <link href="/assets/css/main.css" rel="stylesheet">
</head>
<body>
    <script src="/assets/js/main.js"></script>
</body>
</html>
```