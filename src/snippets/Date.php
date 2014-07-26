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
    public $format = DateTime::DEFAULT_FORMAT;
    public $date = 'now';
    public $timezone;
    public $config = [];

    public function get()
    {
        return (new DateTime($this->date, $this->timezone, $this->config))->format($this->format);
    }
}
