<?php namespace Finnito\FitlyticsModule\Http\Controller;

use Anomaly\Streams\Platform\Http\Controller\PublicController;
use Finnito\FitlyticsModule\Activity\ActivityRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Finnito\FitlyticsModule\Activity\ActivityModel;
use Illuminate\Support\Facades\DB;

class APIController extends PublicController
{

    public function __construct(Auth $auth, Request $request)
    {
        $this->auth = $auth;
        $this->request = $request;
    }

    /**
     * A flexible API route for getting
     * statistics about activities in
     * bulk.
     * 
     * VERB:
     * - count
     * - sum
     * 
     * FILTER:
     * - all (special)
     * - Run (or any other activity type)
     * - Run,Ride
     * 
     * PERIOD:
     * - day
     * - week
     * - month
     * - year
     * - rolling-year
     * 
     * METRICS:
     * - distance,elevation (for example)
     **/
    public function activities(
        ActivityRepository $acitivites,
        $verb,
        $filter,
        $period,
        $metrics
    ) {
        // dd($verb, $filter, $period, $metrics);
        // dd(explode(",", $metrics));
        $query = $acitivites->newQuery();

        // BEGIN VERB and METRICS
        if ($verb == "COUNT") {
            $query->selectRaw("COUNT(id) AS count");
        } elseif ($verb == "SUM") {
            $raw = [];
            // $metricsArray = explode(",", $metrics);
            // if (sizeof($metricsArray) > 1) {
            foreach (explode(",", $metrics) as $metric) {
                array_push($raw, "SUM(".$metric.") AS ".$metric);
            }
            // }
            
            $query->selectRaw(implode(",", $raw));
        } else {
            exit("Option '" . $verb . "' does not exist for the verb parameter.");
        }
        // END VERB and METRICS

        // BEGIN: FILTER
        if ($filter !== "all") {
            $query->whereIn("type", explode(",", $filter));
        }
        // END: FILTER

        // BEGIN: PERIOD
        $end = \Carbon\CarbonImmutable::now()->timezone("Pacific/Auckland");
        switch ($period) {
            case "day":
                $start = $end;
                break;
            case "week":
                $start = $end->startOfWeek()->toDateString();
                break;
            case "month":
                $start = $end->startOfMonth()->toDateString();
                break;
            case "year":
                $start = $end->startOfYear()->toDateString();
                break;
            case "rolling-year":
                $start = $end->subYear()->toDateString();
                break;
            default:
                exit("Option '" . $period . "' does not exist for the period parameter.");
        }

        $query->whereBetween("activity_json->start_date_local", [$start, $end]);
        // END: PERIOD

        return $query->get();
    }

    public function currentWeekChart(ActivityRepository $activities, $week)
    {
        $this->week_of = $week;

        $out = [];
        $out["datasets"] = [];
        $date = \Carbon\CarbonImmutable::parse($this->week_of)->timezone("Pacific/Auckland");

        $types = $activities->newQuery()
            ->select("type")
            ->whereBetween("activity_json->start_date_local", [$date->startOfWeek()->toDateString(), $date->endOfWeek()->toDateString()])
            ->groupBy("type")
            ->get()
            ->pluck("type")
            ->values();

        $colours = [
            "#fad390",
            "#6a89cc",
            "#82ccdd",
            "#b8e994",
            "#ff7f50",
            "#ff6b81",
            "#9b59b6",
        ];

        foreach ($types as $num => $type) {
            if ($type == "Yoga") {
                $sumField = "moving_time";
            } else {
                $sumField = "distance";
            }

            $week = [
                ["y" => $activities->newQuery()
                    ->whereDate("activity_json->start_date_local", $date->startOfWeek()->toDateString())
                    ->where("type", $type)
                    ->sum($sumField),
                    "x" => "Mon",
                ],
                ["y" => $activities->newQuery()
                    ->whereDate("activity_json->start_date_local", $date->startOfWeek()->add(1, "day")->toDateString())
                    ->where("type", $type)
                    ->sum($sumField),
                "x" => "Tues",
                ],
                ["y" => $activities->newQuery()
                    ->whereDate("activity_json->start_date_local", $date->startOfWeek()->add(2, "day")->toDateString())
                    ->where("type", $type)
                    ->sum($sumField),
                    "x" => "Wed",
                ],
                ["y" => $activities->newQuery()
                    ->whereDate("activity_json->start_date_local", $date->startOfWeek()->add(3, "day")->toDateString())
                    ->where("type", $type)
                    ->sum($sumField),
                    "x" => "Thur",
                ],
                ["y" => $activities->newQuery()
                    ->whereDate("activity_json->start_date_local", $date->startOfWeek()->add(4, "day")->toDateString())
                    ->where("type", $type)
                    ->sum($sumField),
                    "x" => "Fri",
                ],
                ["y" => $activities->newQuery()
                    ->whereDate("activity_json->start_date_local", $date->startOfWeek()->add(5, "day")->toDateString())
                    ->where("type", $type)
                    ->sum($sumField),
                    "x" => "Sat",
                ],
                ["y" => $activities->newQuery()
                    ->whereDate("activity_json->start_date_local", $date->startOfWeek()->add(6, "day")->toDateString())
                    ->where("type", $type)
                    ->sum($sumField),
                    "x" => "Sun",
                ],
            ];

            // if (in_array($type, ["Yoga", "RockClimbing", "Workout", "WeightTraining"])) {
            //     $yaxis = "y2";
            //     $unit = "min";
            // } else {
            //     $yaxis = "y";
            //     $unit = "km";
            // }

            if (in_array($type, ["Yoga", "RockClimbing", "Workout", "WeightTraining"])) {
                foreach ($week as $i => $day) {
                    $week[$i]["y"] = round(floatval($day["y"])/60, 2);
                }
                array_push($out["datasets"], [
                    "data" => $week,
                    "backgroundColor" => $colours[$num],
                    "label" => $type,
                    "yAxisID" => "y2",
                    "unit" => "min",
                ]);
            } else {
                foreach ($week as $i => $day) {
                    $week[$i]["y"] = round(floatval($day["y"])/1000, 2);
                }
                array_push($out["datasets"], [
                    "data" => $week,
                    "backgroundColor" => $colours[$num],
                    "label" => $type,
                    "yAxisID" => "y",
                    "unit" => "km",
                ]);
            }
        }

        return json_encode($out);
    }

