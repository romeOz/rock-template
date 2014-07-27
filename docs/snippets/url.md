Url
====================
**Autoescape: `\rock\template\Template::STRIP_TAGS`**

Build is performed `\rock\template\url\Url`.

Params
--------------------

###url

Current url by default.

###args
Set args.

###addArgs
Adding args to existing.

###anchor
Adding anchor.

###beginPath
Concat to begin URL.

###endPath
Concat to end URL.

###removeArgs
Selective removing arguments.

###removeAllArgs
Removing all arguments.

###removeAnchor
Removing anchor.

###const
All constants are see in `\rock\template\url\UrlInterface`.

Example
--------------------

```html
[[Url
    ?url=`http://site.com/categories/?view=all`
    ?args=`{"page" : 1}`
    ?beginPath=`/parts`
    ?endPath=`/news/`
    ?anchor=`name`
    ?const=`32`
]]
 ```