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

 * [Guide by Rock engine](https://github.com/romeOz/rock-template/blob/master/docs/rock.md)
 * [Guide by PHP engine](https://github.com/romeOz/rock-template/blob/master/docs/php.md)

Demo & Tests (one of three ways)
-------------------

 1. [Destination](http://demo.template.framerock.net/)
 2. Docker + Ansible
    * `docker run -d -p 8080:80 romeoz/vagrant-rock-template`
    * Open demo [http://localhost:8080/](http://localhost:8080/)
 3. Vagrant + Ansible
    * `git clone https://github.com/romeOz/vagrant-rock-template.git`
    * [Install Vagrant](https://www.vagrantup.com/downloads), and additional Vagrant plugins `vagrant plugin install vagrant-hostsupdater vagrant-vbguest vagrant-cachier`
    * `vagrant up`
    * Open demo [http://rock.tpl/](http://rock.tpl/) or [http://192.168.33.34/](http://192.168.33.34/)

> Work/editing the project can be done via ssh:

```bash
vagrant ssh
cd /var/www/rock-template
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