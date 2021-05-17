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

        $types = [
            ["Run", "#2ecc71"],
            ["Ride", "#f1c40f"],
            ["Kayaking", "#3498db"],
            ["Swim", "#9b59b6"]
        ];

        foreach ($types as $type) {
            $week = [
                ["y" => $activities->newQuery()
                    ->whereDate("activity_json->start_date_local", $date->startOfWeek()->toDateString())
                    ->where("type", $type[0])
                    ->sum("distance"),
                    "x" => "Mon",
                ],
                ["y" => $activities->newQuery()
                    ->whereDate("activity_json->start_date_local", $date->startOfWeek()->add(1, "day")->toDateString())
                    ->where("type", $type[0])
                    ->sum("distance"),
                "x" => "Tues",
                ],
                ["y" => $activities->newQuery()
                    ->whereDate("activity_json->start_date_local", $date->startOfWeek()->add(2, "day")->toDateString())
                    ->where("type", $type[0])
                    ->sum("distance"),
                    "x" => "Wed",
                ],
                ["y" => $activities->newQuery()
                    ->whereDate("activity_json->start_date_local", $date->startOfWeek()->add(3, "day")->toDateString())
                    ->where("type", $type[0])
                    ->sum("distance"),
                    "x" => "Thur",
                ],
                ["y" => $activities->newQuery()
                    ->whereDate("activity_json->start_date_local", $date->startOfWeek()->add(4, "day")->toDateString())
                    ->where("type", $type[0])
                    ->sum("distance"),
                    "x" => "Fri",
                ],
                ["y" => $activities->newQuery()
                    ->whereDate("activity_json->start_date_local", $date->startOfWeek()->add(5, "day")->toDateString())
                    ->where("type", $type[0])
                    ->sum("distance"),
                    "x" => "Sat",
                ],
                ["y" => $activities->newQuery()
                    ->whereDate("activity_json->start_date_local", $date->startOfWeek()->add(6, "day")->toDateString())
                    ->where("type", $type[0])
                    ->sum("distance"),
                    "x" => "Sun",
                ],
            ];

            array_push($out["datasets"], [
                "data" => $week,
                "backgroundColor" => $type[1],
                "label" => $type[0],
            ]);
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
