<?php namespace Finnito\FitlyticsModule\Http\Controller\Admin;

use Finnito\FitlyticsModule\Plan\Form\PlanFormBuilder;
use Finnito\FitlyticsModule\Plan\Table\PlanTableBuilder;
use Anomaly\Streams\Platform\Http\Controller\AdminController;

class PlansController extends AdminController
{

    /**
     * Display an index of existing entries.
     *
     * @param PlanTableBuilder $table
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(PlanTableBuilder $table)
    {
        return $table->render();
    }

    /**
     * Create a new entry.
     *
     * @param PlanFormBuilder $form
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(PlanFormBuilder $form)
    {
        return $form->render();
    }

    /**
     * Edit an existing entry.
     *
     * @param PlanFormBuilder $form
     * @param        $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(PlanFormBuilder $form, $id)
    {
        return $form->render($id);
    }
}
