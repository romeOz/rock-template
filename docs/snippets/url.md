Url
====================
**Autoescape: `\rock\template\Template::STRIP_TAGS`**

Build is performed `\rock\template\url\Url`.

Params
--------------------

###modify
Modify arguments.

###scheme
All constants are see in `\rock\template\url\UrlInterface`.

Example
--------------------

```html
[[url
    ?modify=`{"/articles/football/?view=all", "page" : 1, "#": "name"}`
    ?scheme=`abs`
]]
 ```