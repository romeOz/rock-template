<?php
use rock\template\execute\CacheExecute;
use rock\template\filters\BaseFilter;
use rock\template\filters\ConditionFilter;
use rock\template\filters\NumericFilter;
use rock\template\filters\StringFilter;
use rock\template\snippets\Formula;
use rock\template\snippets\ForSnippet;
use rock\template\snippets\IfSnippet;
use rock\template\snippets\ListView;
use rock\template\snippets\Pagination;
use rock\template\snippets\Url;

$datetimeConfig = [
    'formats' => [
        'dmy'   => function(\rock\template\date\DateTime $dateTime){
                $nowYear  = date('Y');
                $lastYear = $dateTime->format('Y');

                return $nowYear > $lastYear
                    ? $dateTime->format('j F Y')
                    : $dateTime->format('d F');
            },
        'dmyhm' => function(\rock\template\date\DateTime $dateTime){
                $nowYear  = date('Y');
                $lastYear = $dateTime->format('Y');
                return $nowYear > $lastYear
                    ? $dateTime->format('j F Y H:i')
                    : $dateTime->format('j F H:i');
            },
    ]
];

$execute = function(){
    $execute = new CacheExecute();
    $execute->path = '@runtime/execute';
    return $execute;
};

return [
    'filters' => [
        'size' => [
            'class' => StringFilter::className(),
        ],
        'trimPattern' => [
            'class' => StringFilter::className(),
        ],
        'contains' => [
            'class' => StringFilter::className(),
        ],
        'truncate' => [
            'class' => StringFilter::className(),
        ],
        'truncateWords' => [
            'class' => StringFilter::className(),
        ],
        'upper' => [
            'class' => StringFilter::className(),
        ],
        'lower' => [
            'class' => StringFilter::className(),
        ],
        'upperFirst' => [
            'class' => StringFilter::className(),
        ],
        'encode' => [
            'class' => StringFilter::className(),
        ],
        'decode' => [
            'class' => StringFilter::className(),
        ],
        'isParity' => [
            'class' => NumericFilter::className(),
        ],
        'positive' => [
            'class' => NumericFilter::className(),
        ],
        'formula' => [
            'class' => NumericFilter::className(),
        ],
        'unserialize' => [
            'class' => BaseFilter::className(),
        ],
        'replaceTpl' => [
            'class' => BaseFilter::className(),
        ],
        'modifyDate' => [
            'class' => BaseFilter::className(),
            'config' => $datetimeConfig
        ],
        'date' => [
            'class' => BaseFilter::className(),
            'config' => $datetimeConfig
        ],
        'modifyUrl' => [
            'class' => BaseFilter::className(),
        ],
        'url' => [
            'method' => 'modifyUrl',
            'class' => BaseFilter::className(),
        ],
        'arrayToJson' => [
            'class' => BaseFilter::className(),
        ],
        'toJson' => [
            'method' => 'arrayToJson',
            'class' => BaseFilter::className(),
        ],
        'jsonToArray' => [
            'method' => 'unserialize',
            'class' => BaseFilter::className(),
        ],
        'toArray' => [
            'method' => 'unserialize',
            'class' => BaseFilter::className(),
        ],
        'notEmpty' => [
            'class' => ConditionFilter::className(),
        ],
        'empty' => [
            'method' => '_empty',
            'class' => ConditionFilter::className(),

        ],
        'if' => [
            'method' => '_if',
            'class' => ConditionFilter::className(),
        ],
    ],
    'snippets' => [
        'ListView' => [
            'class'        => ListView::className(),
        ],
        'List' => [
            'class'        => ListView::className(),
        ],
        'Date' => [
            'class'        => \rock\template\snippets\Date::className(),
            'config' => $datetimeConfig
        ],
        'For' => [
            'class'        => ForSnippet::className(),
        ],
        'Formula' => [
            'class'        => Formula::className(),
            'execute' => $execute
        ],
        'If' => [
            'class'        => IfSnippet::className(),
        ],
        'Pagination' => [
            'class'        => Pagination::className(),
        ],
        'Url' => [
            'class'        => Url::className(),
        ],
    ]
];