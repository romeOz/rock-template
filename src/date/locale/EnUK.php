<?php

namespace rock\template\date\locale;

use rock\template\date\Date;

class EnUK extends Locale
{
    protected static $months = [
        'January', 'February', 'March', 'April', 'May', 'June', 'Jule', 'August', 'September', 'October', 'November',
        'December'
    ];
    protected static $shortMonths = [
        'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
    ];
    protected static $weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    protected static $shortWeekDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

    protected static $formats = [
        Date::USER_DATE_FORMAT => 'm/d/Y',
        Date::USER_TIME_FORMAT => 'G:i',
        Date::USER_DATETIME_FORMAT => 'm/d/Y G:i',
    ];
}
