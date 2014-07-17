<?php

namespace rock\template\date\locale;

use rock\template\date\Date;

class Ua extends Locale
{
    protected static $months = [
        'січня', 'лютого', 'березня', 'квітня', 'травня', 'червня', 'липня', 'серпня', 'вересня', 'жовтня', 'листопада',
        'грудня'
    ];
    protected static $weekDays = ['понеділок', 'вівторок', 'середа', 'четвер', "п'ятниця", 'субота', 'неділя'];
    protected static $weekDaysShort = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Нд'];

    protected static $formats = array(
        Date::USER_DATE_FORMAT => 'd.m.Y',
        Date::USER_TIME_FORMAT => 'G:i',
        Date::USER_DATETIME_FORMAT => 'd.m.Y G:i',
    );
}
