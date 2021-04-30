<?php namespace Finnito\FitlyticsModule\Plan;

use Finnito\FitlyticsModule\Plan\Contract\PlanInterface;
use Anomaly\Streams\Platform\Model\Fitlytics\FitlyticsPlansEntryModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PlanModel extends FitlyticsPlansEntryModel implements PlanInterface
{
    use HasFactory;

    /**
     * @return PlanFactory
     */
    protected static function newFactory()
    {
        return PlanFactory::new();
    }
}
