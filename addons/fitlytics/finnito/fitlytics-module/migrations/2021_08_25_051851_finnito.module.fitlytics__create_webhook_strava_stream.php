<?php

use Anomaly\Streams\Platform\Database\Migration\Migration;

class FinnitoModuleFitlyticsCreateWebhookStravaStream extends Migration
{

    /**
     * This migration creates the stream.
     * It should be deleted on rollback.
     *
     * @var bool
     */
    protected $delete = true;

    protected $fields = [
        "content"   => 'visiosoft.field_type.json',
        "processed" => "anomaly.field_type.boolean",
    ];

    /**
     * The stream definition.
     *
     * @var array
     */
    protected $stream = [
        'slug'          => 'webhook_strava',
        'title_column'  => 'id',
        'translatable'  => false,
        'versionable'   => false,
        'trashable'     => false,
        'searchable'    => false,
        'sortable'      => false,
    ];

    /**
     * The stream assignments.
     *
     * @var array
     */
    protected $assignments = [
        "content",
        "processed",
    ];
}