    public function hrChart($week)
    {
        $this->week_of = $week;
        $out = [];
        $out["datasets"] = [];
        $date = \Carbon\CarbonImmutable::parse($this->week_of)->timezone("Pacific/Auckland");

        $activities = ActivityModel::select()
            ->whereBetween("activity_json->start_date_local", [$date->startOfWeek()->toDateString(), $date->endOfWeek()->toDateString()])
            ->get()
            ->values();

        if ($activities->isNotEmpty()) {
            $moving_time = $activities->pluck("moving_time")->sum();

            $zones = [
                [
                    "x" => "Z1 - Recovery",
                    "y" => 0,
                ],
                [
                    "x" => "Z2 - Aerobic Base",
                    "y" => 0,
                ],
                [
                    "x" => "Z3 - Tempo",
                    "y" => 0,
                ],
                [
                    "x" => "Z4 - Lactate Threshold",
                    "y" => 0,
                ],
                [
                    "x" => "Z5 - VO2 Max",
                    "y" => 0,
                ],
            ];

            foreach ($activities as $activity) {
                $zones[0]["y"] += floatval($activity->hrBuckets()[0]["count"]);
                $zones[1]["y"] += floatval($activity->hrBuckets()[1]["count"]);
                $zones[2]["y"] += floatval($activity->hrBuckets()[2]["count"]);
                $zones[3]["y"] += floatval($activity->hrBuckets()[3]["count"]);
                $zones[4]["y"] += floatval($activity->hrBuckets()[4]["count"]);
            }

            for ($i = 0; $i < sizeof($zones); $i++) {
                $zones[$i]["y"] = round((($zones[$i]["y"] / sizeof($activities)) * $moving_time) / 60);
            }

            array_push($out["datasets"], [
                "data" => $zones,
                "backgroundColor" => ["#3498db", "#2ecc71", "#f1c40f", "#e67e22", "#e74c3c"],
                "label" => "HR",
                "unit" => "min",
            ]);
        }

        
        
        return json_encode($out);
    }

    public function historicalWeeks($week)
    {
        $endDate = \Carbon\CarbonImmutable::parse($week)->endOfWeek();
        $startDate = $endDate->startOfWeek()->subWeeks(12);
        $period = \Carbon\CarbonPeriod::create($startDate, "1 week", $endDate);

        $out = [];
        $out["datasets"] = [];

        $types = ActivityModel::select()
            ->where("activity_json->start_date_local", ">=", $startDate)
            ->where("activity_json->start_date_local", "<=", $endDate)
            ->groupBy("type")
            ->orderBy("type", "DESC")
            ->get()
            ->pluck("type");

        foreach ($types as $type) {
            $summary = ActivityModel::select(
                    DB::raw("STRFTIME('%YW%W', JSON_EXTRACT(activity_json, '$.start_date_local')) AS week"),
                    DB::raw("SUM(distance) as distance"),
                    DB::raw("SUM(moving_time) as duration")
                )->where("type", $type)
                ->where("activity_json->start_date_local", ">=", $startDate)
                ->where("activity_json->start_date_local", "<=", $endDate)
                ->groupBy("week")
                ->orderBy("week", "ASC")
                ->get();

            $data = [];
            foreach ($period as $key => $date) {
                array_push($data, [
                    "x" => intval($date->setTime(12, 0, 0)->isoFormat("x")),
                    "y" => 0,
                ]);
            }

            for ($i = 0; $i < sizeof($data); $i++) {
                foreach ($summary as $week) {
                    list($year, $weekNum) = explode("W", $week->week);
                    $d = \Carbon\Carbon::now();
                    $d->setISODate($year, $weekNum);
                    $d->setTime(12, 0, 0);

                    if (intval($d->isoFormat("x")) == $data[$i]["x"]) {
                        if (in_array($type, ["Yoga", "RockClimbing", "Workout", "WeightTraining"])) {
                            $data[$i]["y"] = round($week->duration / 60, 2);
                        } else {
                            $data[$i]["y"] = round($week->distance / 1000, 2);
                        }
                        break;
                    }
                }
            }

            if ($type == "Run") {
                $borderColor = "#27ae60";
            } else {
                $borderColor = "#bdc3c7";
            }

            if (in_array($type, ["Yoga", "RockClimbing", "Workout", "WeightTraining"])) {
                $yaxis = "y2";
                $unit = "min";
            } else {
                $yaxis = "y";
                $unit = "km";
            }

            array_push($out["datasets"], [
                "data" => $data,
                "label" => $type,
                "hidden" => ($type != "Run"),
                "borderColor" => $borderColor,
                "yAxisID" => $yaxis,
                "unit" => $unit,
            ]);
        }

        return json_encode($out);
    }
}
