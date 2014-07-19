<?php
namespace rock\template\snippets;

/**
 * Snippet "DateView"
 *
 * Get formatted now date:
 * ```
 * [[Date
 *  ?format=`j n`
 * ]]
 * ```
 *
 * With default format:
 *
 * ```
 * [[Date
 *  ?date=`2014-02-12 15:01`
 *  ?format=`dmyhm`
 * ]]
 * ```
 */
use rock\template\date\DateTime;
use rock\template\date\DateTimeInterface;
use rock\template\Snippet;

/** @noinspection PhpHierarchyChecksInspection */
class Date extends Snippet implements DateTimeInterface
{
    /**
     * format of date
     * @var string
     */
    public $format;
    public $date = 'now';
    public $timezone;

    /** @var DateTime */
    public $datetime;

    public function init()
    {
        parent::init();
        if (!isset($this->datetime)) {
            $this->datetime = new DateTime();
        } elseif($this->datetime instanceof \Closure) {
            $this->datetime = call_user_func($this->datetime);
        }
    }

    public function get()
    {
        if (empty($this->format)) {
            $this->format = self::ISO_DATETIME_FORMAT;
        }

        return $this->datetime->set($this->date, $this->timezone)->format($this->format);
    }
}
