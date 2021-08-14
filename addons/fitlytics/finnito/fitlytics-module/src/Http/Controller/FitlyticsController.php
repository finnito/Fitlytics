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

        if ($request->has("week-of")) {
            if (str_starts_with($request->query("week-of"), "a")) {
                $activity = $activities->newQuery()->select("activity_json->start_date_local")->where("id", substr($request->query("week-of"), 1))->first();
                $this->week_of = \Carbon\Carbon::parse($activity->start_date_local)->format("Y-m-d");
            }

            elseif (str_starts_with($request->query("week-of"), "p")) {
                $plan = $planModel->newQuery()->select("date")->where("id", substr($request->query("week-of"), 1))->first();
                $this->week_of = \Carbon\Carbon::parse($plan->date)->format("Y-m-d");
            }

            elseif (str_starts_with($request->query("week-of"), "n")) {
                $note = $noteModel->newQuery()->select("date")->where("id", substr($request->query("week-of"), 1))->first();
                $this->week_of = \Carbon\Carbon::parse($note->date)->format("Y-m-d");
            }

            else {
                $this->week_of = \Carbon\Carbon::parse($request->query("week-of"))->format("Y-m-d");
            }
        } else {
            $this->week_of = "now";
        }
    }

    public function home(ActivityRepositoryInterface $activitiesRepository, NoteRepositoryInterface $notesRepository, PlanRepositoryInterface $plansRepository)
    {
        // $this->messages->success("This is a success!");
        // $this->messages->warning("This is a warning!");
        // $this->messages->info("This is a info!");
        // $this->messages->error("This is an error!");
        $now = \Carbon\CarbonImmutable::parse($this->week_of)->timezone("Pacific/Auckland");
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

    public function activitiesCalendarFeed(Request $request, ActivityModel $activities)
    {
        $activities = $activities->query()
            ->where([
                ["start_date", ">=", $request->input("start")],
                ["start_date", "<=", $request->input("end")],
            ])
            ->get();

        foreach ($activities as $activity)
        {
            $activity->title = $activity->activityTypeEmoji() . ": " . $activity->name();
            $activity->start = $activity->start_date;
            $activity_end = new \DateTime($activity->start_date);
            $activity_end = $activity_end->add(\DateInterval::createFromDateString($activity->elapsed_time . " seconds"));
            $activity->end = $activity_end;
            $activity->backgroundColor = $activity->getColour();
            $activity->borderColor = "white";
            $activity->eventDisplay = "block";
            $activity->description = $activity->description();
        }
        return $activities;
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

    public function notesCalendarFeed(Request $request, NoteRepositoryInterface $notes)
    {
        $start = explode("T", $request->input("start"))[0];
        $end = explode("T", $request->input("end"))[0];
        $notes = $notes->newQuery()
            ->whereDate("date", ">=", $start)
            ->whereDate("date", "<", $end)
            ->get();

        foreach ($notes as $note)
        {
            $note->title = "📘 Note";
            $note->defaultAllDay = true;
            $note->start = $note->date;
            $note->borderColor = "white";
            $note->eventDisplay = "block";
            $note->description = $note->description();
        }
        return $notes;
    }

    public function plansCalendarFeed(Request $request, PlanRepositoryInterface $plans)
    {
        $start = explode("T", $request->input("start"))[0];
        $end = explode("T", $request->input("end"))[0];
        $plans = $plans->newQuery()
            ->whereDate("date", ">=", $start)
            ->whereDate("date", "<", $end)
            ->get();

        foreach ($plans as $plan)
        {
            $plan->title = "📝 Plan: {$plan->plan}";
            $plan->defaultAllDay = true;
            $plan->start = $plan->date;
            $plan->borderColor = "white";
            $plan->eventDisplay = "block";
            // $plan->description = $plan->description();
        }
        return $plans;
    }

    public function weeklySummaryFeed(Request $request, ActivityModel $activities)
    {
        $start_date = explode("T", $request->input("start"))[0];
        $start = new Carbon($start_date);
        $start_of_week = $start->startOfWeek(Carbon::MONDAY)->format("Y-m-d");
        $end_of_week = $start->endOfWeek(Carbon::SUNDAY)->format("Y-m-d");

        $groups = $activities->query()
            ->where([
                ["start_date", ">=", $request->input("start")],
                ["start_date", "<=", $request->input("end")],
            ])
            ->get()
            ->groupBy(function ($entry) {
                return Carbon::parse($entry->start_date)->setTimezone('Pacific/Auckland')->format("W");
            });
        $groups = $groups->sortKeys();
        // dd($groups);
        $out = [];
        foreach ($groups as $group) {
            $runDist = 0;
            $runTime = 0;
            $runElev = 0;
            $otherDist = 0;
            $otherTime = 0;
            $otherElev = 0;
            foreach ($group as $activity) {
                if ($activity->type == "Run") {
                    $runDist += $activity->distance;
                    $runTime += $activity->moving_time;
                    $runElev += $activity->total_elevation_gain;
                } else {
                    $otherDist += $activity->distance;
                    $otherTime += $activity->moving_time;
                    $otherElev += $activity->total_elevation_gain;
                }
            }

            $runDist = $activities->metersToKilometers($runDist, 2);
            $runTime = $activities->secondsToHours($runTime);
            $runElev = $activities->metersToKilometers($runElev, 2);
            $otherDist = $activities->metersToKilometers($otherDist, 2);
            $otherTime = $activities->secondsToHours($otherTime);
            $otherElev = $activities->metersToKilometers($otherElev, 2);
            $w = Carbon::parse($group[0]->start_date)
                ->setTimezone("Pacific/Auckland")
                ->format("W");
            // dd($group);

            $details = "Running
            Distance: {$runDist}km
            Time: {$runTime}
            Elevation: {$runElev}km

            Other
            Distance: {$otherDist}km
            Time: {$otherTime}
            Elevation: {$otherElev}km";

            $week = [];
            $week["start"] = Carbon::parse($group[0]->start_date)
                ->setTimezone('Pacific/Auckland')
                ->endOfWeek(Carbon::SUNDAY)
                ->format("Y-m-d");
            $week["defaultAllDay"] = true;
            $week["title"] = "W{$w} Summary
            ---
            {$details}";
            $week["description"] = $details;
            array_push($out, $week);

        }
        return $out;
    }

    public function plansICS(PlanRepositoryInterface $plans)
    {
        define('ICAL_FORMAT', 'Ymd\THis\Z');

        $plans = $plans->newQuery()->all();

        $icalObject = "BEGIN:VCALENDAR\n"
            . "VERSION:2.0\n"
            . "METHOD:PUBLISH\n"
            . "PRODID:-//Finn Le Sueur//Training Plan//EN\n";

        foreach ($plans as $plan) {
            if (sizeof($plan->decorated->plan->lines()) == 1) {
                $summary = "📝 " . $plan->plan;
                $description = str_replace("\r\n", "\\n", $plan->plan);
            } else {
                $summary = "📝 " . $plan->decorated->plan->line(1) . " ⨁";
                $description = str_replace("\r\n", "\\n", $plan->plan);
            }
            $icalObject .= ""
                . "BEGIN:VEVENT\n"
                . "TRANSP:TRANSPARENT\n"
                . "DTSTART;VALUE=DATE:" . date("Ymd", strtotime($plan->date)) . "\n"
                . "DTEND;VALUE=DATE:" . date("Ymd", strtotime($plan->date)) . "\n"
                . "DTSTAMP:" . date(ICAL_FORMAT, strtotime($plan->date)) . "\n"
                . "SUMMARY:" . $summary . "\n"
                . "DESCRIPTION:" . $description . "\n"
                . "UID:fitlytics-plan-" . $plan->id . "\n"
                . "STATUS:CONFIRMED\n"
                . "CREATED:" . date(ICAL_FORMAT, strtotime($plan->created_at)) . "\n"
                . "LAST-MODIFIED:" . date(ICAL_FORMAT, strtotime($plan->updated_at)) . "\n"
                . "LOCATION:\n"
                . "END:VEVENT\n";
        }
        $icalObject .= "END:VCALENDAR";

        header('Content-type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="fitlytics-planning.ics"');
        return $icalObject;
    }

    public function notesICS(NoteModel $notes)
    {
        define('ICAL_FORMAT', 'Ymd\THis\Z');

        $notes = $notes->all();

        $icalObject = "BEGIN:VCALENDAR\n"
            . "VERSION:2.0\n"
            . "METHOD:PUBLISH\n"
            . "PRODID:-//Finn Le Sueur//Training Plan//EN\n";

        foreach ($notes as $note) {
            $summary = "✍️ Training Journal";

            if ($note->injured) {
                $injured = "Yes";
            } else {
                $injured = "No";
            }

            if ($note->sick) {
                $sick = "Yes";
            } else {
                $sick = "No";
            }

            $noteText = str_replace("\r\n", "\\n", $note->note);

            $description =  "{$noteText}\\n"
                . "---" . "\\n"
                . "Sick: {$sick}" . "\\n"
                . "Injured: {$injured}" . "\\n"
                . "Sleep Quality: {$note->sleep_quality}" . "\\n"
                . "Stress Level: {$note->stress_level}" . "\\n"
                . "Weight: {$note->weight}";

            $icalObject .= ""
                . "BEGIN:VEVENT\n"
                . "TRANSP:TRANSPARENT\n"
                . "DTSTART;VALUE=DATE:" . date("Ymd", strtotime($note->date)) . "\n"
                . "DTEND;VALUE=DATE:" . date("Ymd", strtotime($note->date)) . "\n"
                . "DTSTAMP:" . date(ICAL_FORMAT, strtotime($note->date)) . "\n"
                . "SUMMARY:" . $summary . "\n"
                . "DESCRIPTION:" . $description . "\n"
                . "UID:fitlytics-note-" . $note->id . "\n"
                . "STATUS:CONFIRMED\n"
                . "CREATED:" . date(ICAL_FORMAT, strtotime($note->created_at)) . "\n"
                . "LAST-MODIFIED:" . date(ICAL_FORMAT, strtotime($note->updated_at)) . "\n"
                . "LOCATION:\n"
                . "END:VEVENT\n";
        }
        $icalObject .= "END:VCALENDAR";

        header('Content-type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="fitlytics-training-journal.ics"');
        return $icalObject;
    }

    public function activitiesICS(ActivityModel $activities)
    {
        $activities = $activities->all();

        define('ICAL_FORMAT', 'Ymd\THis\Z');

        $icalObject = "BEGIN:VCALENDAR\n"
            . "VERSION:2.0\n"
            . "METHOD:PUBLISH\n"
            . "PRODID:-//Finn Le Sueur//Activities//EN\n";

        // loop over events
        foreach ($activities as $activity) {
            // dd($activity->start_date);
            $start_time = \Carbon\Carbon::parse($activity->start_date);
            $activity_end = \Carbon\Carbon::parse($activity->start_date)->addSeconds($activity->elapsed_time);
            // dd($start_time, $activity_end);
            // $activity_end = $activity_end->add(\DateInterval::createFromDateString($activity->elapsed_time . " seconds"));
            // $activity_end = \Carbon\Carbon::parse($activity_end)->toISOString();

            // $activity_end = new \DateTime($activity->start_date);
            // $activity_end = $activity_end->add(\DateInterval::createFromDateString($activity->elapsed_time . " seconds"));

            $dist = $activity->metersToKilometers($activity->distance, 2);
            $moving_time = $activity->secondsToHours($activity->moving_time);
            $elapsed_time = $activity->secondsToHours($activity->elapsed_time);
            $elevation = $activity->total_elevation_gain;

            if (isset($activity->activity_json()->average_heartrate)) {
                $avg_hr = $activity->activity_json()->average_heartrate;
                $max_hr = $activity->activity_json()->max_heartrate;
                $hr_string = "Heart Rate: {$avg_hr}bpm ($max_hr max)";
            } else {
                $hr_string = "Not Recorded";
            }
            

            if ($activity->type == "Run") {
                $avg_speed = $activity->metersPerSecondToMinPerKilometer($activity->activity_json()->average_speed);
                $max_speed = $activity->metersPerSecondToMinPerKilometer($activity->activity_json()->max_speed);
                $speed_unit = "min/km";
            } else {
                $avg_speed = $activity->metersPerSecondToKilometersPerHour($activity->activity_json()->average_speed);
                $max_speed = $activity->metersPerSecondToKilometersPerHour($activity->activity_json()->max_speed);
                $speed_unit = "km/hr";
            }

            $description = ""
                . "Distance: {$dist}km\\n"
                . "Time: {$moving_time} (Elapsed: {$elapsed_time})\\n"
                . "Elevation: {$elevation}m\\n"
                . "{$hr_string}\\n"
                . "Speed: {$avg_speed}{$speed_unit} ($max_speed max)";

            $icalObject .= ""
                . "BEGIN:VEVENT\n"
                . "TRANSP:OPAQUE\n"
                . "DTSTART:" . $start_time->format(ICAL_FORMAT) . "\n"
                . "DTEND:" . $activity_end->format(ICAL_FORMAT) . "\n"
                . "DTSTAMP:" . date(ICAL_FORMAT, strtotime($activity->updated_at)) . "\n"
                . "SUMMARY:" . $activity->activityTypeEmoji() . " " . $activity->name() . "\n"
                . "DESCRIPTION:" . $description . "\n"
                . "UID:fitlytics-activity-" . $activity->strava_id . "\n"
                . "STATUS:CONFIRMED\n"
                . "CREATED:" . date(ICAL_FORMAT, strtotime($activity->created_at)) . "\n"
                . "LAST-MODIFIED:" . date(ICAL_FORMAT, strtotime($activity->updated_at)) . "\n"
                . "LOCATION:\n"
                . "END:VEVENT\n";
        }

        // close calendar
        $icalObject .= "END:VCALENDAR";

        // Set the headers
        header('Content-type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="fitlytics-training-activities.ics"');

        echo $icalObject;
    }
}
