Template engine for PHP
=================

[![Latest Stable Version](https://poser.pugx.org/romeo7/rock-template/v/stable.svg)](https://packagist.org/packages/romeo7/rock-template)
[![Total Downloads](https://poser.pugx.org/romeo7/rock-template/downloads.svg)](https://packagist.org/packages/romeo7/rock-template)
[![Build Status](https://travis-ci.org/romeo7/rock-template.svg?branch=master)](https://travis-ci.org/romeo7/rock-template)
[![Coverage Status](https://coveralls.io/repos/romeo7/rock-template/badge.png?branch=master)](https://coveralls.io/r/romeo7/rock-template?branch=master)
[![License](https://poser.pugx.org/romeo7/rock-template/license.svg)](https://packagist.org/packages/romeo7/rock-template)

[Rock template on Packagist](https://packagist.org/packages/romeo7/rock-template)

Features
-------------------

 * Supports native PHP engine and declarative MODx-like syntax (placeholders, chunk, snippet,...)
 * The variety of filters (arithmetic/logic operations, conditions, string, date, and url)
 * Custom autoescaping
 * Support adding/customization filters and snippets
 * There is a possibility of adding extensions
 * Caching all entities templating engine (suggest [Rock Cache](https://github.com/romeo7/rock-cache))

Installation
-------------------

From the Command Line:

```composer require romeo7/rock-template:*```

In your composer.json:

```json
{
 "require": {
     "romeo7/rock-template": "*"
 }
}
```

Requirements
-------------------

 * **PHP 5.4+**
 * **For caching (optional):**
 suggested to use [Rock Cache](https://github.com/romeo7/rock-cache). Should be installed: ```composer require romeo7/rock-cache:*``` or in your composer.json:
 ```json
 {
  "require": {
      "romeo7/rock-cache": "*"
  }
 }
 ```

License
-------------------

The Rock Template engine is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).