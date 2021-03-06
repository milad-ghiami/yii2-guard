[![Yii2](https://img.shields.io/badge/required-Yii2_v2.0.33-blue.svg)](https://packagist.org/packages/yiisoft/yii2)
[![Downloads](https://img.shields.io/packagist/dt/wdmg/yii2-guard.svg)](https://packagist.org/packages/wdmg/yii2-guard)
[![Packagist Version](https://img.shields.io/packagist/v/wdmg/yii2-guard.svg)](https://packagist.org/packages/wdmg/yii2-guard)
![Progress](https://img.shields.io/badge/progress-in_development-red.svg)
[![GitHub license](https://img.shields.io/github/license/wdmg/yii2-guard.svg)](https://github.com/wdmg/yii2-guard/blob/master/LICENSE)

# Yii2 guard
Security System for Yii2

# Requirements 
* PHP 5.6 or higher
* Yii2 v.2.0.33 and newest
* [Yii2 Base](https://github.com/wdmg/yii2-base) module (required)

# Installation
To install the module, run the following command in the console:

`$ composer require "wdmg/yii2-guard"`

# Configure
To add a module to the project, add the following data in your configuration file:

    'modules' => [
        ...
        'guard' => [
            'class' => 'wdmg\guard\Module',
            'routePrefix' => 'admin'
            'useFilters': true, // flag for use filters
            'filters': { // flag for use request filters
                'xss': true,
                'lfi': true,
                'php': true,
                'sql': true
            },
            'patterns': { // security filters (regexp patterns)
                'xss': '/(<.*?(script|body|object|iframe|applet|meta|style|form|frameset|frame|svg).*?>)|(base64|data\\:|fromCharCode|expression|onmouse|onload|alert|getcookie|document\\.)/uim',
                'lfi': '/((\\.|%2e){2,}(\\/|%5c|\\\\)|php:\\/\\/|file:\\/\\/|expect:\\/\\/|zip:\\/\\/|yii\\.php|init\\.php|web\\.php|params\\.php|db\\.php|console\\.php|test\\.php|test_db\\.php|phpinfo|passwd|htaccess)/uism',
                'php': '/(php:\\/\\/|(eval|preg_replace|require|include|call_user|create_func|array_filter|array_reduce|array_walk|array_map|reflection)\\()/uism',
                'sql': '/(UNION|SELECT|OUTFILE|ALTER|INSERT|DROP|TRUNCATE|({%tables}))\\s/uism'
            },
            'useRateLimit': true, // flag for use requests limitation
            'rateLimit': 60, // request limit`s per minute
            'rateLimitIgnoringIP': [ // ignoring by IP
                '::1',
                '127.0.0.1',
            ],
            'rateLimitIgnoringRoutes': [ // ignoring by request route
                '/admin'
            ],
            'rateLimitExceptionRoutes': [ // exception from ignoring by request route
                '/admin/login'
                '/admin/restore'
            ],
            'rateLimitIgnoringRequests': [ // ignoring by request type
                'post': false,
                'get': false,
                'ajax': true
            ],
            'rateLimitErrorMessage': 'Your request limit has been exceeded! Try later.', // request limit error message
            'useOverdriveLimit': true, // flag for use overdrive limitation
            'overdriveLimit': { // limit for $_POST and $_GET data overdrive
                'post': 200,
                'get': 100
            }
        ],
        ...
    ],


# Routing
Use the `Module::dashboardNavItems()` method of the module to generate a navigation items list for NavBar, like this:

    <?php
        echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
            'label' => 'Modules',
            'items' => [
                Yii::$app->getModule('guard')->dashboardNavItems(),
                ...
            ]
        ]);
    ?>

# Status and version [in progress development]
* v.1.0.2 - Up to date dependencies
* v.1.0.1 - Fixed deprecated class declaration
* v.1.0.0 - Added base module and model, behaviors and filters