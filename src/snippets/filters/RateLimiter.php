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
     * Limit iterations.
     * @var int
     */
    public $defaultLimit = 8;
    /**
     * Delay (second).
     * @var int
     */
    public $defaultPeriod = 60;
    /**
     * List actions.
     * @var array
     */
    public $actions = [];

    /**
     * @var boolean whether to include rate limit headers in the response
     */
    public $sendHeaders = false;

    public $throwException = false;
    /**
     * @var string the message to be displayed when rate limit exceeds
     */
    public $errorMessage = 'Rate limit exceeded.';
    /**
     * The condition which to run the {@see \rock\filters\RateLimiterTrait::saveAllowance()}.
     * @var callable|bool
     */
    public $dependency = true;
    /**
     * Invert checking.
     * @var bool
     */
    public $invert = false;
    /**
     * Hash-key.
     * @var string
     */
    public $name;
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