<?php namespace Finnito\FitlyticsModule\Http\Controller\Admin;

use Finnito\FitlyticsModule\Activity\Form\ActivityFormBuilder;
use Finnito\FitlyticsModule\Activity\Table\ActivityTableBuilder;
use Anomaly\Streams\Platform\Http\Controller\AdminController;

class ActivitiesController extends AdminController
{

    /**
     * Display an index of existing entries.
     *
     * @param ActivityTableBuilder $table
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(ActivityTableBuilder $table)
    {
        return $table->render();
    }

    /**
     * Create a new entry.
     *
     * @param ActivityFormBuilder $form
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(ActivityFormBuilder $form)
    {
        return $form->render();
    }

    /**
     * Edit an existing entry.
     *
     * @param ActivityFormBuilder $form
     * @param        $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(ActivityFormBuilder $form, $id)
    {
        return $form->render($id);
    }
}
