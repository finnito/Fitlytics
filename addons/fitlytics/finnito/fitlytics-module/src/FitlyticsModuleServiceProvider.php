<?php namespace Finnito\FitlyticsModule;

use Anomaly\Streams\Platform\Addon\AddonServiceProvider;
use Finnito\FitlyticsModule\WebhookStrava\Contract\WebhookStravaRepositoryInterface;
use Finnito\FitlyticsModule\WebhookStrava\WebhookStravaRepository;
use Anomaly\Streams\Platform\Model\Fitlytics\FitlyticsWebhookStravaEntryModel;
use Finnito\FitlyticsModule\WebhookStrava\WebhookStravaModel;
use Finnito\FitlyticsModule\StravaCredential\Contract\StravaCredentialRepositoryInterface;
use Finnito\FitlyticsModule\StravaCredential\StravaCredentialRepository;
use Anomaly\Streams\Platform\Model\Fitlytics\FitlyticsStravaCredentialsEntryModel;
use Finnito\FitlyticsModule\StravaCredential\StravaCredentialModel;
use Finnito\FitlyticsModule\Note\Contract\NoteRepositoryInterface;
use Finnito\FitlyticsModule\Note\NoteRepository;
use Anomaly\Streams\Platform\Model\Fitlytics\FitlyticsNotesEntryModel;
use Finnito\FitlyticsModule\Note\NoteModel;
use Finnito\FitlyticsModule\Plan\Contract\PlanRepositoryInterface;
use Finnito\FitlyticsModule\Plan\PlanRepository;
use Anomaly\Streams\Platform\Model\Fitlytics\FitlyticsPlansEntryModel;
use Finnito\FitlyticsModule\Plan\PlanModel;
use Finnito\FitlyticsModule\Activity\Contract\ActivityRepositoryInterface;
use Finnito\FitlyticsModule\Activity\ActivityRepository;
use Anomaly\Streams\Platform\Model\Fitlytics\FitlyticsActivitiesEntryModel;
use Finnito\FitlyticsModule\Activity\ActivityModel;
use Illuminate\Routing\Router;

class FitlyticsModuleServiceProvider extends AddonServiceProvider
{

    /**
     * Additional addon plugins.
     *
     * @type array|null
     */
    protected $plugins = [];

    /**
     * The addon Artisan commands.
     *
     * @type array|null
     */
    protected $commands = [
        "strava:get" => \Finnito\FitlyticsModule\Console\GetNewActivities::class,
        "strava:gpx" => \Finnito\FitlyticsModule\Console\DownloadGPXFiles::class,
    ];

    /**
     * The addon's scheduled commands.
     *
     * @type array|null
     */
    protected $schedules = [
        "* * * * *" => [
            \Finnito\FitlyticsModule\Console\GetNewActivities::class,
            // \Finnito\FitlyticsModule\Console\DownloadGPXFiles::class,
        ],
    ];

    /**
     * The addon API routes.
     *
     * @type array|null
     */
    protected $api = [];

