ListView
==========
**Autoescape: disabled**

Display list items + pagination.

##Params
**array** -  the data as an array.

**call** -  the data as an call. May be a callable, snippet, and instance.

```html
[[ListView?call=`\foo\FooController.getAll`]]
[[ListView?call=`context.getAll`]]
[[ListView?call=`FooSnippet`]]
```
or

```php
$template->getSnippet('ListView', ['call' => ['\foo\FooController', 'getAll']]);
$template->getSnippet('ListView', ['call' => [new \foo\FooController(), 'getAll']]);
$template->getSnippet('ListView', ['call' => function(){}]);
$template->getSnippet('ListView', ['call' => 'FooSnippet']);
```

**addPlaceholders** - adding placeholders in ```tpl``` and ```wrapperTpl```.

**prepare** - prepare item. May be a callable, snippet, and instance. You can implement the hook, e.g. not a repetition of the date:

```html
22 Sep 2011
15:33 news_3
12:15 news_2

21 Sep 2011
22:17 news_1
```

Example:

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
$template->getSnippet('ListView', $params);
```
> Note: must return prepared placeholders.


**tpl** - wrapper for item. You can specify the path to chunk ```?tpl=`/to/path/chunk```/```?tpl=`@views/chunk``` or on the spot to specify a template ``` ?tpl=`@INLINE<b>[[+title]]</b>` ```.

**wrapperTpl** - wrapper for all items. You can specify the path to chunk ```?tpl=`/to/path/chunk```/```?tpl=`@views/chunk``` or on the spot to specify a template ``` ?tpl=`@INLINE<b>[[+title]]</b>` ```.

**toPlaceholder** - the name of global placeholder to adding the list. Becomes available anywhere in the template.

**errorText** - display the text of the error, if the data are empty. '' by default.

**pagination** - integration [Pagination (snippet)](https://github.com/romeo7/rock-template/blob/master/docs/snippets/pagination.md). Params:

    * array - \rock\template\helpers\Pagination::get().
    * call - the data as an call. May be a callable, snippet, and instance.
    * pageLimit - count buttons of pagination
    * pageVar - name url-argument of pagination ("page" by default)
    * pageArgs - url-arguments of pagination
    * pageAnchor - url-anchor of pagination
    * wrapperTpl - wrapper template
    * pageNumTpl       - template for buttons
    * pageActiveTpl    - template for active button
    * pageFirstTpl     - template for button "first"
    * pageLastTpl      - template for button  "end"
    * toPlaceholder    - the name of global placeholder to adding the pagination