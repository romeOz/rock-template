Rock engine
=================

 * [Aliases for path/url/namespace](https://github.com/romeOz/rock-template/blob/master/docs/aliases.md)
 * [Render] (https://github.com/romeOz/rock-template/blob/master/docs/render.md)
 * [Placeholder `[[+placeholder]]`](#placeholder)
 * [Resource `[[*resource]]`](#resource)
 * [Chunk `[[$chunk]]`](#chunk)
 * [Filters `[[+placeholder:filter]]`](https://github.com/romeOz/rock-template/blob/master/docs/filters.md)
 * [Snippet `[[Snippet]]`](https://github.com/romeOz/rock-template/blob/master/docs/snippets/readme.md)
 * [Extension `[[#extension]]`](#extension)
 * [Autoescape `[[!+placeholder]]`](#autoescape)
 * [Comments](#comments)
 * [Shielding](#shielding)
 * [Caching](#caching)

Placeholder
-----------------

The engine supports local and global placeholders.
> Note: local placeholders in contrast to global, used only in the context of the current tpl-entity (chunk, snippet).

```php
$template = new Template;

// Adding local placeholders
$template->addPlaceholder('foo', 'Hello');

// Adding global placeholders
$template->addPlaceholder('baz', 'Test', true);

echo $template->replace('[[+foo]] <b>[[+bar]]</b> [[++baz]]', ['bar' => 'world!!!']);
```

Result:
```html
Hello <b>world!!!</b> Test
```

If placeholder is an array:
```html
[[+bar.subbar]]
```

Resource
-----------------

Typically, the page of the site is a resource with data, retrieved from the database or controller. E.g., article/topic have fields: `id`, `title`, `description`, `content`, `url`.
All these data it is appropriate to stored in `resources`. Are available anywhere template.

Adding/getting resource:

```php
$template = new Template;

$template->addResource('content', 'Text...');

echo $template->replace('<article>[[*content]]</article>');
```

Chunk
-----------------

Chunk is an html-entity. Autoescape is not affect.

```html
{* absolute path *}
[[$/path/to/chunk]]

{* using alias *}
[[$@views/chunk]]
```

Adding local placeholders in the chunk:

```html
[[$@views/chunk
    ?foo =`test`
    ?bar=`[[+bar]]`
]]
```

Extension
-----------------

You can extend template engine.

###Example

```php
use \rock\template\Template;

$template = new Template;
$template->extensions = [
    'user' => function (array $keys, array $params = [], Template $template)
    {
        $user = new User;
        if (current($keys) === 'isGuest') {
            return $user->isGuest();
        } elseif (current($keys) === 'isLogin') {
            return !$user->isGuest();
        }
        return \rock\template\helpers\ArrayHelper::getValue($user->getAll(), $keys);
    }
];

echo $template->replace('[[#user.firstname]]');
```

Autoescape
-----------------

By default, escaping be made on all entities of a template, except chunks and some snippets (`listView`, `For`,... see to the docs snippet).

###Include

You can set globally autoescape in config of `\rock\template\Template`:

```php
use \rock\template\Template;

$config = [
    'autoescape' => Template::STRIP_TAGS // only strip tags
];

$template = new Template($config);
```

or

```php
use \rock\template\Template;

$template = new Template;
$template->autoescape = Template::STRIP_TAGS;
```
> Note: the property `autoescape` has a default value: `\rock\template\Template::ESCAPE`.

###Usage

You can specify custom escaping for any entity Template engine:

```html
[[+foo?autoEscape=`false`]]

[[Snippet?autoEscape=`4`]] // to type

[[#user.about?autoEscape=`true`]]
```

Possible value:

 * true - default globally autoescape `rock\template\Template::ESCAPE`.
 * false - doesn't escape.
 * integer - `Template::ESCAPE`, `Template::STRIP_TAGS`, `Template::TO_TYPE`, or derived from bitwise operations.

Sugar for `false`:

```
[[!+foo]]

// equivalent

[[+foo?autoEscape=`false`]]
```

Comments
-----------------

```html
{*
    Note: about...
*}
```

Text located between `{* ... *}` will not be displayed.

Shielding
-----------------

Necessary for displaying the entities of the template engine.

```html
{! [[listView]] !}
```

Text located between `{! ... !}` will not be replaced.

Caching
------------------

You can caching any entity template engine.

###Include

```php
use \rock\template\Template;

$config = [
    'cache' => new \rock\cache\Memcached;
];

$template = new Template($config);
```

or

```php
use \rock\template\Template;

$template = new Template;
$template->cache = new \rock\cache\Memcached;
```

###Usage

The parameters to use for caching:

 * `cacheKey` - key of cache
 * `cacheExpire` - expire time
 * `cacheTags` - tags of cache

###Example

```html
[[+placeholder
    ?cacheKey=`plh`
    ?cacheExpire=`3600`
]]

[[listView
    ?call=`\foo\FooController.getAll`
    ?cacheKey=`list`
    ?cacheTags=`["articles", "news"]`
]]
```

More detailed information see [Rock cache](https://github.com/romeOz/rock-cache/).