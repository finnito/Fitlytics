<?php namespace Finnito\FitlyticsModule\Activity\Contract;

use Anomaly\Streams\Platform\Entry\Contract\EntryRepositoryInterface;

interface ActivityRepositoryInterface extends EntryRepositoryInterface
{
    public function currentWeekStatistics();
    public function currentWeekStatisticsByType();
    public function weekBoundaries();
    public function thisWeek();
}
