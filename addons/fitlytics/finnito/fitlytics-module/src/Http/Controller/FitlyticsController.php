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
        ActivityRepositoryInterface $activities,
        PlanRepositoryInterface $planModel,
        NoteRepositoryInterface $noteModel,
        ViewTemplate $template,
        MessageBag $messages
    ) {
        $this->request = $request;
        $this->template = $template;
        $this->messages = $messages;
        // dd($request->path());
        if ($request->path() == "/") {
            $this->week_of = \Carbon\Carbon::parse("now")->timezone("Pacific/Auckland");
        } else {
            $this->week_of = \Carbon\Carbon::parse($request->path())->timezone("Pacific/Auckland");
        }
        // dd($this->week_of);
    }

    public function home(ActivityRepositoryInterface $activitiesRepository, NoteRepositoryInterface $notesRepository, PlanRepositoryInterface $plansRepository, $week = null)
    {
        // dd($week);
        $now = $this->week_of;
        // $this->messages->success("This is a success!");
        // $this->messages->warning("This is a warning!");
        // $this->messages->info("This is a info!");
        // $this->messages->error("This is an error!");
        // dd($this->week_of);
        // $now = \Carbon\CarbonImmutable::parse($this->week_of)->timezone("Pacific/Auckland");
        // dd($now);

        $this->template->set("meta_title", $now->format("d-m-Y"));
        
        $activities = $activitiesRepository->newQuery()
            ->whereBetween("activity_json->start_date_local", [$now->startOfWeek()->format("Y-m-d\TH:i:s"), $now->endOfWeek()->format("Y-m-d\TH:i:s")])
            ->orderBy("start_date")
            ->get();
            
        // dd($now, $now->startOfWeek()->format("Y-m-d\TH:i:s"), $now->endOfWeek()->format("Y-m-d\TH:i:s"), $activities);
        // dd($activities);
        $activities = $activities->groupBy(function($activity) {
            return \Carbon\Carbon::parse(json_decode($activity->activity_json)->start_date_local)->dayOfWeekIso;
        });
        
        $notes = $notesRepository->newQuery()
            ->select("id", "date", "injured", "sick", "sleep_quality", "stress_level", "note")
            ->whereBetween("date", [$now->startOfWeek()->toDateString(), $now->endOfWeek()->toDateString()])
            ->get();
        $notes = $notes->groupBy(function($note) {
            return \Carbon\Carbon::parse($note->date)->dayOfWeekIso;
        });
        // dd($notes);

        $plans = $plansRepository->newQuery()
            ->whereBetween("date", [$now->startOfWeek()->toDateString(), $now->endOfWeek()->toDateString()])
            ->get();
        $plans = $plans->groupBy(function($plan) {
            // dd(\Carbon\Carbon::parse($plan->date)->dayOfWeekIso);
            return \Carbon\Carbon::parse($plan->date)->dayOfWeekIso;
        });
        // dd($plans);


        $period = new \Carbon\CarbonPeriod(
            \Carbon\Carbon::parse($now)->startOfWeek()->toDateString(),
            \Carbon\Carbon::parse($now)->startOfWeek()->add(6, "day")->toDateString()
        );
        // dd($period);
        // dd($activities);
        // dd(\Carbon\Carbon::parse("now"));
        // config(['DEBUG_BAR' => "true"]);
        // $now = \Carbon\CarbonImmutable::parse($this->week_of)->timezone("Pacific/Auckland");
        // dd($now);
        // $week = [
        //     [
        //         "date" => $now->startOfWeek(),
        //         "note" => $notes->query()->select("id", "injured", "sick", "sleep_quality", "stress_level", "note")->whereDate("date", $now->startOfWeek()->toDateString())->first(),
        //         "plan" => $plans->query()->select()->whereDate("date", $now->startOfWeek()->toDateString())->first(),
        //         "activities" => $activitiesRepository->newQuery()
        //             ->whereDate("activity_json->start_date_local", $now->startOfWeek()->toDateString())
        //             ->orderBy("activity_json->start_date_local", "asc")
        //             ->get(),
        //     ],
        //     [
        //         "date" => $now->startOfWeek()->add(1, "day"),
        //         "note" => $notes->query()->select("id", "injured", "sick", "sleep_quality", "stress_level", "note")->whereDate("date", $now->startOfWeek()->add(1, "day")->toDateString())->first(),
        //         "plan" => $plans->query()->select()->whereDate("date", $now->startOfWeek()->add(1, "day")->toDateString())->first(),
        //         "activities" => $activitiesRepository->newQuery()
        //             ->whereDate("activity_json->start_date_local", $now->startOfWeek()->add(1, "day")->toDateString())
        //             ->orderBy("activity_json->start_date_local", "asc")
        //             ->get(),
        //     ],
        //     [
        //         "date" => $now->startOfWeek()->add(2, "day"),
        //         "note" => $notes->query()->select("id", "injured", "sick", "sleep_quality", "stress_level", "note")->whereDate("date", $now->startOfWeek()->add(2, "day")->toDateString())->first(),
        //         "plan" => $plans->query()->select()->whereDate("date", $now->startOfWeek()->add(2, "day")->toDateString())->first(),
        //         "activities" => $activitiesRepository->newQuery()
        //             ->whereDate("activity_json->start_date_local", $now->startOfWeek()->add(2, "day")->toDateString())
        //             ->orderBy("activity_json->start_date_local", "asc")
        //             ->get(),
        //     ],
        //     [
        //         "date" => $now->startOfWeek()->add(3, "day"),
        //         "note" => $notes->query()->select("id", "injured", "sick", "sleep_quality", "stress_level", "note")->whereDate("date", $now->startOfWeek()->add(3, "day")->toDateString())->first(),
        //         "plan" => $plans->query()->select()->whereDate("date", $now->startOfWeek()->add(3, "day")->toDateString())->first(),
        //         "activities" => $activitiesRepository->newQuery()
        //             ->whereDate("activity_json->start_date_local", $now->startOfWeek()->add(3, "day")->toDateString())
        //             ->orderBy("activity_json->start_date_local", "asc")
        //             ->get(),
        //     ],
        //     [
        //         "date" => $now->startOfWeek()->add(4, "day"),
        //         "note" => $notes->query()->select("id", "injured", "sick", "sleep_quality", "stress_level", "note")->whereDate("date", $now->startOfWeek()->add(4, "day")->toDateString())->first(),
        //         "plan" => $plans->query()->select()->whereDate("date", $now->startOfWeek()->add(4, "day")->toDateString())->first(),
        //         "activities" => $activitiesRepository->newQuery()
        //             ->whereDate("activity_json->start_date_local", $now->startOfWeek()->add(4, "day")->toDateString())
        //             ->orderBy("activity_json->start_date_local", "asc")
        //             ->get(),
        //     ],
        //     [
        //         "date" => $now->startOfWeek()->add(5, "day"),
        //         "note" => $notes->query()->select("id", "injured", "sick", "sleep_quality", "stress_level", "note")->whereDate("date", $now->startOfWeek()->add(5, "day")->toDateString())->first(),
        //         "plan" => $plans->query()->select()->whereDate("date", $now->startOfWeek()->add(5, "day")->toDateString())->first(),
        //         "activities" => $activitiesRepository->newQuery()
        //             ->whereDate("activity_json->start_date_local", $now->startOfWeek()->add(5, "day")->toDateString())
        //             ->orderBy("activity_json->start_date_local", "asc")
        //             ->get(),
        //     ],
        //     [
        //         "date" => $now->startOfWeek()->add(6, "day"),
        //         "note" => $notes->query()->select("id", "injured", "sick", "sleep_quality", "stress_level", "note")->whereDate("date", $now->startOfWeek()->add(6, "day")->toDateString())->first(),
        //         "plan" => $plans->query()->select()->whereDate("date", $now->startOfWeek()->add(6, "day")->toDateString())->first(),
        //         "activities" => $activitiesRepository->newQuery()
        //             ->whereDate("activity_json->start_date_local", $now->startOfWeek()->add(6, "day")->toDateString())
        //             ->get(),
        //     ],
        // ];

        // dd($week);

        // $this->template->set("meta_title", "Home");
        // dd($activitiesRepository->currentWeekStatisticsByType($this->week_of));

        return view(
            'finnito.module.fitlytics::pages/home',
            [
                "activities" => $activities,
                "plans" => $plans,
                "notes" => $notes,
                "period" => $period,
                //'activities' => $activitiesRepository->thisWeek(),
                "currentWeekStatisticsByType" => $activitiesRepository->currentWeekStatisticsByType($this->week_of),
                //"currentWeekStatistics" => $activitiesRepository->currentWeekStatistics($this->week_of),
                //"weekBoundaries" => $activitiesRepository->weekBoundaries(),
                // "week" => $week,
                //"weeklyRunStats" => $activitiesRepository->weeklyRunStats(),
                "week_of" => $now,
                "weeks" => $activitiesRepository->getSelectWeeks(),
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
