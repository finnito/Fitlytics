<?php

use Anomaly\Streams\Platform\Database\Migration\Migration;

class FinnitoModuleFitlyticsCreateFitlyticsFields extends Migration
{

    /**
     * The addon fields.
     *
     * @var array
     */
    protected $fields = [
        /**
         * Note Fields
         */
        "date" => [
            "type" => "anomaly.field_type.text",
        ],
        "note" => "anomaly.field_type.textarea",
        "sleep_quality" => [
            "type" => "anomaly.field_type.integer",
            "config" => [
                "default_value" => 3,
                "separator" => null,
                "min" => 1,
                "max" => 5,
            ],
        ],
        "stress_level" => [
            "type" => "anomaly.field_type.integer",
            "config" => [
                "default_value" => 3,
                "separator" => null,
                "min" => 1,
                "max" => 5,
            ],
        ],
        "weight" => [
            "type" => "anomaly.field_type.decimal",
            "config" => [
                "separator" => null,
                "decimals" => 1,
            ],
        ],
        "injured" => "anomaly.field_type.boolean",
        "sick" => "anomaly.field_type.boolean",

        /**
         * Activity Fields
         */
        "strava_id" => [
            "type" => "anomaly.field_type.text",
            "config" => [],
        ],
        "name" => [
            "type" => "anomaly.field_type.textarea",
            "config" => []
        ],
        "distance" => [
            "type" => "anomaly.field_type.decimal",
            "config" => [
                "separator" => null,
                "decimals" => 1,
            ]
        ],
        "elapsed_time" => [
            "type" => "anomaly.field_type.integer",
            "config" => [
                "separator" => null,
            ],
        ],
        "moving_time" => [
            "type" => "anomaly.field_type.integer",
            "config" => [
                "separator" => null,
            ],
        ],
        "total_elevation_gain" => [
            "type" => "anomaly.field_type.integer",
            "config" => [
                "separator" => null,
            ],
        ],
        "type" => [
            "type" => "anomaly.field_type.text",
            "config" => []
        ],
        "start_date" => [
            "type" => "anomaly.field_type.text",
            "config" => []
        ],
        "polyline" => [
            "type" => "anomaly.field_type.textarea",
            "config" => []
        ],
        "activity_json" => [
            "type" => "anomaly.field_type.textarea",
            "config" => []
        ],

        /**
         * Plan Fields
         */
        "plan" => [
            "type" => "anomaly.field_type.textarea",
        ],
    ];
}
