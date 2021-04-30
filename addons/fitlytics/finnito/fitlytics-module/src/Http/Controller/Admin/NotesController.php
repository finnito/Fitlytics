<?php namespace Finnito\FitlyticsModule\Http\Controller\Admin;

use Finnito\FitlyticsModule\Note\Form\NoteFormBuilder;
use Finnito\FitlyticsModule\Note\Table\NoteTableBuilder;
use Anomaly\Streams\Platform\Http\Controller\AdminController;

class NotesController extends AdminController
{

    /**
     * Display an index of existing entries.
     *
     * @param NoteTableBuilder $table
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(NoteTableBuilder $table)
    {
        return $table->render();
    }

    /**
     * Create a new entry.
     *
     * @param NoteFormBuilder $form
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(NoteFormBuilder $form)
    {
        return $form->render();
    }

    /**
     * Edit an existing entry.
     *
     * @param NoteFormBuilder $form
     * @param        $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(NoteFormBuilder $form, $id)
    {
        return $form->render($id);
    }
}
