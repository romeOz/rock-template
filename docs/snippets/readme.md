Snippets
================

Snippets can simplify the implementation of some routine operations.
For Rock engine parameters of snippet are specified in the string type. The array must be in a [JSON](http://en.wikipedia.org/wiki/JSON) (e.g. ``` ?array=`[{"name" : "Tom", "email" : "tom@site.com"}, {"name" : "Chuck"}]` ```).
All parameters of snippet are automatically converted to the type (e.g. ``` ?is=`null` ```).

 * [Usage](#usage)
 * [ListView](https://github.com/romeOz/rock-template/blob/master/docs/snippets/list-view.md)
 * [Pagination](https://github.com/romeOz/rock-template/blob/master/docs/snippets/pagination.md)
 * [For](https://github.com/romeOz/rock-template/blob/master/docs/snippets/for.md)
 * [If](https://github.com/romeOz/rock-template/blob/master/docs/snippets/if.md)
 * [Formula](https://github.com/romeOz/rock-template/blob/master/docs/snippets/formula.md)
 * [Date](https://github.com/romeOz/rock-template/blob/master/docs/snippets/date.md)
 * [Url](https://github.com/romeOz/rock-template/blob/master/docs/snippets/url.md)
 * [Custom snippet](#custom-snippet)

Usage
----------------

For Rock engine:

```html
[[ListView]]
```

For PHP engine:

```php
echo $template->getSnippet('ListView');
```

Custom snippet
----------------

Adding to existing snippets:

```php
use rock\template\Snippet;

class CustomSnippet extends Snippet
{
  public $charset = 'UTF-8';

    public function get()
    {
        return mb_strlen($value, $this->charset);
    }
}

$config = [
    'snippets' => [
        'CustomSnippet' => [
            'class' => CustomSnippet::className(),
        ],
    ]
];
$template = new Template($config);
```

if you want to specify the alias:

```php
$config = [
    'snippets' => [
        'CustomSnippet' => [
            'class' => CustomSnippet::className(),
        ],
        'Custom' => [
           'class' => CustomSnippet::className(),
        ],
    ]
];
```

if you want to specify autoescape:

> Autoescape: true by default.

```php
class CustomSnippet extends Snippet
{
    public $autoescape = false; // disabled autoescape
    ...
}
```

More detailed information [see "Autoescape"](https://github.com/romeOz/rock-template/blob/master/docs/rock.md#autoescape)