<?php

namespace rock\template\date\locale;

use rock\template\date\Date;

class Ru extends Locale
{
    protected static $months = [
        'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября',
        'декабря'
    ];
    protected static $shortMonths = [
        'янв', 'фев', 'мар', 'апр', 'май', 'июн', 'июл', 'авг', 'сен', 'окт', 'ноя', 'дек'
    ];
    protected static $weekDays = [
        'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота', 'воскресенье'
    ];
    protected static $weekDaysShort = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];

    protected static $formats = [
        Date::USER_DATE_FORMAT => 'd.m.Y',
        Date::USER_TIME_FORMAT => 'G:i',
        Date::USER_DATETIME_FORMAT => 'd.m.Y G:i',
    ];
}