    /**
     * The addon routes.
     *
     * @type array|null
     */
    protected $routes = [
        'admin/fitlytics/webhook_strava'           => 'Finnito\FitlyticsModule\Http\Controller\Admin\WebhookStravaController@index',
        'admin/fitlytics/webhook_strava/create'    => 'Finnito\FitlyticsModule\Http\Controller\Admin\WebhookStravaController@create',
        'admin/fitlytics/webhook_strava/edit/{id}' => 'Finnito\FitlyticsModule\Http\Controller\Admin\WebhookStravaController@edit',
        'admin/fitlytics/strava_credentials'           => 'Finnito\FitlyticsModule\Http\Controller\Admin\StravaCredentialsController@index',
        'admin/fitlytics/strava_credentials/create'    => 'Finnito\FitlyticsModule\Http\Controller\Admin\StravaCredentialsController@create',
        'admin/fitlytics/strava_credentials/edit/{id}' => 'Finnito\FitlyticsModule\Http\Controller\Admin\StravaCredentialsController@edit',
        'admin/fitlytics/notes'           => 'Finnito\FitlyticsModule\Http\Controller\Admin\NotesController@index',
        'admin/fitlytics/notes/create'    => 'Finnito\FitlyticsModule\Http\Controller\Admin\NotesController@create',
        'admin/fitlytics/notes/edit/{id}' => 'Finnito\FitlyticsModule\Http\Controller\Admin\NotesController@edit',
        'admin/fitlytics/plans'           => 'Finnito\FitlyticsModule\Http\Controller\Admin\PlansController@index',
        'admin/fitlytics/plans/create'    => 'Finnito\FitlyticsModule\Http\Controller\Admin\PlansController@create',
        'admin/fitlytics/plans/edit/{id}' => 'Finnito\FitlyticsModule\Http\Controller\Admin\PlansController@edit',
        'admin/fitlytics'           => 'Finnito\FitlyticsModule\Http\Controller\Admin\ActivitiesController@index',
        'admin/fitlytics/create'    => 'Finnito\FitlyticsModule\Http\Controller\Admin\ActivitiesController@create',
        'admin/fitlytics/edit/{id}' => 'Finnito\FitlyticsModule\Http\Controller\Admin\ActivitiesController@edit',
        "settings" => [
            "uses" => "Finnito\FitlyticsModule\Http\Controller\SettingsController@index",
            "streams::addon" => "finnito.module.fitlytics",
            "verb" => "get",
            "middleware" => [
                \Finnito\FitlyticsModule\Http\Middleware\AuthMiddleware::class
            ],
        ],
        "activities.ics" => [
            "uses" => "Finnito\FitlyticsModule\Http\Controller\FitlyticsController@activitiesICS",
            "streams::addon" => "finnito.module.fitlytics",
            "verb" => "get",
            "as" => "activitiesICS",
        ],
        "plans.ics" => [
            "uses" => "Finnito\FitlyticsModule\Http\Controller\FitlyticsController@plansICS",
            "streams::addon" => "finnito.module.fitlytics",
            "verb" => "get",
            "as" => "plansICS",
        ],
        "notes.ics" => [
            "uses" => "Finnito\FitlyticsModule\Http\Controller\FitlyticsController@notesICS",
            "streams::addon" => "finnito.module.fitlytics",
            "verb" => "get",
            "as" => "notesICS",
        ],
        "activities-calendar-feed" => [
            "uses" => "Finnito\FitlyticsModule\Http\Controller\FitlyticsController@activitiesCalendarFeed",
            "streams::addon" => "finnito.module.fitlytics",
            "verb" => "get",
            "as" => "activitiesCalendarFeed",
            "middleware" => [
                \Finnito\FitlyticsModule\Http\Middleware\AuthMiddleware::class
            ],
        ],
        "notes-calendar-feed" => [
            "uses" => "Finnito\FitlyticsModule\Http\Controller\FitlyticsController@notesCalendarFeed",
            "streams::addon" => "finnito.module.fitlytics",
            "verb" => "get",
            "as" => "notesCalendarFeed",
            "middleware" => [
                \Finnito\FitlyticsModule\Http\Middleware\AuthMiddleware::class
            ],
        ],
        "plans-calendar-feed" => [
            "uses" => "Finnito\FitlyticsModule\Http\Controller\FitlyticsController@plansCalendarFeed",
            "streams::addon" => "finnito.module.fitlytics",
            "verb" => "get",
            "as" => "plansCalendarFeed",
            "middleware" => [
                \Finnito\FitlyticsModule\Http\Middleware\AuthMiddleware::class
            ],
        ],
        "weekly-summary-feed" => [
            "uses" => "Finnito\FitlyticsModule\Http\Controller\FitlyticsController@weeklySummaryFeed",
            "streams::addon" => "finnito.module.fitlytics",
            "verb" => "get",
            "as" => "weeklySummaryFeed",
            "middleware" => [
                \Finnito\FitlyticsModule\Http\Middleware\AuthMiddleware::class
            ],
        ],
        "strava" => [
            "uses" => "Finnito\FitlyticsModule\Http\Controller\StravaController@connections",
            "streams::addon" => "finnito.module.fitlytics",
            "verb" => "get",
            "middleware" => [
                \Finnito\FitlyticsModule\Http\Middleware\AuthMiddleware::class
            ],
        ],
        "strava/create-subscription" => [
            "uses" => "Finnito\FitlyticsModule\Http\Controller\StravaController@create_subscription",
            "streams::addon" => "finnito.module.fitlytics",
            "verb" => "get",
            "middleware" => [
                \Finnito\FitlyticsModule\Http\Middleware\AuthMiddleware::class
            ],
        ],
        "authorization-code/callback" => [
            "uses" => "Finnito\FitlyticsModule\Http\Controller\StravaController@callback",
            "streams::addon" => "finnito.module.fitlytics",
            "verb" => "get",
            "middleware" => [
                \Finnito\FitlyticsModule\Http\Middleware\AuthMiddleware::class
            ],
        ],
        "weekly-summary-chart-data" => [
            "uses" => "Finnito\FitlyticsModule\Http\Controller\FitlyticsController@weeklySummaryChartData",
            "streams::addon" => "finnito.module.fitlytics",
            "verb" => "get",
            "middleware" => [
                \Finnito\FitlyticsModule\Http\Middleware\AuthMiddleware::class
            ],
        ],
        "weekly-run-data" => [
            "uses" => "Finnito\FitlyticsModule\Http\Controller\FitlyticsController@weeklyRunData",
            "streams::addon" => "finnito.module.fitlytics",
            "verb" => "get",
            "middleware" => [
                \Finnito\FitlyticsModule\Http\Middleware\AuthMiddleware::class
            ],
        ],

        "/api/currentWeekChart" => [
            "uses" => "Finnito\FitlyticsModule\Http\Controller\APIController@currentWeekChart",
            "middleware" => [
                \Finnito\FitlyticsModule\Http\Middleware\AuthMiddleware::class
            ],
        ],

        "/api/webhook/strava" => \Finnito\FitlyticsModule\Http\Controller\Webhook\Strava::class,

        "{week?}" => [
            "uses" => "Finnito\FitlyticsModule\Http\Controller\FitlyticsController@home",
            "streams::addon" => "finnito.module.fitlytics",
            "verb" => "get",
            "as" => "home",
            "middleware" => [
                \Finnito\FitlyticsModule\Http\Middleware\AuthMiddleware::class
            ],
        ],
    ];

