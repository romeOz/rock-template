DateTimeSnippet
====================

Required installed [Rock DateTime](https://github.com/romeOz/rock-date): `composer require romeOz/rock-date`.

Params
--------------------

###date

Datetime. `now` by default.

###format

Support [`date()` formats](http://php.net/manual/en/function.date.php).
You can using presets for formats: `date`, `time`, `datetime`, `js`, `w3c` and [other](https://github.com/romeOz/rock-date).

###timezone

E.g. `America/Chicago`.

Example
--------------------

Returns formatted now date:

```html
[[date
    ?format=`j n`
]]
```

With custom format:

```html
[[date
    ?date=`2014-02-12 15:01`
    ?format=`dmyhm`
]]
```