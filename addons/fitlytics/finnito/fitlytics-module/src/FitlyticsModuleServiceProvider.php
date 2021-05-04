<?php namespace Finnito\FitlyticsModule;

use Anomaly\Streams\Platform\Addon\AddonServiceProvider;
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
    protected $commands = [];

    /**
     * The addon's scheduled commands.
     *
     * @type array|null
     */
    protected $schedules = [];

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
        'admin/fitlytics/notes'           => 'Finnito\FitlyticsModule\Http\Controller\Admin\NotesController@index',
        'admin/fitlytics/notes/create'    => 'Finnito\FitlyticsModule\Http\Controller\Admin\NotesController@create',
        'admin/fitlytics/notes/edit/{id}' => 'Finnito\FitlyticsModule\Http\Controller\Admin\NotesController@edit',
        'admin/fitlytics/plans'           => 'Finnito\FitlyticsModule\Http\Controller\Admin\PlansController@index',
        'admin/fitlytics/plans/create'    => 'Finnito\FitlyticsModule\Http\Controller\Admin\PlansController@create',
        'admin/fitlytics/plans/edit/{id}' => 'Finnito\FitlyticsModule\Http\Controller\Admin\PlansController@edit',
        'admin/fitlytics'           => 'Finnito\FitlyticsModule\Http\Controller\Admin\ActivitiesController@index',
        'admin/fitlytics/create'    => 'Finnito\FitlyticsModule\Http\Controller\Admin\ActivitiesController@create',
        'admin/fitlytics/edit/{id}' => 'Finnito\FitlyticsModule\Http\Controller\Admin\ActivitiesController@edit',
        "" => [
            "uses" => "Finnito\FitlyticsModule\Http\Controller\FitlyticsController@home",
            "streams::addon" => "finnito.module.fitlytics",
            "verb" => "get",
            "as" => "home",
            // "middleware" => [
            //     \Finnito\FitlyticsModule\Http\Middleware\AuthMiddleware::class
            // ]
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
        ],
        "notes-calendar-feed" => [
            "uses" => "Finnito\FitlyticsModule\Http\Controller\FitlyticsController@notesCalendarFeed",
            "streams::addon" => "finnito.module.fitlytics",
            "verb" => "get",
            "as" => "notesCalendarFeed",
        ],
        "plans-calendar-feed" => [
            "uses" => "Finnito\FitlyticsModule\Http\Controller\FitlyticsController@plansCalendarFeed",
            "streams::addon" => "finnito.module.fitlytics",
            "verb" => "get",
            "as" => "plansCalendarFeed",
        ],
        "weekly-summary-feed" => [
            "uses" => "Finnito\FitlyticsModule\Http\Controller\FitlyticsController@weeklySummaryFeed",
            "streams::addon" => "finnito.module.fitlytics",
            "verb" => "get",
            "as" => "weeklySummaryFeed",
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
        FitlyticsNotesEntryModel::class => NoteModel::class,
        FitlyticsPlansEntryModel::class => PlanModel::class,
        FitlyticsActivitiesEntryModel::class => ActivityModel::class,
        "note_form" => \Finnito\FitlyticsModule\Note\Form\NoteFormBuilder::class,
        "plan_form" => \Finnito\FitlyticsModule\Plan\Form\PlanFormBuilder::class,
    ];

    /**
     * The addon singleton bindings.
     *
     * @type array|null
     */
    protected $singletons = [
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
