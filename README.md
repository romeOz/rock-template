Template engine for PHP
=======================

[![Latest Stable Version](https://poser.pugx.org/romeOz/rock-template/v/stable.svg)](https://packagist.org/packages/romeOz/rock-template)
[![Total Downloads](https://poser.pugx.org/romeOz/rock-template/downloads.svg)](https://packagist.org/packages/romeOz/rock-template)
[![Build Status](https://travis-ci.org/romeOz/rock-template.svg?branch=master)](https://travis-ci.org/romeOz/rock-template)
[![HHVM Status](http://hhvm.h4cc.de/badge/romeoz/rock-template.svg)](http://hhvm.h4cc.de/package/romeoz/rock-template)
[![Coverage Status](https://coveralls.io/repos/romeOz/rock-template/badge.svg?branch=master)](https://coveralls.io/r/romeOz/rock-template?branch=master)
[![License](https://poser.pugx.org/romeOz/rock-template/license.svg)](https://packagist.org/packages/romeOz/rock-template)

[Rock template on Packagist](https://packagist.org/packages/romeOz/rock-template)

Features
-------------------

 * Supports native PHP engine and declarative MODx-like syntax (placeholders, chunk, snippet,...)
 * Supports multi-engines
 * Multi-scopes (`$root` and `$parent`)
 * The variety of filters (arithmetic/bitwise operations, conditions, string, date, and url)
 * Custom auto-escaping
 * Support adding/customization filters and snippets
 * There is a possibility of adding custom extensions
 * Widgets **(option)**
 * Caching all entities template engine **(option)**
 * Standalone module/component for [Rock Framework](https://github.com/romeOz/rock)

Installation
-------------------

From the Command Line:

`composer require romeoz/rock-template:*`

In your composer.json:

```json
{
    "require": {
        "romeoz/rock-template": "*"
    }
}
```

Quick Start
-------------------

###PHP engine

```php
use rock\template\Template;

$template = new Template;

echo $template->render('/path/to/layout.php', ['foo' => 'world!!!']);
```

Contents layout.php:

```php
<?php
/** @var \rock\template\Template $this */
?>

Hello <b><?=$this->foo?></b>
```

###Rock engine

```php
use rock\template\Template;

echo (new Template)->render('/path/to/layout', ['foo' => 'world!!!']);
```

Contents layout.html:

```html
Hello <b>[[+foo]]</b>
```

Documentation
-------------------

 * [Guide to Rock engine](https://github.com/romeOz/rock-template/blob/master/docs/rock.md)
 * [Guide to PHP engine](https://github.com/romeOz/rock-template/blob/master/docs/php.md)

[Demo](https://github.com/romeOz/docker-rock-template)
-------------------

 * [Install Docker](https://docs.docker.com/installation/) or [askubuntu](http://askubuntu.com/a/473720)
 * `docker run --name demo -d -p 8080:80 romeoz/docker-rock-template`
 * Open demo [http://localhost:8080/](http://localhost:8080/)

Requirements
-------------------

 * **PHP 5.4+**
 * [Rock Cache](https://github.com/romeOz/rock-cache) **(optional)**. Should be installed: `composer require romeoz/rock-cache:*`
 * [Rock Widgets](https://github.com/romeOz/rock-widgets) **(optional)**. Should be installed: `composer require romeoz/rock-widgets:*`

License
-------------------

The Rock Template engine is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).