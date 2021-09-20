<?php namespace Finnito\FitlyticsModule\Activity;

use Finnito\FitlyticsModule\Activity\Contract\ActivityRepositoryInterface;
use Anomaly\Streams\Platform\Entry\EntryRepository;

class ActivityRepository extends EntryRepository implements ActivityRepositoryInterface
{

    /**
     * The entry model.
     *
     * @var ActivityModel
     */
    protected $model;

    /**
     * Create a new ActivityRepository instance.
     *
     * @param ActivityModel $model
     */
    public function __construct(ActivityModel $model)
    {
        $this->model = $model;
        $this->now = \Carbon\Carbon::now("UTC");
        $this->offset = \Carbon\Carbon::createFromTimestamp(0, config('app.timezone'))->getTimezone()->toOffsetName();
    }

    public function weekBoundaries()
    {
        $now = \Carbon\Carbon::now("UTC");
        // dd($now->startOfWeek());
        return [
            $now->startOfWeek()->format("M jS"),
            $now->endOfWeek()->format("M jS"),
        ];
    }

    public function currentWeekStatistics($week_of)
    {
        // $now = \Carbon\Carbon::now("Pacific/Auckland");
        $now = \Carbon\Carbon::parse($week_of, "Pacific/Auckland");
        $offset = \Carbon\Carbon::createFromTimestamp(0, "Pacific/Auckland")->getTimezone()->toOffsetName();
        return $this->model->newQuery()
            ->selectRaw("SUM(distance) AS distance, SUM(total_elevation_gain) as elevation, SUM(moving_time) as moving_time")
            ->whereRaw(
                "datetime(strftime(start_date, '%Y-%m-%dT%H:%i:%sZ'), '+00:00','"
                . $offset
                . "') BETWEEN '"
                . $now->startOfWeek()->format("Y-m-d H:i:s")
                . "' AND '"
                . $now->endOfWeek()->format("Y-m-d H:i:s")
                . "'"
            )
            ->first();
    }

    public function currentWeekStatisticsByType($week_of)
    {
        $now = \Carbon\Carbon::parse($week_of);
        // $offset = \Carbon\Carbon::createFromTimestamp(0)->getTimezone()->toOffsetName();
        return $this->model->query()
            ->selectRaw("type, SUM(distance) AS distance, SUM(total_elevation_gain) as elevation, SUM(moving_time) as moving_time ")
            ->whereRaw(
                "datetime(strftime('%Y-%m-%dT%H:%M:%SZ', JSON_EXTRACT(activity_json, '$.start_date_local')), '+00:00') BETWEEN '"
                . $now->startOfWeek()->format("Y-m-d H:i:s")
                . "' AND '"
                . $now->endOfWeek()->format("Y-m-d H:i:s")
                . "'"
            )
            ->groupBy("type")
            ->orderBy("type", "asc")
            ->get();
    }

    public function thisWeek()
    {
        $now = \Carbon\Carbon::now("UTC");
        $offset = \Carbon\Carbon::createFromTimestamp(0, config('app.timezone'))->getTimezone()->toOffsetName();
        
        return $this->model->query()
            ->whereRaw(
                "datetime(strftime(start_date, '%Y-%m-%dT%H:%M:%SZ'), '+00:00','"
                . $offset
                . "') BETWEEN '"
                . $now->startOfWeek()->format("Y-m-d H:i:s")
                . "' AND '"
                . $now->endOfWeek()->format("Y-m-d H:i:s")
                . "'"
            )
            ->orderBy("start_date", "asc")
            ->get();
    }

    public function getSelectWeeks()
    {
        $weeks = $this->model->query()
            ->get()
            ->groupBy(function ($activity) {
                $dte = \Carbon\Carbon::parse($activity->activity_json()->start_date_local)->startOfWeek();
                return $dte->year . "-" . $dte->week;
                // return $dte->isoWeekYear() . "-" . $dte->isoWeek();
            });
            // ->keys()
            // ->sortDesc();
        // dd($weeks);
            // ->first();
            // ;
        // return $weeks;
        $out = [];
        foreach ($weeks as $week => $activities) {
            $date = \Carbon\Carbon::now();
            // dd($date);
            // dd($week);
            $date->setISODate(explode("-", $week)[0], explode("-", $week)[1]);
            // dd($date);
            // $start = $date;
            // $end = $date->endOfWeek();
            // dd($date);
            // dd($start, $end);
            array_push($out, $date->startOfWeek());
        }

        return collect($out)->sortDesc();
    }

    public function weeklyRunStats()
    {
        $runs = $this->model->query()
            ->where("type", "Run")
            ->get();

        $weeks = $runs->groupBy(function ($run) {
            // dd($run->activity_json);
            $date = \Carbon\Carbon::parse($run->activity_json()->start_date_local);
            $week = $date->format("W");
            $year = $date->format("Y");
            return "$year-$week";
        });

        $weeks = $weeks->sortKeys();

        $out = [];
        $out["datasets"] = [];

        $distanceDataset = [];
        $elevationDataset = [];
        $durationDataset = [];

        foreach ($weeks as $key=>$week) {
            // $out[key($week)] = [];
            // $w = [];
            $distance = 0;
            $elevation = 0;
            $moving_time = 0;
            foreach ($week as $run) {
                $distance += $run->distance;
                $elevation += $run->total_elevation_gain;
                $moving_time += $run->moving_time;
            }

            $start_date = \Carbon\Carbon::parse($week[0]->activity_json()->start_date_local)->format("d-m-Y");

            $distance = $this->model->metersToKilometers($distance, 2);
            $elevation = $this->model->metersToKilometers($elevation, 2);
            $moving_time = $this->model->secondsToHours($moving_time);

            array_push($distanceDataset, ["x" => $start_date, "y" => $distance]);
            array_push($elevationDataset, ["x" => $start_date, "y" => $elevation]);
            array_push($durationDataset, ["x" => $start_date, "y" => $moving_time]);
        }

        array_push($out["datasets"], [
            "data" => $distanceDataset,
            "label" => "Distance (km)",
            "backgroundColor" => "rgba(230, 126, 34, 0.5)",
            "borderColor" => "rgba(230, 126, 34,1.0)",
            "yAxisID" => 'y1',
        ]);
        array_push($out["datasets"], [
            "data" => $elevationDataset,
            "label" => "Elevation (km)",
            "backgroundColor" => "rgba(52, 152, 219,0.5)",
            "borderColor" => "rgba(52, 152, 219,1.0)",
            "yAxisID" => 'y2',
            "hidden" => true,
        ]);

        array_push($out["datasets"], [
            "data" => array_fill(0, sizeof($weeks), env("RUN_MAX_LOAD")),
            "label" => "Maximum Load (km)",
            "borderColor" => "#e74c3c",
            "yAxisID" => 'y1',
            "pointRadius" => 0,
        ]);
        
        return json_encode($out);
    }
}
