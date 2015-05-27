formula
====================

Params
--------------------

###subject

Subject (strip html/php-tags). E.g `:pageCurrent - 1`

###operands

Compliance of the operand to the placeholder. E.g. `{"pageCurrent" : "[[+pageCurrent]]"}`

###execute
Specifies a execute-handler: `\rock\template\execute\CacheExecute` or `\rock\template\execute\EvalExecute.php`. `\rock\template\execute\CacheExecute` by default.
May be an string, callable, or instance. E.g. ``` ?execute=`\rock\template\execute\EvalExecute` ```.

Example
--------------------

```html
[[formula
    ?subject=`:pageCurrent - 1`
    ?operands=`{"pageCurrent" : "[[+pageCurrent]]"}`
]]
```