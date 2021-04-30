<?php namespace Finnito\FitlyticsModule\Note;

use Finnito\FitlyticsModule\Note\Contract\NoteRepositoryInterface;
use Anomaly\Streams\Platform\Entry\EntryRepository;

class NoteRepository extends EntryRepository implements NoteRepositoryInterface
{

    /**
     * The entry model.
     *
     * @var NoteModel
     */
    protected $model;

    /**
     * Create a new NoteRepository instance.
     *
     * @param NoteModel $model
     */
    public function __construct(NoteModel $model)
    {
        $this->model = $model;
    }
}