    /**
     * The addon middleware.
     *
     * @type array|null
     */
    protected $middleware = [];

    /**
     * Addon group middleware.
     *
     * @var array
     */
    protected $groupMiddleware = [
        //'web' => [
        //    Finnito\FitlyticsModule\Http\Middleware\ExampleMiddleware::class,
        //],
    ];

    /**
     * Addon route middleware.
     *
     * @type array|null
     */
    protected $routeMiddleware = [];

    /**
     * The addon event listeners.
     *
     * @type array|null
     */
    protected $listeners = [
        //Finnito\FitlyticsModule\Event\ExampleEvent::class => [
        //    Finnito\FitlyticsModule\Listener\ExampleListener::class,
        //],
    ];

    /**
     * The addon alias bindings.
     *
     * @type array|null
     */
    protected $aliases = [
        //'Example' => Finnito\FitlyticsModule\Example::class
    ];

    /**
     * The addon class bindings.
     *
     * @type array|null
     */
    protected $bindings = [
        FitlyticsWebhookStravaEntryModel::class => WebhookStravaModel::class,
        FitlyticsStravaCredentialsEntryModel::class => StravaCredentialModel::class,
        FitlyticsNotesEntryModel::class => NoteModel::class,
        FitlyticsPlansEntryModel::class => PlanModel::class,
        FitlyticsActivitiesEntryModel::class => ActivityModel::class,
        "note_form" => \Finnito\FitlyticsModule\Note\Form\NoteFormBuilder::class,
        "plan_form" => \Finnito\FitlyticsModule\Plan\Form\PlanFormBuilder::class,
        "user_form" => \Finnito\FitlyticsModule\User\Form\FitlyticsUserFormBuilder::class,
    ];

    /**
     * The addon singleton bindings.
     *
     * @type array|null
     */
    protected $singletons = [
        WebhookStravaRepositoryInterface::class => WebhookStravaRepository::class,
        StravaCredentialRepositoryInterface::class => StravaCredentialRepository::class,
        NoteRepositoryInterface::class => NoteRepository::class,
        PlanRepositoryInterface::class => PlanRepository::class,
        ActivityRepositoryInterface::class => ActivityRepository::class,
    ];

    /**
     * Additional service providers.
     *
     * @type array|null
     */
    protected $providers = [
        //\ExamplePackage\Provider\ExampleProvider::class
    ];

    /**
     * The addon view overrides.
     *
     * @type array|null
     */
    protected $overrides = [
        //'streams::errors/404' => 'module::errors/404',
        //'streams::errors/500' => 'module::errors/500',
    ];

    /**
     * The addon mobile-only view overrides.
     *
     * @type array|null
     */
    protected $mobile = [
        //'streams::errors/404' => 'module::mobile/errors/404',
        //'streams::errors/500' => 'module::mobile/errors/500',
    ];

    /**
     * Register the addon.
     */
    public function register()
    {
        // Run extra pre-boot registration logic here.
        // Use method injection or commands to bring in services.
    }

    /**
     * Boot the addon.
     */
    public function boot()
    {
        // Run extra post-boot registration logic here.
        // Use method injection or commands to bring in services.
    }

    /**
     * Map additional addon routes.
     *
     * @param Router $router
     */
    public function map(Router $router)
    {
        // Register dynamic routes here for example.
        // Use method injection or commands to bring in services.
    }
}
