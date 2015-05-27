Pagination
====================
**Autoescape: disabled**

Build URL is performed `\rock\template\url\Url`.

> Note: templates for pagination built on [Twitter Bootstrap  3.2.0 "Pagination"](http://getbootstrap.com/components/#pagination).

Params
--------------------

###array

The data returned `\rock\template\helpers\Pagination::get()`.

###call

The data as an call. May be an callable, snippet, or instance.

```html
[[pagination?call=`\foo\FooController.getPagination`]]
[[pagination?call=`context.getPagination`]]
[[pagination?call=`FooSnippet`]]
```
or

```php
$template->getSnippet('pagination', ['call' => ['\foo\FooController', 'getPagination']]);
$template->getSnippet('pagination', ['call' => [new \foo\FooController(), 'getPagination']]);
$template->getSnippet('pagination', ['call' => function(){}]);
$template->getSnippet('pagination', ['call' => 'FooSnippet']);
```
###url
Set instance `\rock\url\Url` or config as array/JSON

```
[[pagination
    ?url=`{"class" : "\rock\url\Url", "query": "view=all&order=desc"}`
]]
```

or


```php
$config = [
    'url' => \rock\url\Url::set()->addArgs(['views' => 'all', 'order' => 'desc']);
];
$template->getSnippet('pagination', $config);
```

###pageLimit

Count buttons of pagination.

###pageParam

Name url-argument of pagination ("page" by default).

###pageNumTpl

Template for buttons. You can specify the path to chunk ```?pageNumTpl=`/path/to/chunk```/```?pageNumTpl=`@views/chunk``` or on the spot to specify a template ``` ?pageNumTpl=`@INLINE<b>[[+title]]</b>` ```.
Default: ```@rock.views/pagination/num```

###pageActiveTpl

Template for active button. [See syntax](#pagenumtpl).
Default: ```@rock.views/pagination/numActive```

###pageFirstTpl

Template for button "first". [See syntax](#pagenumtpl).
Default: ```@rock.views/pagination/first```

###pageLastTpl

Template for button "last". [See syntax](#pagenumtpl).
Default: ```@rock.views/pagination/last```

###wrapperTpl

Wrapper template. [See syntax](#pagenumtpl).
Default: ```@rock.views/pagination/wrapper```

###toPlaceholder

The name of global placeholder to adding the list. Becomes available anywhere in the template.

Example
-----------------

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

    public function getPagination()
    {
        $currentPage = isset($_GET['num']) ? (int)$_GET['num'] : null;
        return  \rock\template\helpers\Pagination::get(count($this->getAll()), $currentPage);
    }
}
```

Contents layout.html:

```html
[[pagination
    ?call = `context.getPagination`
]]
```

or for PHP engine:

```php
$template = new \rock\template\Template;
$countItems = 10;
$params = [
    'array' => \rock\helpers\Pagination::get($countItems, (int)$_GET['page'])
];
echo $template->getSnippet('\rock\snippet\Pagination', $params);
```