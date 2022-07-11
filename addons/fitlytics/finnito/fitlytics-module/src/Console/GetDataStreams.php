<?php namespace Finnito\FitlyticsModule\Console;

use Illuminate\Console\Command;
use Finnito\FitlyticsModule\Strava\Strava;
use Finnito\FitlyticsModule\Activity\ActivityModel;

class GetDataStreams extends Command
{
    protected $name = "strava:data_streams";
    protected $description = "Downloads missing data streams.";

    public function handle()
    {
        $activities = ActivityModel::where("data_streams->latlng", null)
            ->limit(550)
            ->get();

        echo $activities->count() . " activities without their data streams.\n";

        $strava = new Strava();

        foreach ($activities as $activity) {
            echo "Processing " . $activity->name . " now.\n";
            $response = $strava->call(
                "/activities/{$activity->strava_id}/streams",
                [
                    "key_by_type" => "true",
                    "keys" => "altitude,cadence,heartrate,time,latlng,velocity_smooth,watts,grade_smooth",
                ]
            );
            $activity->data_streams = json_encode($response);
            $activity->save();
        }
    }
}
