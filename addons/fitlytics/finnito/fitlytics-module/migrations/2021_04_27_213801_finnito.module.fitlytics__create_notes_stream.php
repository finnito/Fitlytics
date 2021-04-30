<?php

use Anomaly\Streams\Platform\Database\Migration\Migration;

class FinnitoModuleFitlyticsCreateNotesStream extends Migration
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
        'slug' => 'notes',
        'title_column' => 'date',
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
        "date" => [
            "required" => true,
            "unique" => true,
        ],
        "sleep_quality",
        "stress_level",
        "injured",
        "sick",
        "weight",
        "note",
    ];
}
