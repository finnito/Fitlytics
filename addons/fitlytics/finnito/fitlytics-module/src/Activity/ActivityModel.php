<?php namespace Finnito\FitlyticsModule\Activity;

use Finnito\FitlyticsModule\Activity\Contract\ActivityInterface;
use Anomaly\Streams\Platform\Entry\EntryModel;

class ActivityModel extends EntryModel implements ActivityInterface
{

    protected $table = "fitlytics_activities";

    protected $fields = ["*"];

    protected $casts = [
        "start_date" => "datetime",
        "activity_json" => "json",
    ];

    public function localStartDate()
    {
        return \Carbon\Carbon::parse($this->start_date)->timezone(env("APP_TIMEZONE"));
    }

    public function start_date()
    {
        return $this->start_date->toDateTimeString();
    }

    /**
     * Gets a field and converts it
     * from meters to kilometers,
     * before rounding it to a given
     * accuracy and returning it.
     *
     * @param string $field_slug
     * @param int $accuracy
     * @return @float
     **/
    public function metersToKilometers($value, $accuracy)
    {
        return round($value / 1000, $accuracy);
    }

    /**
     * Takes seconds and converts
     * it to h:m:s before returning
     * it.
     *
     * @param string $field_slug
     * @return string
     **/
    public function secondsToHours($seconds)
    {
        // $seconds = $this->getAttribute($field_slug);
        // if (!isset($seconds)) {
        //     return "";
        // }

        return sprintf(
            '%2d:%02d:%02d',
            ($seconds/3600),
            ($seconds/60%60),
            ($seconds%60)
        );
    }

    public function metersPerSecondToKilometersPerHour($ms)
    {
        return round(($ms * 60 * 60) / 1000, 2);
    }

    public function metersPerSecondToMinPerKilometer($ms)
    {
        $decimalMins = (1000 / $ms) / 60;
        $min = floor($decimalMins);
        $secRemaining = $decimalMins - $min;
        return $min . ":" . round($secRemaining * 60, 0);
    }

    /**
     * Takes a activity type and
     * returns the associated emoji.
     *
     * @param string $field_slug
     * @return string
     **/
    public function activityTypeEmoji()
    {
        $type = $this->getAttribute("type");
        if (!isset($type)) {
            return "";
        }

        $emoji = array(
            "Ride" => "🚴",
            "Run" => "🏃",
            "Swim" => "🏊",
            "Walk" => "👟",
            "Hike" => "🥾",
            "Alpine Ski" => "⛷",
            "Backcountry Ski" => "🎿",
            "Canoe" => "🛶",
            "Crossfit" => "🏋️",
            "E-Bike Ride" => "🚲",
            "Elliptical" => "🚲",
            "Handcycle" => "🚲",
            "Ice Skate" => "⛸",
            "Inline Skate" => "⛸",
            "Kayaking" => "🛶",
            "Kitesurf Session" => "🪁",
            "Nordic Ski" => "🎿",
            "Rock Climb" => "🧗",
            "Roller Ski" => "🛼",
            "Row" => "🚣",
            "Snowboard" => "🏂",
            "Snowshoe" => "❄️",
            "Stair Stepper" => "🪜",
            "Stand Up Paddle" => "",
            "Surf" => "🏄",
            "Virtual Ride" => "🚲",
            "Virtual Run" => "🏃",
            "Weight Training" => "🏋️",
            "Windsurf Session" => "🌊",
            "Wheelchair" => "🧑‍🦽",
            "Workout" => "💪",
            "Yoga" => "🧘",
        );

        if (isset($emoji[$type])) {
            return $emoji[$type];
        } else {
            return "-";
        }
    }

    public function getColour()
    {
        $type = $this->getAttribute("type");
        if (!isset($type)) {
            return "";
        }

        $colours = array(
            "Ride" => "#badc58",
            "Run" => "#f0932b",
            "Swim" => "#dff9fb",
            "Walk" => "#686de0",
            "Hike" => "",
            "Alpine Ski" => "",
            "Backcountry Ski" => "",
            "Canoe" => "",
            "Crossfit" => "",
            "E-Bike Ride" => "",
            "Elliptical" => "",
            "Handcycle" => "",
            "Ice Skate" => "",
            "Inline Skate" => "",
            "Kayak" => "",
            "Kitesurf Session" => "",
            "Nordic Ski" => "",
            "Rock Climb" => "",
            "Roller Ski" => "",
            "Row" => "",
            "Snowboard" => "",
            "Snowshoe" => "❄",
            "Stair Stepper" => "",
            "Stand Up Paddle" => "",
            "Surf" => "",
            "Virtual Ride" => "",
            "Virtual Run" => "",
            "Weight Training" => "",
            "Windsurf Session" => "",
            "Wheelchair" => "",
            "Workout" => "",
            "Yoga" => "",
        );

        if (isset($colours[$type])) {
            return htmlentities($colours[$type]);
        } else {
            return "";
        }
    }

    public function description()
    {
        $start_time = \Carbon\Carbon::parse($this->start_date)->toISOString();
        $activity_end = new \DateTime($this->start_date);
        $activity_end = $activity_end->add(\DateInterval::createFromDateString($this->elapsed_time . " seconds"));
        $activity_end = \Carbon\Carbon::parse($activity_end)->toISOString();

        // dd($this->getAttribute("distance"));
        $dist = $this->metersToKilometers($this->distance, 2);
        // dd($dist);
        $moving_time = $this->secondsToHours($this->moving_time);
        $elapsed_time = $this->secondsToHours($this->elapsed_time);
        $elevation = $this->total_elevation_gain;
        // dd($this->activity_json->average_heartrate);
        $avg_hr = $this->activity_json->average_heartrate;
        $max_hr = $this->activity_json->max_heartrate;

        if ($this->type == "Run") {
            $avg_speed = $this->metersPerSecondToMinPerKilometer($this->activity_json->average_speed);
            $max_speed = $this->metersPerSecondToMinPerKilometer($this->activity_json->max_speed);
            $speed_unit = "min/km";
        } else {
            $avg_speed = $this->metersPerSecondToKilometersPerHour($this->activity_json->average_speed);
            $max_speed = $this->metersPerSecondToKilometersPerHour($this->activity_json->max_speed);
            $speed_unit = "km/hr";
        }
        

        // $avg_cadence = $this->activity_json->average_cadence;

        return "Start: {$start_time}<br>
        End: {$activity_end}<br>
        Distance: {$dist}km<br>
        Time: {$moving_time} (Elapsed: {$elapsed_time})<br>
        Elevation: {$elevation}m<br>
        Heart Rate: {$avg_hr}bpm ($max_hr max)<br>
        Speed: {$avg_speed}{$speed_unit} ($max_speed max)<br>";
        // Cadence: $avg_cadence";
    }
}
