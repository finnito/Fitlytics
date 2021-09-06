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

        // $splitPath = explode("/", $request->path());
        // $this->week_of = end($splitPath);

        // if ($request->has("week-of")) {
            // $this->week_of = $request->query("week-of");
        // } else {
            // $this->week_of = "now";
        // }
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

            if ($type == "Yoga") {
                foreach ($week as $i => $day) {
                    $week[$i]["y"] = round(floatval($day["y"])/60, 2);
                }
                array_push($out["datasets"], [
                    "data" => $week,
                    "backgroundColor" => $colours[$num],
                    "label" => $type,
                    "yAxisID" => "y2",
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
            ]);
        }

        
        
        return json_encode($out);
    }

    }
}
