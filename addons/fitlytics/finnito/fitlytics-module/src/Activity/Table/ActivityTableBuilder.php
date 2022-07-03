<?php namespace Finnito\FitlyticsModule\Activity\Table;

use Anomaly\Streams\Platform\Ui\Table\TableBuilder;

class ActivityTableBuilder extends TableBuilder
{
    protected $sortable = true;

    /**
     * The table views.
     *
     * @var array|string
     */
    protected $views = [];

    /**
     * The table filters.
     *
     * @var array|string
     */
    protected $filters = [
        "distance",
    ];

    /**
     * The table columns.
     *
     * @var array|string
     */
    protected $columns = [
        "type" => [
            "wrapper" => "{value.type}",
            "value" => [
                "type" => "entry.activityTypeEmoji()",
            ],
        ],
        "date" => [
            "value" => "entry.start_date|date('D jS F, g:ia', 'Pacific/Auckland')",
        ],
        "name" => [
            "value" => "entry.name",
        ],
        "distance" => [
            "wrapper" => "{value.distance}km",
            "value" => [
                "distance" => "entry.metersToKilometers(entry.distance,2)",
            ],
        ],
        "duration" => [
            "wrapper" => "{value.moving_time}<br/>{value.elapsed_time}",
            "value" => [
                "moving_time" => "entry.secondsToHours(entry.moving_time)",
                "elapsed_time" => "entry.secondsToHours(entry.elapsed_time)"
            ],
        ],
        "elevation" => [
            "wrapper" => "{value.elevation}m",
            "value" => [
                "elevation" => "entry.total_elevation_gain",
            ],
        ],

    ];

    /**
     * The table buttons.
     *
     * @var array|string
     */
    protected $buttons = [
        'edit'
    ];

    /**
     * The table actions.
     *
     * @var array|string
     */
    protected $actions = [
        'delete'
    ];

    /**
     * The table options.
     *
     * @var array
     */
    protected $options = [
        "order_by" => [
            "activity_json->start_date_local" => "desc"
        ]
    ];

    /**
     * The table assets.
     *
     * @var array
     */
    protected $assets = [];

}
