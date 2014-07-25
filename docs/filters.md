Filters
=================

An filter must start with the ```:``` character. Used filter parameters ``` &is=`null` ``` are automatically converted to the type.

 * cascade
 * conditions
 * string
     * size
     * truncate
     * truncateWords
     * lower
     * upper
     * upperFirst
     * trimPattern
     * contains
     * encode
     * decode
 * numeric
     * isParity
     * positive
 * arithmetic operations
     * addition
     * negation
     * multiplication
     * exponential expression
     * division
     * modulus
 * bitwise operations
    * or
    * and
    * xor
    * shift the bits to the left
    * shift the bits to the right
 * other
     * url
     * date
     * toJson
     * toArray
     * replaceTpl
     * php-function
 * custom filter

Cascade
------------------

Supported of the cascade filters.

```php
$replace =
    '[[+foo
       :unserialize
            &key=`bar.subbar`
    :size

    ]]';
echo (new Template)->replace($replace, ['foo' => '{"bar" : {"subbar" : "test"}}']); // result 4
```
A situation may arise when, after applying cascade filters, variable will need to check on the condition.

```html
[[+foo
    :unserialize
          &key=`bar.subbar`
    :size
    && `[[+foo:unserialize&key=`bar.subbar`:size]]`
]]
```
To doesn't to apply filters again, in the conditions settings, available placeholder [[+output]]

```html
[[+foo
    :unserialize
          &key=`bar.subbar`
    :size

    && `[[+output]]`
]]
```

Conditions
------------------
> Note: supported ```[[+output]]```

Success:

```php
$replace =
    '[[+foo
       :if
           &is=``
           &then=`[[+bar]]`
           &else=`[[+foo]]`
    ]]';
echo (new Template)->replace($replace, ['bar' => 'bar_test']); // result 'bar_test'
```

Sugar:

```html
[[+foo is `` ? `[[+bar]]` : `[[+foo]]`]]
```

###Is empty

Aliases: ```isequalto```, ```isequal```, ```equalto```, ```equals```, ```is```, ```eq```

```html
[[+foo
   :if
       &is=``
       &then=`[[+bar]]`
]]
```

Sugar:

```html
[[+foo || `[[+bar]]`]]
```

###Is not empty

Aliases: ```notequalto```, ```notequals```, ```isnt```, ```isnot```, ```neq```, ```ne```

```html
[[+foo
   :if
       &isnot=``
       &then=`[[+bar]]`
]]
```

Sugar:

```html
[[+foo && `[[+bar]]`]]
```

###Greater than

Aliases: ```isgreaterthan```, ```greaterthan```, ```isgt```, ```gt```

```html
[[+number gt `3` ? `success` : `fail`]]
```

###Equal or greater then

Aliases: ```greaterthanorequalto```, ```equalorgreaterthen```, ```ge```, ```eg```, ```isgte```, ```gte```

```html
[[+number gte `3` ? `success` : `fail`]]
```

###Is less than

Aliases: ```islowerthan```, ```islessthan```, ```lowerthan```, ```lessthan```, ```islt```, ```lt```

```html
[[+number lt `3` ? `success` : `fail`]]
```

###Equal or less than

Aliases: ```equaltoorlessthan```, ```lessthanorequalto```, ```el```, ```le```, ```islte```, ```lte```

```html
[[+number lte `3` ? `success` : `fail`]]
```

###In array

Aliases: ```inarray```, ```in_array```, ```in_arr```

```html
[[+number in_array `foo,3,5` ? `success` : `fail`]]
```

String
------------------

###Size

```php
$replace = '[[+foo:size]]';
echo (new Template)->replace($replace, ['foo' => 'test']); // result 4
```

###Truncate

```php
$replace = '[[+foo:truncate&length=`5`]]';
echo (new Template)->replace($replace, ['foo' => 'Hello world']); // result 'Hello...'
```

###TruncateWords

```php
$replace = '[[+foo:truncateWords&length=`6`]]';
echo (new Template)->replace($replace, ['foo' => 'Hello world']); // result 'Hello...'
```

###Lower

```php
$replace = '[[+foo:lower]]';
echo (new Template)->replace($replace, ['foo' => 'Hello World']); // result 'hello world'
```

###Upper

