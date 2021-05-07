<?php

use Anomaly\Streams\Platform\Database\Migration\Migration;

class FinnitoModuleFitlyticsCreateStravaCredentialsStream extends Migration
{

    protected $delete = true;

    protected $stream = [
        'slug' => 'strava_credentials',
    ];

    protected $fields = [
        'access_token' => 'anomaly.field_type.text',
        'refresh_token' => 'anomaly.field_type.text',
        'expires_at' => "anomaly.field_type.text",
        "user_id" => [
            "type" => "anomaly.field_type.integer",
            "config" => [
                "separator" => "",
            ],
        ],
    ];

    protected $assignments = [
        "access_token",
        "refresh_token",
        "expires_at",
        "user_id",
    ];
}
