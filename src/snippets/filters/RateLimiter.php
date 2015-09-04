<?php

namespace rock\snippets\filters;

use rock\filters\RateLimiterTrait;

/**
 * RateLimiter implements a rate limiting.
 *
 * You may use RateLimiter by attaching it as a behavior to a controller or module, like the following,
 *
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         'rateLimiter' => [
 *             'class' => RateLimiter::className(),
 *             'limit' => 10,
 *             'period' => 120
 *         ],
 *     ];
 * }
 * ```
 *
 * When the user has exceeded his rate limit, RateLimiter will throw a {@see \rock\filters\RateLimiterException} exception.
 */
class RateLimiter extends SnippetFilter
{
    use RateLimiterTrait;

    /**
     * Count of iteration.
     * @var int
     */
    public $limit = 5;
    /**
     * Period rate limit (second).
     * @var int
     */
    public $period = 180;

    /**
     * @inheritdoc
     */
    public function before()
    {
        return $this->check($this->limit, $this->period, isset($this->name) ? $this->name : get_class($this->owner));
    }
}