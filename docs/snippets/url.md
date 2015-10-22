UrlSnipet
====================
**Autoescape: `\rock\template\Template::STRIP_TAGS`**

Based on [Rock Url](https://github.com/romeOz/rock-url).

Params
--------------------

###modify
Modify arguments.

###scheme

`abs`, `http`, `https` and `rel` (default).
 
>All constants are [see](https://github.com/romeOz/rock-url) (`\rock\url\UrlInterface`).

###csrf

`true/false`

Adding a CSRF-token.

>Required installed [Rock CSRF](https://github.com/romeOz/rock-csrf): `composer require romeOz/rock-csrf`.

Example
--------------------

```html
[[url
    ?modify=`{"/articles/football/?view=all", "page" : 1, "#": "name"}`
    ?scheme=`abs`
]]
 ```