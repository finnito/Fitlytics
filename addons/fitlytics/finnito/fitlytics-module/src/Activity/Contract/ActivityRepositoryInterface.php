<?php namespace Finnito\FitlyticsModule\Activity\Contract;

use Anomaly\Streams\Platform\Entry\Contract\EntryRepositoryInterface;

interface ActivityRepositoryInterface extends EntryRepositoryInterface
{
    public function currentWeekStatistics($week_of);
    public function currentWeekStatisticsByType($week_of);
    public function weekBoundaries();
    public function thisWeek();
}
