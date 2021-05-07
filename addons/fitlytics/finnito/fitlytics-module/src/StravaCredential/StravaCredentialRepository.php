<?php namespace Finnito\FitlyticsModule\StravaCredential;

use Finnito\FitlyticsModule\StravaCredential\Contract\StravaCredentialRepositoryInterface;
use Anomaly\Streams\Platform\Entry\EntryRepository;

class StravaCredentialRepository extends EntryRepository implements StravaCredentialRepositoryInterface
{

    /**
     * The entry model.
     *
     * @var StravaCredentialModel
     */
    protected $model;

    /**
     * Create a new StravaCredentialRepository instance.
     *
     * @param StravaCredentialModel $model
     */
    public function __construct(StravaCredentialModel $model)
    {
        $this->model = $model;
    }
}
