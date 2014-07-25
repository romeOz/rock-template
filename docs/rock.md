Rock engine
=================

 * Aliases for path/url/namespace
 * [Placeholders](./#placeholders)
 * [Chunk](./#chunk)
 * [Filters](./#filters)
 * [Snippet](./#snippet)
 * [Autoescape](./#filters)
 * [Caching](./#caching)

Aliases for path/url/namespace
-----------------

Aliases are used to represent file paths, URLs, or namespace so that you don't have to hard-code absolute paths or URLs in your project.
An alias must start with the ```@``` character to be differentiated from normal file paths and URLs.
Template engine has many pre-defined aliases already available. For example, the alias ```@rock``` represents the installation path of the Rock Template; ```@rock.views``` represents the path of the views by default.

You can define an alias for a file path or URL:

```php
// an alias of a file path
Template::setAlias('views', '/to/path/views');

// an alias of a URL
Template::setAlias('site', 'http://www.site.com');

// an alias of a namespace
Template::setAlias('ns.backend', 'apps\\backend');
```

You can define an alias using another alias (either root or derived):

```php
Template::setAlias('@views.article', '@views/article');
```

###Using Aliases
Aliases are recognized in many places in Template engine without needing to call [[Template::getAlias()]] to convert them into paths or URLs.
For example, [[Template::render()]] can accept both a file path and an alias representing a file path, thanks to the ```@``` prefix which allows it to differentiate a file path from an alias.

```php
Template::setAlias('views', '/to/path/views');

echo (new Template)->render('@views/layout');
```

To template:

```html
{* For chunk *}
[[$@views/chunks/item]]

{* For snippet *}
[[@ns.backend\snippets\FooSnippet]]

{* Display alias *}
[[$$ns.backend\snippets\FooSnippet]]
```

Render
-----------------

###Inline render

```php
echo (new Template)->replace('Hello <b>[[+foo]]</b>', ['foo' => 'world!!!']);
```

###Render

```php
echo (new Template)->render('/to/path/layout', ['foo' => 'world!!!']);
```

With specifying the context:

```php
class FooController
{
    use rock\template\Template;

    public function actionIndex()
    {
        echo (new Template)->render('/to/path/layout', [], $this);
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

###Configure render

Through methods:

```php
$template = new Template;
$template->title = 'Test';
$template->registerMetaTag(['charset' => 'UTF-8']);
$template->registerMetaTag(['name' => 'description', 'content' => 'about'], 'description');
$template->registerLinkTag(['rel' => 'Shortcut Icon', 'type' => 'image/x-icon', 'href' => '/favicon.ico']);
$template->registerCssFile('/assets/css/main.css');
$template->registerJsFile('/assets/js/main.js');

echo $template->render('/to/path/layout');
```

Through array:

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

echo (new Template($config))->render('/to/path/layout');
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

Comments
-----------------

```html
{*
    Note: about...
*}
```

Text located between ```{* *}``` will not be displayed.

Placeholders
-----------------

The engine supports local and global placeholders.
> Note: local placeholders in contrast to global, used only in the context of the current tpl-entity (chunk, snippet).

```php
$template = new Template;

// Adding local placeholders
$template->addPlaceholder('foo', 'Hello');

// Adding global placeholders
$template->addPlaceholder('baz', 'Test', true);

echo (new Template)->replace('[[+foo]] <b>[[+bar]]</b> [[++baz]]', ['bar' => 'world!!!']);
```

Result:

```html
Hello <b>world!!!</b> Test
```

If placeholder is an array:

```html
[[+bar.subbar]]
```

Chunk
-----------------

Chunk is an html-entity. Autoescape is not affect.

```html
{* absolute path *}
[[$/to/path/chunk]]

{* using alias *}
[[$@views/chunk]]
```

Smart adding placeholders in the chunk

```html
[[$@views/chunk
    ?foo =`test`
    ?bar=`[[+bar]]`
]]
```

[Filters](https://github.com/romeo7/rock-template/blob/master/docs/filters.md)
-----------------

**[See](https://github.com/romeo7/rock-template/blob/master/docs/filters.md)**
