Aliases for path/url/namespace
===============================

Aliases are used to represent file paths, URLs, or namespace so that you don't have to hard-code absolute paths or URLs in your project.
An alias must start with the ```@``` character to be differentiated from normal file paths and URLs.
Template engine has many pre-defined aliases already available. For example, the alias ```@rock``` represents the installation path of the Rock Template; ```@rock.views``` represents the path of the views by default.

You can define an alias for a file path or URL:

```php
// an alias of a file path
Alias::setAlias('views', '/path/to/views');

// an alias of a URL
Alias::setAlias('site', 'http://www.site.com');

// an alias of a namespace
Alias::setAlias('ns.backend', 'apps\\backend');
```

You can define an alias using another alias (either root or derived):

```php
Alias::setAlias('@views.article', '@views/article');
```

Using Aliases
---------------------

Aliases are recognized in many places in Template engine without needing to call `\rock\base\Alias::getAlias()` to convert them into paths or URLs.
For example, `\rock\template\Template::render()` can accept both a file path and an alias representing a file path, thanks to the ```@``` prefix which allows it to differentiate a file path from an alias.

For PHP engine:

```php
Alias::setAlias('views', '/path/to/views');

echo (new Template)->render('@views/layout');
```

For Rock engine:

```html
{* For chunk *}
[[$@views/chunks/item]]

{* For snippet *}
[[@ns.backend\snippets\FooSnippet]]

{* Display alias *}
[[@@ns.backend\snippets\FooSnippet]]
```