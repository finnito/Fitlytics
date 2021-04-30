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
    }
}
