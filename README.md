Template engine for PHP
=======================

[![Latest Stable Version](https://poser.pugx.org/romeOz/rock-template/v/stable.svg)](https://packagist.org/packages/romeOz/rock-template)
[![Total Downloads](https://poser.pugx.org/romeOz/rock-template/downloads.svg)](https://packagist.org/packages/romeOz/rock-template)
[![Build Status](https://travis-ci.org/romeOz/rock-template.svg?branch=master)](https://travis-ci.org/romeOz/rock-template)
[![Coverage Status](https://coveralls.io/repos/romeOz/rock-template/badge.png?branch=master)](https://coveralls.io/r/romeOz/rock-template?branch=master)
[![License](https://poser.pugx.org/romeOz/rock-template/license.svg)](https://packagist.org/packages/romeOz/rock-template)

[Rock template on Packagist](https://packagist.org/packages/romeOz/rock-template)

Features
-------------------

 * Supports native PHP engine and declarative MODx-like syntax (placeholders, chunk, snippet,...)
 * Supports multi-engines
 * The variety of filters (arithmetic/bitwise operations, conditions, string, date, and url)
 * Custom auto-escaping
 * Support adding/customization filters and snippets
 * There is a possibility of adding custom extensions
 * Caching all entities template engine (suggest [Rock Cache](https://github.com/romeOz/rock-cache))

Installation
-------------------

From the Command Line:

```composer require romeoz/rock-template:*```

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

 * [Guide by Rock engine](https://github.com/romeOz/rock-template/blob/master/docs/rock.md)
 * [Guide by PHP engine](https://github.com/romeOz/rock-template/blob/master/docs/php.md)

[Demo](http://demo.template.framerock.net/) & Tests
-------------------

Destination:

[**DEMO**](http://demo.template.framerock.net/)

or local:

Use a specially prepared environment (Vagrant + Ansible).

###Out of the box:

 * Ubuntu 14.04 64 bit

> If you need to use 32 bit of Ubuntu, then uncomment `config.vm.box_url` the appropriate version in the file /path/to/`Vagrantfile`.

 * Nginx 1.6
 * PHP-FPM 5.5
 * Composer
 * For caching
    * Couchbase 2.2.0 + pecl couchbase-1.2.2 (**option**)
    * Redis 2.8 + php5-redis (**option**)
    * Memcached 1.4.14 + php5_memcached, php5_memcache
 * Local IP loop on Host machine /etc/hosts and Virtual hosts in Nginx already set up!

> To run all services marked `option` you should to uncomment them in the file `/path/to/provisioning/main.yml`.

###Installation:

1. [Install Composer](https://getcomposer.org/doc/00-intro.md#globally)
2. ```composer create-project --prefer-dist --stability=dev romeoz/rock-template```
3. [Install Vagrant](https://www.vagrantup.com/downloads), and additional Vagrant plugins ```vagrant plugin install vagrant-hostsupdater vagrant-vbguest vagrant-cachier```
4. ```vagrant up```
5. Open demo [http://rock.tpl/](http://rock.tpl/) or [http://192.168.33.34/](http://192.168.33.34/)

> Work/editing the project can be done via ssh:
```bash
vagrant ssh
cd /var/www/
```

Requirements
-------------------

 * **PHP 5.4+**
 * **For caching (optional):**
 suggested to use [Rock Cache](https://github.com/romeOz/rock-cache). Should be installed:

```
 composer require romeoz/rock-cache:*
```

or in your composer.json:

```json
 {
    "require": {
        "romeoz/rock-cache": "*"
    }
 }
```

License
-------------------

The Rock Template engine is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).