<?php namespace Finnito\FitlyticsModule\Http\Controller;

use Anomaly\Streams\Platform\Http\Controller\PublicController;
use Finnito\FitlyticsModule\Activity\ActivityRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class APIController extends PublicController
{

    public function __construct(Auth $auth, Request $request)
    {
        $this->auth = $auth;
        $this->request = $request;

        if ($request->has("week-of")) {
            $this->week_of = $request->query("week-of");
        } else {
            $this->week_of = "now";
        }
    }

    public function currentWeekChart(ActivityRepository $activities)
    {
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

        // return view(
        //     'finnito.module.fitlytics::asyncHtml/currentWeekChart',
        //     [
        //         "data" => $out,
        //     ]
        // );
    }
}
