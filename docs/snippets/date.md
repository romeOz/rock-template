Date
====================

Build is performed `\rock\template\date\DateTime`.

Params
--------------------

###date

Datetime. `now` by default.

###format
Format of datetime. `\rock\template\date\DateTime::DateTime::DEFAULT_FORMAT` by default.

###timezone
E.g. `America/Chicago`.

Example
--------------------

Get formatted now date:

```html
[[Date
    ?format=`j n`
]]
```

With custom format:

```html
[[Date
    ?date=`2014-02-12 15:01`
    ?format=`dmyhm`
]]
```