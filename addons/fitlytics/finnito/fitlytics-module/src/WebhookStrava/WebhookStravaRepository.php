<?php namespace Finnito\FitlyticsModule\WebhookStrava;

use Finnito\FitlyticsModule\WebhookStrava\Contract\WebhookStravaRepositoryInterface;
use Anomaly\Streams\Platform\Entry\EntryRepository;

class WebhookStravaRepository extends EntryRepository implements WebhookStravaRepositoryInterface
{

    /**
     * The entry model.
     *
     * @var WebhookStravaModel
     */
    protected $model;

    /**
     * Create a new WebhookStravaRepository instance.
     *
     * @param WebhookStravaModel $model
     */
    public function __construct(WebhookStravaModel $model)
    {
        $this->model = $model;
    }
}
