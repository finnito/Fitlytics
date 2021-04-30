<?php namespace Finnito\FitlyticsModule\Plan;

use Finnito\FitlyticsModule\Plan\Contract\PlanRepositoryInterface;
use Anomaly\Streams\Platform\Entry\EntryRepository;

class PlanRepository extends EntryRepository implements PlanRepositoryInterface
{

    /**
     * The entry model.
     *
     * @var PlanModel
     */
    protected $model;

    /**
     * Create a new PlanRepository instance.
     *
     * @param PlanModel $model
     */
    public function __construct(PlanModel $model)
    {
        $this->model = $model;
    }
}
