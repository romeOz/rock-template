IfSnippet
====================

Params
--------------------

###subject

Condition (strip html/php-tags). E.g `:foo > 1 && :foo < 3`

###operands

Compliance of the operand to the placeholder. E.g. `{"foo" : "[[+foo]]"}`

###then

If expression evaluates to `true`, snippet will execute statement, and if it evaluates to `false` - it'll ignore it.

###else

Often you'd want to execute a statement if a certain condition is met, and a different statement if the condition is not met. This is what else is for. else extends an if statement to execute a statement in case the expression in the if statement evaluates to `false`.

> You can not specify.

###execute
Specifies a execute-handler: `\rock\template\execute\CacheExecute` or `\rock\template\execute\EvalExecute.php`. `\rock\template\execute\CacheExecute` by default.
May be an string, callable, or instance. E.g. ``` ?execute=`\rock\template\execute\EvalExecute` ```.

Example
--------------------

```html
[[if
    ?subject=`:foo > 1 && :foo < 3`
    ?operands=`{"foo" : "[[+foo]]"}`
    ?then=`success`
    ?else=`fail`
]]
```