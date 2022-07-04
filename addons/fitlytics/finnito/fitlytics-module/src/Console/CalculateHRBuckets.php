<?php namespace Finnito\FitlyticsModule\Console;

use Illuminate\Console\Command;
use Finnito\FitlyticsModule\Strava\Strava;
use Finnito\FitlyticsModule\Activity\ActivityModel;

class CalculateHRBuckets extends Command
{
    protected $name = "strava:calculate_hr_buckets";
    protected $description = "Calculates missing heart rate buckets.";

    public function handle()
    {
        $activities = ActivityModel::where("hr_buckets", null)
            ->get();
        echo $activities->count() . " activities without HR buckets.\n";

        $strava = new Strava();

        foreach ($activities as $activity) {
            echo "Processing " . $activity->name . "\n";
            
            // Check if data_streams is missing.
            // Request from Strava if they are.
            if (is_null($activity->data_streams)) {
                echo " - Missing data_streams. Requesting from Strava.\n";
                $response = $strava->call(
                    "/activities/{$activity->strava_id}/streams",
                    [
                        "key_by_type" => "true",
                        "keys" => "altitude,cadence,heartrate",
                    ]
                );
                $activity->data_streams = json_encode($response);
                $activity->save();
            }

            // if (!is_null($activity->data_streams)) {
                $activity->hrBuckets();
                // echo " Buckets calculated.\n";
                // $activity->save();
            // } else {
                // echo " No data_streams available for this activity. Skipping.\n";
            // }    
        }
    }
}
