<?php namespace Finnito\FitlyticsModule\Http\Controller;

use Anomaly\Streams\Platform\Http\Controller\PublicController;
use Finnito\FitlyticsModule\Activity\ActivityModel;
use Finnito\FitlyticsModule\Activity\Contract\ActivityRepositoryInterface;
use Finnito\FitlyticsModule\Plan\Contract\PlanRepositoryInterface;
use Finnito\FitlyticsModule\Note\Contract\NoteRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Anomaly\Streams\Platform\View\ViewTemplate;
use \Anomaly\Streams\Platform\Message\MessageBag;

class FitlyticsController extends PublicController
{
    public function __construct(
        Request $request,
        ViewTemplate $template,
        MessageBag $messages
    ) {
        $this->request = $request;
        $this->template = $template;
        $this->messages = $messages;

        if ($request->path() == "/") {
            $this->week_of = \Carbon\Carbon::parse("now")->timezone("Pacific/Auckland");
        } else {
            $this->week_of = \Carbon\Carbon::parse($request->path())->timezone("Pacific/Auckland");
        }
    }

    public function home(ActivityRepositoryInterface $activitiesRepository, NoteRepositoryInterface $notesRepository, PlanRepositoryInterface $plansRepository, $week = null)
    {
        $now = $this->week_of;

        $this->template->set("meta_title", $now->format("d-m-Y"));
        
        /**
         * Get current week activities,
         * grouped by day of week.
         **/
        $activities = $activitiesRepository->newQuery()
            ->whereBetween("activity_json->start_date_local", [$this->week_of->startOfWeek()->format("Y-m-d\TH:i:s"), $this->week_of->endOfWeek()->format("Y-m-d\TH:i:s")])
            ->orderBy("start_date")
            ->get();
        $activities = $activities->groupBy(function ($activity) {
            return \Carbon\Carbon::parse(json_decode($activity->activity_json)->start_date_local)->dayOfWeekIso;
        });
        
        /**
         * Get current week notes,
         * grouped by day of week.
         **/
        $notes = $notesRepository->newQuery()
            ->select("id", "date", "injured", "sick", "sleep_quality", "stress_level", "note")
            ->whereBetween("date", [$this->week_of->startOfWeek()->toDateString(), $this->week_of->endOfWeek()->toDateString()])
            ->get();
        $notes = $notes->groupBy(function ($note) {
            return \Carbon\Carbon::parse($note->date)->dayOfWeekIso;
        });

        /**
         * Get current week plans,
         * grouped by day of week.
         **/
        $plans = $plansRepository->newQuery()
            ->whereBetween("date", [$this->week_of->startOfWeek()->toDateString(), $this->week_of->endOfWeek()->toDateString()])
            ->get();
        $plans = $plans->groupBy(function ($plan) {
            return \Carbon\Carbon::parse($plan->date)->dayOfWeekIso;
        });


        $period = new \Carbon\CarbonPeriod(
            \Carbon\Carbon::parse($this->week_of)->startOfWeek()->toDateString(),
            \Carbon\Carbon::parse($this->week_of)->endOfWeek()->toDateString()
        );

        $firstActivity = ActivityModel::select("start_date")
            ->orderBy("start_date", "ASC")
            ->limit(1)
            ->get()
            ->pluck("start_date")[0];

        $weeks = new \Carbon\CarbonPeriod(
            $firstActivity->startOfWeek()->toDateString(),
            "7 days",
            \Carbon\Carbon::parse("now")->startOfWeek()->toDateString()
        );

        return view(
            'finnito.module.fitlytics::pages/home',
            [
                "activities" => $activities,
                "plans" => $plans,
                "notes" => $notes,
                "period" => $period,
                "currentWeekStatisticsByType" => $activitiesRepository->currentWeekStatisticsByType($this->week_of),
                "week_of" => $now,
                "weeks" => $weeks,
            ]
        );
    }

    

    public function weeklySummaryChartData(ActivityRepositoryInterface $activities)
    {
        return $activities->weeklyRunStats();
    }

