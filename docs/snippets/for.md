for
====================
**Autoescape: disabled**

Params
--------------------

###count

Count iteration.

###addPlaceholders
Adding external placeholders in `tpl` and `wrapperTpl`. Example: ``` ?addPlaceholders=`{"foo" : "[[+foo]]"}` ``` or in the short form ``` ?addPlaceholders=`["foo"]` ```.

###tpl
Wrapper for item. You can specify the path to chunk ```?tpl=`/path/to/chunk```/```?tpl=`@views/chunk``` or on the spot to specify a template ``` ?tpl=`@INLINE<b>[[+title]]</b>` ```.

###wrapperTpl
Wrapper for all items. You can specify the path to chunk ```?wrapperTpl=`/path/to/chunk```/```?wrapperTpl=`@views/chunk``` or on the spot to specify a template ``` ?wrapperTpl=`@INLINE<p>[[+output]]</p>` ```.

Example
--------------------

```html
[[for
    ?count=`2`
    ?tpl=`@INLINE<b>[[+foo]]</b>`
    ?addPlaceholders=`["foo"]`
    ?wrapperTpl=`@INLINE<p>[[!+output]]</p>`
]]
```