```php
$replace = '[[+foo:upper]]';
echo (new Template)->replace($replace, ['foo' => 'Hello World']); // result 'HELLO WORLD'
```

###UpperFirst

```php
$replace = '[[+foo:upperFirst]]';
echo (new Template)->replace($replace, ['foo' => 'hello world']); // result 'Hello world'
```

###TrimPattern

```php
$replace = '[[+foo:trimPattern&pattern=`/l{2}/`]]';
echo (new Template)->replace($replace, ['foo' => 'hello world']); // result 'weo world'
```

###Contains

> Note: supported ```[[+output]]```

Success:

```php
$replace = '[[+foo:contains&is=`Wo`&then=`[[+foo]]`]]';
echo (new Template)->replace($replace, ['foo' => 'Hello World']); // result 'hello world'
```

Fail:

```php
$replace = '[[+foo:contains&is=`wo`&then=`[[+foo]]`]]';
echo (new Template)->replace($replace, ['foo' => 'Hello World']); // result ''
```

Numeric
---------------

###isParity

```php
$replace = '[[+num:isParity&then=`success`]]';
echo (new Template)->replace($replace, ['foo' => 2]); // result 'success'
```

###Positive

```php
$replace = '[[+num:positive]]';
echo (new Template)->replace($replace, ['foo' => -7]); // result 0
```

Arithmetic operations
-----------------------

```html
[[+num:formula&operator=`*`&operand=`3`]]
```

Sugar:

```html
[[+num * `3`]]
```

Cascade:

```html
[[+num * `3` + `2`]]
```

Supported operations: ```+```, ```-```, ```*```, ```/```, ```**```, ```mod```

Bitwise operations
-----------------------

```html
[[+num |  `8`]]
```

Supported operations: ```|```, ```&```, ```^```, ```<<```, ```>>```


Other
------------

###Url
Alias: ```modifyUrl```

```php
$replace =
    '[[+url:modifyUrl
         &args=`{"page" : 1}`
         &beginPath=`/parts`
         &endPath=`/news/`
         &anchor=`name`
         &const=`32`
    ]]';

echo (new Template)->replace($replace,['url'=> 'http://site.com/categories/?view=all']); // result: http://site.com/parts/categories/news/?page=1#name
```

If url is empty, then get dummy ```#```.

```php
$replace = '[[+url:modifyUrl]]';

echo (new Template)->replace($replace,['url'=> '']); // result: '#'
```

###date
Alias: ```modifyDate```

```php
$replace = '[[+date:modifyDate&format=`dmy`]]';

echo (new Template)->replace($replace,['date'=> '2012-02-12 15:01']); // result: 12 February 2012
```

###toJson
Alias: ```ArrayToJson```

```php
$replace = '[[+array:toJson]]';

echo (new Template)->replace($replace, ['array'=> ['foo' => 'test']]); // result: {"foo" : "test"}
```

###toArray
Aliases: ```jsonToArray```, ```unserialize```

```php
$replace ='[[!+foo:toArray:size]]';

echo (new Template)->replace($replace, ['foo' => '{"bar" : {"subbar" : "test"}}']); // result: 1
```

###replaceTpl

```php
$template = new Template;
$template->addPlaceholder('title', 'hello', true);

echo $template->replace('[[!+content:replaceTpl]]', ['content' => '<b>[[++title]]</b>']); // result: <b>hello</b>
```

###php-function

```php
$replace ='[[+foo:substr&start=`6`&length=`2`:strlen]]';

echo (new Template)->replace($replace, ['title'=> 'hello world']); // result: 2
```

Custom Filter
------------------

Add to existing filters.
> Note: the method must be static

```php
use rock\template\ClassName;

class CustomFilter
{
    use className;

    public static function customFilter($value, array $params, Template $template)
    {
        return mb_strlen($value, 'UTF-8');
    }
}

$config = [
    'filters' => [
        'customFilter' => [
            'class' => CustomFilter::className(),
        ],
    ]
];
$template = new Template($config);
```

if you want to specify the alias:

```php
$config = [
    'filters' => [
        'customFilter' => [
            'class' => CustomFilter::className(),
        ],
        'custom' => [
           'class' => CustomFilter::className(),
            'method'=> 'customFilter'
        ],
    ]
];
```

Now to the filter can be accessed by two names: ```:customFilter``` and ```:custom```