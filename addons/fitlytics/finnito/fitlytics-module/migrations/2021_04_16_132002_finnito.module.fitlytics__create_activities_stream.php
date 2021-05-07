<?php

use Anomaly\Streams\Platform\Database\Migration\Migration;

class FinnitoModuleFitlyticsCreateActivitiesStream extends Migration
{

    /**
     * This migration creates the stream.
     * It should be deleted on rollback.
     *
     * @var bool
     */
    protected $delete = true;

    /**
     * The stream definition.
     *
     * @var array
     */
    protected $stream = [
        'slug' => 'activities',
        'title_column' => 'strava_id',
        'translatable' => false,
        'versionable' => false,
        'trashable' => false,
        'searchable' => true,
        'sortable' => false,
    ];

    /**
     * The stream assignments.
     *
     * @var array
     */
    protected $assignments = [
        "strava_id",
        "name",
        "distance",
        "elapsed_time",
        "moving_time",
        "total_elevation_gain",
        "type",
        "start_date",
        "polyline",
        "activity_json",
    ];

}
