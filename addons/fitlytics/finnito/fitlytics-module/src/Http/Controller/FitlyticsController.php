<?php namespace Finnito\FitlyticsModule\Http\Controller;

use Anomaly\Streams\Platform\Http\Controller\PublicController;
use Finnito\FitlyticsModule\Activity\ActivityModel;
use Finnito\FitlyticsModule\Plan\PlanModel;
use Finnito\FitlyticsModule\Note\NoteModel;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FitlyticsController extends PublicController
{
    public function home(ActivityModel $activities)
    {
        $activities = $activities->query()
            ->whereYear("start_date", "2021")
            ->orderBy("start_date", "desc")
            ->get();

        $this->template->set("meta_title", "Home");

        return view(
            'finnito.module.fitlytics::pages/home',
            [
                'activities' => $activities
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
            $activity->title = $activity->activityTypeEmoji() . ": " . $activity->name;
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

    public function notesCalendarFeed(Request $request, NoteModel $notes)
    {
        $start = explode("T", $request->input("start"))[0];
        $end = explode("T", $request->input("end"))[0];
        $notes = $notes->query()
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

    public function plansCalendarFeed(Request $request, PlanModel $plans)
    {
        $start = explode("T", $request->input("start"))[0];
        $end = explode("T", $request->input("end"))[0];
        $plans = $plans->query()
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

    public function plansICS(PlanModel $plans)
    {
        define('ICAL_FORMAT', 'Ymd\THis\Z');

        $plans = $plans->all();

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

            $activity_end = new \DateTime($activity->start_date);
            $activity_end = $activity_end->add(\DateInterval::createFromDateString($activity->elapsed_time . " seconds"));

            $dist = $activity->metersToKilometers($activity->distance, 2);
            $moving_time = $activity->secondsToHours($activity->moving_time);
            $elapsed_time = $activity->secondsToHours($activity->elapsed_time);
            $elevation = $activity->total_elevation_gain;

            if (isset($activity->activity_json->average_heartrate)) {
                $avg_hr = $activity->activity_json->average_heartrate;
                $max_hr = $activity->activity_json->max_heartrate;
                $hr_string = "Heart Rate: {$avg_hr}bpm ($max_hr max)";
            } else {
                $hr_string = "Not Recorded";
            }
            

            if ($activity->type == "Run") {
                $avg_speed = $activity->metersPerSecondToMinPerKilometer($activity->activity_json->average_speed);
                $max_speed = $activity->metersPerSecondToMinPerKilometer($activity->activity_json->max_speed);
                $speed_unit = "min/km";
            } else {
                $avg_speed = $activity->metersPerSecondToKilometersPerHour($activity->activity_json->average_speed);
                $max_speed = $activity->metersPerSecondToKilometersPerHour($activity->activity_json->max_speed);
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
                . "DTSTART:" . date(ICAL_FORMAT, strtotime($activity->start_date)) . "\n"
                . "DTEND:" . $activity_end->format(ICAL_FORMAT) . "\n"
                . "DTSTAMP:" . date(ICAL_FORMAT, strtotime($activity->start_date)) . "\n"
                . "SUMMARY:" . $activity->name . "\n"
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
        header('Content-Disposition: attachment; filename="activities.ics"');

        echo $icalObject;
    }
}
