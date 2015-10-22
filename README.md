Template engine for PHP
=======================

[![Latest Stable Version](https://poser.pugx.org/romeOz/rock-template/v/stable.svg)](https://packagist.org/packages/romeOz/rock-template)
[![Total Downloads](https://poser.pugx.org/romeOz/rock-template/downloads.svg)](https://packagist.org/packages/romeOz/rock-template)
[![Build Status](https://travis-ci.org/romeOz/rock-template.svg?branch=master)](https://travis-ci.org/romeOz/rock-template)
[![HHVM Status](http://hhvm.h4cc.de/badge/romeoz/rock-template.svg)](http://hhvm.h4cc.de/package/romeoz/rock-template)
[![Coverage Status](https://coveralls.io/repos/romeOz/rock-template/badge.svg?branch=master)](https://coveralls.io/r/romeOz/rock-template?branch=master)
[![License](https://poser.pugx.org/romeOz/rock-template/license.svg)](https://packagist.org/packages/romeOz/rock-template)

Features
-------------------

 * Supports native PHP engine and declarative MODx-like syntax (placeholders, chunk, snippet,...)
 * Supports multi-engines
 * Multi-scopes (`$root` and `$parent`)
 * The variety of filters (arithmetic/bitwise operations, conditions, string, date, and url)
 * Custom auto-escaping
 * Support adding/customization filters and snippets
 * There is a possibility of adding custom extensions
 * Widgets
 * Caching all entities template engine
 * Standalone module/component for [Rock Framework](https://github.com/romeOz/rock)

Installation
-------------------

From the Command Line:

`composer require romeoz/rock-template`

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

 * [Guide on Rock engine](https://github.com/romeOz/rock-template/blob/master/docs/rock.md)
 * [Guide on PHP engine](https://github.com/romeOz/rock-template/blob/master/docs/php.md)

[Demo](https://github.com/romeOz/docker-rock-template)
-------------------

 * [Install Docker](https://docs.docker.com/installation/) or [askubuntu](http://askubuntu.com/a/473720)
 * `docker run --name demo -d -p 8080:80 romeoz/docker-rock-template`
 * Open demo [http://localhost:8080/](http://localhost:8080/)

Requirements
-------------------

 * **PHP 5.4+**
 * For caching layouts, chunks, placeholders and others variables required [Rock Cache](https://github.com/romeOz/rock-cache): `composer require romeoz/rock-cache`
 * For using a widgets required [Rock Widgets](https://github.com/romeOz/rock-widgets): `composer require romeoz/rock-widgets`
 * For validation rules a model required [Rock Validate](https://github.com/romeOz/rock-validate): `composer require romeoz/rock-validate`
 * For sanitization rules a model required [Rock Sanitize](https://github.com/romeOz/rock-sanitize): `composer require romeoz/rock-sanitize`
 * For using filters to snippets required [Rock Filters](https://github.com/romeOz/rock-filters): `composer require romeoz/rock-filters`
 * For editing a image (cropping, watermarks and etc) required [Rock Image](https://github.com/romeOz/rock-image): `composer require romeoz/rock-image`
 * For generating CSRF-token (security) required [Rock CSRF](https://github.com/romeOz/rock-csrf): `composer require romeoz/rock-csrf`
 * For building a datetime (DateTimeSnippet and filter date) required [Rock DateTime](https://github.com/romeOz/rock-date): `composer require romeoz/rock-date`

>All unbolded dependencies is optional

License
-------------------

The Rock Template engine is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).