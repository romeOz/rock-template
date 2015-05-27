listView
====================
**Autoescape: disabled**

Display list items + pagination.

Params
--------------------

###array

The data as an array.

###call

The data as an call. May be a callable, snippet, and instance.

```html
[[listView?call=`\foo\FooController.getAll`]]
[[listView?call=`context.getAll`]]
[[listView?call=`FooSnippet`]]
```
or

```php
$template->getSnippet('listView', ['call' => ['\foo\FooController', 'getAll']]);
$template->getSnippet('listView', ['call' => [new \foo\FooController(), 'getAll']]);
$template->getSnippet('listView', ['call' => function(){}]);
$template->getSnippet('listView', ['call' => 'FooSnippet']);
```

###addPlaceholders

Adding external placeholders in `tpl` and `wrapperTpl`. Example: ``` ?addPlaceholders=`{"foo" : "[[+foo]]"}` ``` or in the short form ``` ?addPlaceholders=`["foo"]` ```.

###prepare

Prepare item. May be an callable, snippet, or instance. You can implement the hook, e.g. not a repetition of the date:

```html
22 Sep 2011
15:33 news_3
12:15 news_2

21 Sep 2011
22:17 news_1
```

**Example:**

```php
$params =  [
    'array' => [...]
    'prepare' => [
        'call' => function(array $placeholders){
            $placeholder['title'] = \rock\template\helpers\String::truncateWords($placeholder['title'], 15);
            return $placeholders;
        }
    ]
];
$template->getSnippet('listView', $params);
```
> Note: must return prepared placeholders.


###tpl

Wrapper for item. You can specify the path to chunk ```?tpl=`/path/to/chunk```/```?tpl=`@views/chunk``` or on the spot to specify a template ``` ?tpl=`@INLINE<b>[[+title]]</b>` ```.

###wrapperTpl

Wrapper for all items. You can specify the path to chunk ```?wrapperTpl=`/path/to/chunk```/```?wrapperTpl=`@views/chunk``` or on the spot to specify a template ``` ?wrapperTpl=`@INLINE<b>[[+title]]</b>` ```.

###toPlaceholder

The name of global placeholder to adding the list. Becomes available anywhere in the template.

###errorText

Display the text of the error, if the data are empty. '' by default.

###pagination

Integration [Pagination (snippet)](https://github.com/romeOz/rock-template/blob/master/docs/snippets/pagination.md).
Params:

 * array - the data returned `\rock\template\helpers\Pagination::get()`.
 * call - the data as an call. May be an callable, snippet, or instance.
 * pageLimit - count buttons of pagination.
 * pageParam - name url-argument of pagination ("page" by default).
 * wrapperTpl - wrapper template.
 * pageNumTpl - template for buttons.
 * pageActiveTpl - template for active button.
 * pageFirstTpl - template for button "first".
 * pageLastTpl - template for button  "last".
 * toPlaceholder - the name of global placeholder to adding the pagination.

> Note: templates for pagination built on [Twitter Bootstrap 3.2.0 "Pagination"](http://getbootstrap.com/components/#pagination)

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
[[listView
    ?call = `context.getAll`
    ?tpl = `@views/chunks/item`
    ?pagination=`{
       "call" : "context.getPagination",
       "pageParam" : "num"
    }`
]]
```

or for PHP engine (layout.php):

```php
/** @var \rock\template\Template $this */

$params = [
    'array' => $this->context->getAll(),
    'tpl' => '@views/chunks/item',
    'pagination' => [
        'array' => $this->context->getPagination(),
        'pageParam' => 'num'
    ]
];

echo $this->getSnippet('listView', $params);
```