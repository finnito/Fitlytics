<?php

use Anomaly\Streams\Platform\Database\Migration\Migration;

class FinnitoModuleFitlyticsEditStravaCredentialsStream extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // $stream = $this->streams()->findBySlugAndNamespace('strava_credentials', 'fitlytics');
        // $stream->setAttribute('sortable', false)->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // $stream = $this->streams()->findBySlugAndNamespace('strava_credentials', 'fitlytics');
        // $stream->setAttribute('sortable', true)->save();
    }
}
