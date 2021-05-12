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

    public function currentWeekStatistics()
    {
        $now = \Carbon\Carbon::now("Pacific/Auckland");
        $offset = \Carbon\Carbon::createFromTimestamp(0, "Pacific/Auckland")->getTimezone()->toOffsetName();
        return $this->model->query()
            ->selectRaw("SUM(distance) AS distance, SUM(total_elevation_gain) as elevation, SUM(moving_time) as moving_time")
            ->whereRaw(
                "CONVERT_TZ(STR_TO_DATE(start_date, '%Y-%m-%dT%H:%i:%sZ'), '+00:00','"
                . $offset
                . "') BETWEEN '"
                . $now->startOfWeek()->format("Y-m-d H:i:s")
                . "' AND '"
                . $now->endOfWeek()->format("Y-m-d H:i:s")
                . "'"
            )
            ->first();
    }

    public function currentWeekStatisticsByType()
    {
        $now = \Carbon\Carbon::now("Pacific/Auckland");
        $offset = \Carbon\Carbon::createFromTimestamp(0, "Pacific/Auckland")->getTimezone()->toOffsetName();
        return $this->model->query()
            ->selectRaw("type, SUM(distance) AS distance, SUM(total_elevation_gain) as elevation, SUM(moving_time) as moving_time")
            ->whereRaw(
                "CONVERT_TZ(STR_TO_DATE(start_date, '%Y-%m-%dT%H:%i:%sZ'), '+00:00','"
                . $offset
                . "') BETWEEN '"
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
                "CONVERT_TZ(STR_TO_DATE(start_date, '%Y-%m-%dT%H:%i:%sZ'), '+00:00','"
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
}
