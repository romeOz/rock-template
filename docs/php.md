PHP engine
=================

 * [Aliases for path/url/namespace](https://github.com/romeOz/rock-template/blob/master/docs/aliases.md)
 * [Render] (https://github.com/romeOz/rock-template/blob/master/docs/render.md)
 * [Placeholder](#placeholder)
 * [Resource](#resource)
 * [Chunk](#chunk)
 * [Snippet](https://github.com/romeOz/rock-template/blob/master/docs/snippets/readme.md)
 * [Autoescape](#autoescape)
 * [Caching](#caching)

Placeholder
-----------------

The engine supports local and global placeholders.
> Note: local placeholders in contrast to global, used only in the context of the current tpl-entity (chunk, snippet).

Adding/getting local placeholder:

```php
$template = new Template;
$template->addPlaceholder('foo', 'Hello');

// or by using setter
$template->foo = 'Hello';

echo $template->getPlaceholder('foo'); // result: Hello

// or by using getter

echo $template->foo; // result: Hello
```

Adding/getting a global placeholder:

```php
$template = new Template;
$template->addPlaceholder('foo', 'Hello', true);

echo $template->getPlaceholder('foo', false, true); // result: Hello

// or by using getter

echo $template->foo; // result: Hello
```

__set() and __unset() using only for local placeholders.

> Conflict resolution of names when using getter (`__get()`): The first is returned to the local placeholder, second - global placeholder, and third - resource.

If placeholder is an array:

```php
$template->foo = ['bar' => 'Hello'];

echo $template->getPlaceholder('foo.bar'); // result: Hello
```

Resource
-----------------

Typically, the page of the site is a resource with data, retrieved from the database or controller. E.g., article/topic: id, title, description, content, url.
All these data it is appropriate to stored in `resources`. Are available anywhere template.

Adding/getting resource:

```php
$template = new Template;

$template->addResource('content', 'Text...');

echo $template->getResource('content'); // result: Text...

// or by using getter

echo $template->content; // result: Text...
```

Chunk
-----------------

Chunk is an html-entity. Autoescape is not affect.

```php
$template = new Template;

//absolute path
echo $template->getChunk('/path/to/chunk.php', ['foo' => 'text']);

// using alias
echo $template->getChunk('@views/chunk.php', ['foo' => 'text']);
```

Autoescape
-----------------

By default, escaping be made on all entities of a template, except chunks and some snippets (`listView`, `for`,... see to the docs snippet).

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

```php
$template = new Template;

$template->getPlaceholder('foo', false);
$template->getSnippet('Snippet', [], Template::TO_TYPE);
```

Possible value:

 * true - default globally autoescape `rock\template\Template::ESCAPE`.
 * false - doesn't escape.
 * integer - `Template::ESCAPE`, `Template::STRIP_TAGS`, `Template::TO_TYPE`, or derived from bitwise operations.

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

```php
$params = [
    'cacheKey' => 'plh',
    'cacheExpire' => 3600
];
echo $template->getPlaceholder('placeholder', $params);

$params = [
    'cacheKey' => 'list',
    'cacheTags' => ['articles', 'news']
];
echo $template->getSnippet('listView', $params);
```

Grouping multiple entities:

```php
$cache = \rock\cache\Memcached;

// get the content from the cache
if (($result = $cache->get('list')) !== false) {
    return $result;
}

$result = $template->getPlaceholder('placeholder');
$result .= $template->getSnippet('listView');

// set cache
$cache->set('list', $result, 3600);

return $result;
```

More detailed information [see Rock Cache](https://github.com/romeOz/rock-cache/).