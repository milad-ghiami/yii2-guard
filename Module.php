<?php

namespace wdmg\guard;

/**
 * Yii2 Guard
 *
 * @category        Module
 * @version         1.0.2
 * @author          Alexsander Vyshnyvetskyy <alex.vyshnyvetskyy@gmail.com>
 * @link            https://github.com/wdmg/yii2-guard
 * @copyright       Copyright (c) 2019 - 2020 W.D.M.Group, Ukraine
 * @license         https://opensource.org/licenses/MIT Massachusetts Institute of Technology (MIT) License
 *
 */

use Yii;
use wdmg\base\BaseModule;
use wdmg\guard\filters\RateLimit;
use wdmg\guard\behaviors\RequestBehavior;
use wdmg\guard\models\Security;
use yii\helpers\ArrayHelper;

/**
 * Guard module definition class
 */
class Module extends BaseModule
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'wdmg\guard\controllers';

    /**
     * {@inheritdoc}
     */
    public $defaultRoute = "security/index";

    /**
     * @var string, the name of module
     */
    public $name = "Guard";

    /**
     * @var string, the description of module
     */
    public $description = "Security System";

    /**
     * @var string the module version
     */
    private $version = "1.0.2";

    /**
     * @var integer, priority of initialization
     */
    private $priority = 1;

    /**
     * @var bool, check password recency
     */
    public $passwordRecency = true;

    /**
     * @var bool, flag for use filters
     */
    public $useFilters = true;

    /**
     * @var array, flag for use request filters
     */
    public $filters = [
        'xss' => false, // flag for scan for XSS attack`s
        'lfi' => true, // flag for scan for LFI/RFI/RCE attack`s
        'php' => true, // flag for scan for PHP-injection attack`s
        'sql' => true // flag for scan for SQL-injection attack`s
    ];

    /**
     * @var array, security filters (regexp patterns)
     */
    public $patterns = [
        'xss' => '/(<.*?(script|body|object|iframe|applet|meta|style|form|frameset|frame|svg).*?>)|(base64|data\\:|fromCharCode|expression|onmouse|onload|alert|getcookie|document\\.)/uim',
        'lfi' => '/((\\.|%2e){2,}(\\/|%5c|\\\\)|php:\\/\\/|file:\\/\\/|expect:\\/\\/|zip:\\/\\/|yii\\.php|init\\.php|web\\.php|params\\.php|db\\.php|console\\.php|test\\.php|test_db\\.php|phpinfo|passwd|htaccess)/uism',
        'php' => '/(php:\\/\\/|(eval|preg_replace|require|include|call_user|create_func|array_filter|array_reduce|array_walk|array_map|reflection)\\()/uism',
        'sql' => '/(UNION|SELECT|OUTFILE|ALTER|INSERT|DROP|TRUNCATE|({%tables}))\\s/uism'
    ];

    /**
     * @var bool, flag for use requests limitation
     */
    public $useRateLimit = true;

    /**
     * @var integer, request limit`s per minute
     */
    public $rateLimit = 60;

    /**
     * @var array, ignoring by IP
     */
    public $rateLimitIgnoringIP = [
        '::1',
        '127.0.0.1',
    ];

    /**
     * @var array, ignoring by request route
     */
    public $rateLimitIgnoringRoutes = [
        '/admin'
    ];

    /**
     * @var array, exception from ignoring by request route
     */
    public $rateLimitExceptionRoutes = [
        '/admin/login',
        '/admin/restore',
    ];

    /**
     * @var array, ignoring by request type
     */
    public $rateLimitIgnoringRequests = [
        'post' => false,
        'get' => false,
        'ajax' => true
    ];

    /**
     * @var string, request limit error message
     */
    public $rateLimitErrorMessage = 'Your request limit has been exceeded! Try later.';

    /**
     * @var bool, flag for use overdrive limitation
     */
    public $useOverdriveLimit = true;

    /**
     * @var bool, limit for $_POST and $_GET data overdrive
     */
    public $overdriveLimit = [
        'post' => 200,
        'get' => 100
    ];

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // Set version of current module
        $this->setVersion($this->version);

        // Set priority of current module
        $this->setPriority($this->priority);

        // Configure module from app params
        if (isset(Yii::$app->params['guard.passwordRecency']))
            $this->passwordRecency = Yii::$app->params['guard.passwordRecency'];

        if (isset(Yii::$app->params['guard.useFilters']))
            $this->useFilters = Yii::$app->params['guard.useFilters'];

        if (isset(Yii::$app->params['guard.filters']))
            $this->filters = Yii::$app->params['guard.filters'];

        if (isset(Yii::$app->params['guard.patterns']))
            $this->patterns = Yii::$app->params['guard.patterns'];

        if (isset(Yii::$app->params['guard.useRateLimit']))
            $this->useRateLimit = Yii::$app->params['guard.useRateLimit'];

        if (isset(Yii::$app->params['guard.rateLimit']))
            $this->rateLimit = Yii::$app->params['guard.rateLimit'];

        if (isset(Yii::$app->params['guard.rateLimitIgnoringIP']))
            $this->rateLimitIgnoringIP = Yii::$app->params['guard.rateLimitIgnoringIP'];

        if (isset(Yii::$app->params['guard.rateLimitIgnoringRoutes']))
            $this->rateLimitIgnoringRoutes = Yii::$app->params['guard.rateLimitIgnoringRoutes'];

        if (isset(Yii::$app->params['guard.rateLimitExceptionRoutes']))
            $this->rateLimitExceptionRoutes = Yii::$app->params['guard.rateLimitExceptionRoutes'];

        if (isset(Yii::$app->params['guard.rateLimitIgnoringRequests']))
            $this->rateLimitIgnoringRequests = Yii::$app->params['guard.rateLimitIgnoringRequests'];

        if (isset(Yii::$app->params['guard.rateLimitErrorMessage']))
            $this->rateLimitErrorMessage = Yii::$app->params['guard.rateLimitErrorMessage'];

        if (isset(Yii::$app->params['guard.useOverdriveLimit']))
            $this->useOverdriveLimit = Yii::$app->params['guard.useOverdriveLimit'];

        if (isset(Yii::$app->params['guard.overdriveLimit']))
            $this->overdriveLimit = Yii::$app->params['guard.overdriveLimit'];

    }

    /**
     * {@inheritdoc}
     */
    public function dashboardNavItems($createLink = false)
    {
        $items = [
            'label' => $this->name,
            'url' => [$this->routePrefix . '/'. $this->id],
            'icon' => 'fa fa-fw fa-shield-alt',
            'active' => in_array(\Yii::$app->controller->module->id, [$this->id])
        ];
        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function bootstrap($app)
    {
        parent::bootstrap($app);

        // Add guard behaviors for web app
        if (!($app instanceof \yii\console\Application) && $this->module) {

            // Prepare SQL-filter and add DB tables
            if (isset($this->filters['sql']) && ($connection = Yii::$app->getDb())) {
                if (preg_match('{%tables}', $this->filters['sql'])) {
                    $tables = $connection->getSchema()->getTableNames();
                    $no_prefix_tables = str_replace($connection->tablePrefix, '', $tables);
                    $tables = ArrayHelper::merge($tables, $no_prefix_tables);
                    $this->filters['sql'] = str_replace('{%tables}', join('|', $tables), $this->filters['sql']);
                }
            }

            // Request query behavior
            $app->attachBehavior('requestBehavior', [
                'class' => RequestBehavior::class,
                'security' => new Security(),
                'module' => $this
            ]);

            if ($this->useRateLimit && intval($this->rateLimit) > 0) {
                $app->attachBehavior('rateLimit', [
                    'class' => rateLimit::class,
                    'rateLimit' => intval($this->rateLimit),
                    'security' => new Security(),
                    'module' => $this
                ]);
            }


            if (!(Yii::$app->user->isGuest) && ($this->isBackend() && $this->passwordRecency)) {
                \yii\base\Event::on(\yii\base\Controller::class, \yii\base\Controller::EVENT_BEFORE_ACTION, function ($event) {
                    if ($auth = Yii::$app->authManager) {
                        $user = Yii::$app->user->identity;
                        if ($roles = $auth->getRolesByUser($user->getId())) {
                            if (in_array('admin', array_keys($roles))) {
                                if (strtotime($user->updated_at) <= strtotime("- 14 day")) {
                                    Yii::$app->getSession()->setFlash(
                                        'danger',
                                        Yii::t(
                                            'app/modules/guard',
                                            'It seems that you have not changed your access password for a long time. We recommend that you, periodically, change the password for access to the administrative interface of the site.'
                                        )
                                    );
                                }
                            }
                        }
                    }
                });
            }

        }
    }
}