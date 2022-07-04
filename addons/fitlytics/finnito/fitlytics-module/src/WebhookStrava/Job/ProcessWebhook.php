<?php namespace Finnito\FitlyticsModule\WebhookStrava\Job;

// use Finnito\FitlyticsModule\WebhookStrava\WebhookStravaModel;
use Finnito\FitlyticsModule\Activity\ActivityModel;
use Illuminate\Support\Facades\Log;
use Finnito\FitlyticsModule\Strava\Strava;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class ProcessWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $event;

    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware()
    {
        return [new WithoutOverlapping($this->event->id)];
    }

    public function __construct($event)
    {
        $this->event = $event;
    }

    public function handle()
    {
        /**
         * Handle Athlete Updates
         **/
        if ($this->event->content()->object_type == "athlete") {
            Log::debug("Parsing webhook ATHLETE event");
            // Handle
        }

        /**
         * Handle Activity Updates
         **/
        else {
            if ($this->event->content()->aspect_type == "create") {
                Log::debug("Parsing webhook CREATE event");
                $strava = new Strava();

                $exists = ActivityModel::where("strava_id", $this->event->content()->object_id)
                    ->where("activity_json->athlete->id", $this->event->content()->owner_id)
                    ->exists();

                if ($exists) {
                    return true;
                }

                $activityData = $strava->call("/activities/{$this->event->content()->object_id}");
                $activity = ActivityModel::create([
                    "strava_id" => $activityData->id,
                    "name" => utf8_encode($activityData->name),
                    "distance" => $activityData->distance,
                    "elapsed_time" => $activityData->elapsed_time,
                    "moving_time" => $activityData->moving_time,
                    "total_elevation_gain" => $activityData->total_elevation_gain,
                    "type" => $activityData->type,
                    "start_date" => $activityData->start_date,
                    "activity_json" => json_encode($activityData),
                ]);

                $response = $strava->call(
                    "/activities/{$activity->strava_id}/streams",
                    [
                        "key_by_type" => "true",
                        "keys" => "altitude,cadence,heartrate",
                    ]
                );
                $activity->data_streams = json_encode($response);
                $activity->hrBuckets();
                $activity->save();
                
                $this->event->processed = true;
                $this->event->save();
            }

            else if ($this->event->content()->aspect_type == "update") {
                Log::debug("Parsing webhook UPDATE event");
                $activity = ActivityModel::where("strava_id", $this->event->content()->object_id)
                    ->where("activity_json->athlete->id", $this->event->content()->owner_id)
                    ->first();

                if (!$activity) {
                    Log::debug("?Activity did not exist");
                    $strava = new Strava();
                    $response = $strava->call("/activities/{$this->event->content()->object_id}");
                    ActivityModel::create([
                        "strava_id" => $response->id,
                        "name" => utf8_encode($response->name),
                        "distance" => $response->distance,
                        "elapsed_time" => $response->elapsed_time,
                        "moving_time" => $response->moving_time,
                        "total_elevation_gain" => $response->total_elevation_gain,
                        "type" => $response->type,
                        "start_date" => $response->start_date,
                        "activity_json" => json_encode($response),
                    ]);
                }

                Log::debug("Updating activity: {$activity->name}");

                if (property_exists($this->event->content()->updates, "title")) {
                    Log::debug("Updating title: {$activity->name} --> {$this->event->content()->updates->title}");
                    $activity->name = $this->event->content()->updates->title;

                    // Update the activity_json column too.
                    $activity_json = $activity->activity_json();
                    $activity_json->name = $this->event->content()->updates->title;
                    $activity->activity_json = json_encode($activity_json);
                }

                if (property_exists($this->event->content()->updates, "type")) {
                    Log::debug("Updating type: {$activity->type} --> {$this->event->content()->updates->type}");
                    $activity->type = $this->event->content()->updates->type;

                    // Update the activity_json column too.
                    $activity_json = $activity->activity_json();
                    $activity_json->type = $this->event->content()->updates->type;
                    $activity->activity_json = json_encode($activity_json);
                }

                $activity->save();
                $this->event->processed = true;
                $this->event->save();
            }

            else if ($this->event->content()->aspect_type == "delete") {
                Log::debug("Parsing webhook DELETE event");
                $activity = ActivityModel::where("strava_id", $this->event->content()->object_id)
                    ->where("activity_json->athlete->id", $this->event->content()->owner_id)
                    ->delete();
                $this->event->processed = true;
                $this->event->save();
            }

            else {
                Log::debug("Unknown webhook event->aspect_type: {$this->event->aspect_type}");
            }
        }
    }
}
