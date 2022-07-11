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


    public function weeklyLoad(
        ActivityRepository $acitivites,
        $metrics
    ) {
        $query = $acitivites->newQuery();
        $query->addSelect(DB::raw("STRFTIME('%YW%W', JSON_EXTRACT(activity_json, '$.start_date_local')) AS week"));

        $raw = [];
        foreach (explode(",", $metrics) as $metric) {
            array_push($raw, "SUM(".$metric.") AS ".$metric);
        }            
        $query->selectRaw(implode(", ", $raw));
        $query->groupBy("week");
        $query->orderBy("week", "DESC");
        $query->limit(52);

        $out = $query->get();
        for ($i = 0; $i < sizeof($out); $i++) {
            list($year, $weekNum) = explode("W", $out[$i]->week);
            $d = \Carbon\Carbon::now();
            $d->setISODate($year, $weekNum);
            $d->setTime(12, 0, 0);
            $out[$i]->week = $d->format("Y-m-d");
        }

        return $out;
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
        $query = $acitivites->newQuery();
        $query->addSelect("type");

        // BEGIN VERB and METRICS
        if ($verb == "COUNT") {
            $query->selectRaw("COUNT(id) AS count");
        } elseif ($verb == "SUM") {
            $raw = [];
            foreach (explode(",", $metrics) as $metric) {
                array_push($raw, "SUM(".$metric.") AS ".$metric);
            }            
            $query->selectRaw(implode(", ", $raw));
        } else {
            exit("Option '" . $verb . "' does not exist for the verb parameter.");
        }
        // END VERB and METRICS

        // BEGIN: FILTER
        if ($filter !== "all") {
            $query->whereIn("type", explode(",", $filter));
        }
        $query->groupBy("type");
        // END: FILTER

        // BEGIN: PERIOD
        $end = \Carbon\CarbonImmutable::now();
        switch ($period) {
            case "day":
                $start = $end->startOfDay()->toDateTimeString();
                break;
            case "week":
                $start = $end->startOfWeek()->startOfDay()->toDateTimeString();
                break;
            case "month":
                $start = $end->startOfMonth()->startOfDay()->toDateTimeString();
                break;
            case "year":
                $start = $end->startOfYear()->startOfDay()->toDateTimeString();
                break;
            case "rolling-year":
                $start = $end->subYear()->startOfDay()->toDateTimeString();
                break;
            default:
                exit("Option '" . $period . "' does not exist for the period parameter.");
        }

        // dd($start, $end->endOfWeek()->toDateTimeString());
        $query->whereBetween("activity_json->start_date_local", [$start, $end->endOfWeek()->toDateTimeString()]);
        // END: PERIOD

        return $query->get();
    }


    /**
     * A route to get activity
     * data to display on the
     * /activity/{id} pages.
     * Typically: distance, HR
     * cadence and altitude.
     **/
    public function data_streams(ActivityRepository $activities, $id)
    {
        if (!$activity = $activities->newQuery()->where("id", $id)->first()) {
            abort(404);
        }

        $borderColours = [
            "altitude" => "rgba(223, 228, 234, 1.0)",
            "cadence" => "rgba(34, 166, 179, 1.0)",
            "distance" => "rgba(72, 219, 251, 1.0)",
            "heartrate" => "rgba(235, 77, 75, 1.0)",
            "time" => "rgba(251, 197, 49, 1.0)",
            "latlng" => "rgba(230, 126, 34, 1.0)",
            "velocity_smooth" => "rgba(106, 176, 76, 1.0)",
            "watts" => "rgba(156, 136, 255, 1.0)",
            "grade_smooth" => "rgba(243, 104, 224, 1.0)",

        ];

        $fillColours = [
            "altitude" => "rgba(223, 228, 234, 0.5)",
            "cadence" => "rgba(34, 166, 179, 0.5)",
            "distance" => "rgba(72, 219, 251, 0.5)",
            "heartrate" => "rgba(235, 77, 75, 0.5)",
            "time" => "rgba(251, 197, 49, 0.5)",
            "latlng" => "rgba(230, 126, 34, 0.5)",
            "velocity_smooth" => "rgba(106, 176, 76, 0.5)",
            "watts" => "rgba(156, 136, 255, 0.5)",
            "grade_smooth" => "rgba(243, 104, 224, 0.5)",
        ];

        $dataStream = $activity->dataStreams();

        $data = [];
        $data["datasets"] = [];

        foreach ($dataStream as $key => $stream) {
            $dataset = [];

            if ($key == "altitude") {
                $dataset["fill"] = true;
            } else {
                $dataset["fill"] = false;
            }

            $dataset["tension"] = 0.5;
            $dataset["parsing"] = false;
            $dataset["indexAxis"] = "x";
            $dataset["borderWidth"] = 1;
            $dataset["borderColor"] = $borderColours[$key];
            $dataset["backgroundColor"] = $fillColours[$key];
            $dataset["radius"] = 0;
            $dataset["label"] = ucfirst($key);
            $dataset["data"] = [];

            if ($activity->type == "Run") {
                $multiplier = 2;
            } else {
                $multiplier = 1;
            }

            for ($i = 0; $i < sizeof($dataStream->$key->data); $i++) {
                if ($activity->type == "Run" and $key == "cadence") {
                    array_push($dataset["data"],[
                        "x" => $i+1,
                        "y" => ($dataStream->$key->data[$i] * 2)
                    ]);
                } else if ($key == "distance") {
                    array_push($dataset["data"],[
                        "x" => $i+1,
                        "y" => $dataStream->$key->data[$i] / 1000
                    ]);
                } else {
                    array_push($dataset["data"],[
                        "x" => $i+1,
                        "y" => $dataStream->$key->data[$i]
                    ]);
                }
                
            }

            array_push($data["datasets"], $dataset);
        }

        return response()->json($data);
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

        $user = auth()->user();
        // dd($user->z1);

        if ($activities->isNotEmpty()) {
            $moving_time = $activities->pluck("moving_time")->sum();

            $zones = [
                [
                    "x" => "Recovery (" . $user->z1 . ")",
                    "y" => 0,
                ],
                [
                    "x" => "Z1 (" . $user->z2 . ")",
                    "y" => 0,
                ],
                [
                    "x" => "Z2 (" . $user->z3 . ")",
                    "y" => 0,
                ],
                [
                    "x" => "Z3 (" . $user->z4 . ")",
                    "y" => 0,
                ],
                [
                    "x" => "Z4 (" . $user->z5 . ")",
                    "y" => 0,
                ],
            ];

            foreach ($activities as $activity) {
                $buckets = $activity->hrBuckets();
                // $buckets = json_decode($activity->hrBuckets(), true);
                foreach ([0, 1, 2, 3, 4] as $zone) {
                    if (isset($buckets[$zone]["count"])) {
                        $zones[$zone]["y"] += floatval($buckets[$zone]["count"]);
                    }
                }
            }

            for ($i = 0; $i < sizeof($zones); $i++) {
                // $percent = $zones[$i]["y"]
                $minutes = round((($zones[$i]["y"] / sizeof($activities)) * $moving_time) / 60);
                $zones[$i]["y"] = $minutes;
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