    public function weeklyRunData(ActivityRepositoryInterface $activitiesRepository)
    {
        $out = [];
        $out["datasets"] = [];
        $now = \Carbon\CarbonImmutable::now()->timezone("Pacific/Auckland");
        // $now = $now->sub(1, "week");
        $runWeek = [
            ["y" => $activitiesRepository->newQuery()
                ->whereDate("activity_json()->start_date_local", $now->startOfWeek()->toDateString())
                ->where("type", "Run")
                ->sum("distance"),
                "x" => "Mon",
            ],
            ["y" => $activitiesRepository->newQuery()
                ->whereDate("activity_json()->start_date_local", $now->startOfWeek()->add(1, "day")->toDateString())
                ->where("type", "Run")
                ->sum("distance"),
            "x" => "Tues",
            ],
            ["y" => $activitiesRepository->newQuery()
                ->whereDate("activity_json()->start_date_local", $now->startOfWeek()->add(2, "day")->toDateString())
                ->where("type", "Run")
                ->sum("distance"),
                "x" => "Wed",
            ],
            ["y" => $activitiesRepository->newQuery()
                ->whereDate("activity_json()->start_date_local", $now->startOfWeek()->add(3, "day")->toDateString())
                ->where("type", "Run")
                ->sum("distance"),
                "x" => "Thur",
            ],
            ["y" => $activitiesRepository->newQuery()
                ->whereDate("activity_json()->start_date_local", $now->startOfWeek()->add(4, "day")->toDateString())
                ->where("type", "Run")
                ->sum("distance"),
                "x" => "Fri",
            ],
            ["y" => $activitiesRepository->newQuery()
                ->whereDate("activity_json()->start_date_local", $now->startOfWeek()->add(5, "day")->toDateString())
                ->where("type", "Run")
                ->sum("distance"),
                "x" => "Sat",
            ],
            ["y" => $activitiesRepository->newQuery()
                ->whereDate("activity_json()->start_date_local", $now->startOfWeek()->add(6, "day")->toDateString())
                ->where("type", "Run")
                ->sum("distance"),
                "x" => "Sun",
            ],
        ];

        $bikeWeek = [
            ["y" => $activitiesRepository->newQuery()
                ->whereDate("activity_json()->start_date_local", $now->startOfWeek()->toDateString())
                ->where("type", "Ride")
                ->sum("distance"),
                "x" => "Mon",
            ],
            ["y" => $activitiesRepository->newQuery()
                ->whereDate("activity_json()->start_date_local", $now->startOfWeek()->add(1, "day")->toDateString())
                ->where("type", "Ride")
                ->sum("distance"),
            "x" => "Tues",
            ],
            ["y" => $activitiesRepository->newQuery()
                ->whereDate("activity_json()->start_date_local", $now->startOfWeek()->add(2, "day")->toDateString())
                ->where("type", "Ride")
                ->sum("distance"),
                "x" => "Wed",
            ],
            ["y" => $activitiesRepository->newQuery()
                ->whereDate("activity_json()->start_date_local", $now->startOfWeek()->add(3, "day")->toDateString())
                ->where("type", "Ride")
                ->sum("distance"),
                "x" => "Thur",
            ],
            ["y" => $activitiesRepository->newQuery()
                ->whereDate("activity_json()->start_date_local", $now->startOfWeek()->add(4, "day")->toDateString())
                ->where("type", "Ride")
                ->sum("distance"),
                "x" => "Fri",
            ],
            ["y" => $activitiesRepository->newQuery()
                ->whereDate("activity_json()->start_date_local", $now->startOfWeek()->add(5, "day")->toDateString())
                ->where("type", "Ride")
                ->sum("distance"),
                "x" => "Sat",
            ],
            ["y" => $activitiesRepository->newQuery()
                ->whereDate("activity_json()->start_date_local", $now->startOfWeek()->add(6, "day")->toDateString())
                ->where("type", "Ride")
                ->sum("distance"),
                "x" => "Sun",
            ],
        ];

        $kayakWeek = [
            ["y" => $activitiesRepository->newQuery()
                ->whereDate("activity_json()->start_date_local", $now->startOfWeek()->toDateString())
                ->where("type", "Kayaking")
                ->sum("distance"),
                "x" => "Mon",
            ],
            ["y" => $activitiesRepository->newQuery()
                ->whereDate("activity_json()->start_date_local", $now->startOfWeek()->add(1, "day")->toDateString())
                ->where("type", "Kayaking")
                ->sum("distance"),
            "x" => "Tues",
            ],
            ["y" => $activitiesRepository->newQuery()
                ->whereDate("activity_json()->start_date_local", $now->startOfWeek()->add(2, "day")->toDateString())
                ->where("type", "Kayaking")
                ->sum("distance"),
                "x" => "Wed",
            ],
            ["y" => $activitiesRepository->newQuery()
                ->whereDate("activity_json()->start_date_local", $now->startOfWeek()->add(3, "day")->toDateString())
                ->where("type", "Kayaking")
                ->sum("distance"),
                "x" => "Thur",
            ],
            ["y" => $activitiesRepository->newQuery()
                ->whereDate("activity_json()->start_date_local", $now->startOfWeek()->add(4, "day")->toDateString())
                ->where("type", "Kayaking")
                ->sum("distance"),
                "x" => "Fri",
            ],
            ["y" => $activitiesRepository->newQuery()
                ->whereDate("activity_json()->start_date_local", $now->startOfWeek()->add(5, "day")->toDateString())
                ->where("type", "Kayaking")
                ->sum("distance"),
                "x" => "Sat",
            ],
            ["y" => $activitiesRepository->newQuery()
                ->whereDate("activity_json()->start_date_local", $now->startOfWeek()->add(6, "day")->toDateString())
                ->where("type", "Kayaking")
                ->sum("distance"),
                "x" => "Sun",
            ],
        ];

        $swimWeek = [
            ["y" => $activitiesRepository->newQuery()
                ->whereDate("activity_json()->start_date_local", $now->startOfWeek()->toDateString())
                ->where("type", "Swim")
                ->sum("distance"),
                "x" => "Mon",
            ],
            ["y" => $activitiesRepository->newQuery()
                ->whereDate("activity_json()->start_date_local", $now->startOfWeek()->add(1, "day")->toDateString())
                ->where("type", "Swim")
                ->sum("distance"),
            "x" => "Tues",
            ],
            ["y" => $activitiesRepository->newQuery()
                ->whereDate("activity_json()->start_date_local", $now->startOfWeek()->add(2, "day")->toDateString())
                ->where("type", "Swim")
                ->sum("distance"),
                "x" => "Wed",
            ],
            ["y" => $activitiesRepository->newQuery()
                ->whereDate("activity_json()->start_date_local", $now->startOfWeek()->add(3, "day")->toDateString())
                ->where("type", "Swim")
                ->sum("distance"),
                "x" => "Thur",
            ],
            ["y" => $activitiesRepository->newQuery()
                ->whereDate("activity_json()->start_date_local", $now->startOfWeek()->add(4, "day")->toDateString())
                ->where("type", "Swim")
                ->sum("distance"),
                "x" => "Fri",
            ],
            ["y" => $activitiesRepository->newQuery()
                ->whereDate("activity_json()->start_date_local", $now->startOfWeek()->add(5, "day")->toDateString())
                ->where("type", "Swim")
                ->sum("distance"),
                "x" => "Sat",
            ],
            ["y" => $activitiesRepository->newQuery()
                ->whereDate("activity_json()->start_date_local", $now->startOfWeek()->add(6, "day")->toDateString())
                ->where("type", "Swim")
                ->sum("distance"),
                "x" => "Sun",
            ],
        ];

        array_push($out["datasets"], [
            "data" => $runWeek,
            "backgroundColor" => "#2ecc71",
            "label" => "Run",
        ]);

        array_push($out["datasets"], [
            "data" => $bikeWeek,
            "backgroundColor" => "#f1c40f",
            "label" => "Bike",
            "hidden" => true,
        ]);

        array_push($out["datasets"], [
            "data" => $swimWeek,
            "backgroundColor" => "#3498db",
            "label" => "Swim",
            "hidden" => true,
        ]);

        array_push($out["datasets"], [
            "data" => $kayakWeek,
            "backgroundColor" => "#9b59b6",
            "label" => "Kayak",
            "hidden" => true,
        ]);
        // array_push($out["datasets"], $week);

        return json_encode($out);
    }

    

    
}
