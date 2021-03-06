<?php
namespace rock\snippets;

use rock\date\DateTime;

/**
 * Snippet "DateView"
 *
 * Get formatted now date:
 * ```
 * [[date
 *  ?format=`j n`
 * ]]
 * ```
 *
 * With default format:
 *
 * ```
 * [[date
 *  ?date=`2014-02-12 15:01`
 *  ?format=`dmyhm`
 * ]]
 * ```
 */
class DateTimeSnippet extends Snippet
{
    /**
     * Datetime. `now` by default.
     * @var string
     */
    public $date = 'now';
    /**
     * Format of datetime.
     * @var string
     */
    public $format;
    public $timezone;
    public $config = [];

    public function get()
    {
        $dateTime = DateTime::set($this->date, null, $this->config);
        if (isset($this->timezone)) {
            $dateTime->convertTimezone($this->timezone);
        }
        return $dateTime->format($this->format);
    }
}