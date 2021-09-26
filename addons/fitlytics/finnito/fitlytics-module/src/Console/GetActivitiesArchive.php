<?php namespace Finnito\FitlyticsModule\Console;

use Illuminate\Console\Command;
use Finnito\FitlyticsModule\Strava\Strava;
use Finnito\FitlyticsModule\Activity\Contract\ActivityRepositoryInterface;
use Finnito\FitlyticsModule\Activity\ActivityModel;

class GetActivitiesArchive extends Command
{
    protected $name = "strava:getAll";
    protected $description = "All activities from Strava.";
    
    public function handle(ActivityModel $activityModel, ActivityRepositoryInterface $activitiesRepository)
    {
        $strava = new Strava();

        $latestActivityTimestamp = $activitiesRepository->newQuery()
            ->orderBy("start_date", "ASC")
            ->pluck("start_date")
            ->first();

        if (!$latestActivityTimestamp) {
            $latestActivityTimestamp = \Carbon\Carbon::now();
        }
        // $before = \Carbon\Carbon::parse("now")->timezone("Pacific/Auckland");

        // echo "Activities after: " . $latestActivityTimestamp->toISOString() . "\n";
        echo "Activities before: " . $latestActivityTimestamp->toISOString() . " (" . $latestActivityTimestamp->timestamp . ")\n";

        $page = 1;

        $response = $strava->call("/athlete/activities", [
            "before" => $latestActivityTimestamp->timestamp,
            "after" => 0,
            "page" => $page
        ]);

        while (sizeof($response) > 0) {
            foreach ($response as $activity) {
                $exists = $activityModel
                    ->where("strava_id", $activity->id)
                    ->first();

                if (!$exists) {
                    echo "Inserting " . $activity->name . "\n";
                    $activityModel->create([
                        "strava_id" => $activity->id,
                        "name" => utf8_encode($activity->name),
                        "distance" => $activity->distance,
                        "elapsed_time" => $activity->elapsed_time,
                        "moving_time" => $activity->moving_time,
                        "total_elevation_gain" => $activity->total_elevation_gain,
                        "type" => $activity->type,
                        "start_date" => $activity->start_date,
                        "polyline" => $activity->map->summary_polyline,
                        "activity_json" => json_encode($activity),
                    ]);
                }
            }

            $page = $page + 1;
            $response = $strava->call("/athlete/activities", [
                "before" => $latestActivityTimestamp->timestamp,
                "after" => 0,
                "page" => $page
            ]);
        }

        // dd($resp);
    }
}
