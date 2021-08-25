<?php namespace Finnito\FitlyticsModule\Http\Controller\Admin;

use Finnito\FitlyticsModule\WebhookStrava\Form\WebhookStravaFormBuilder;
use Finnito\FitlyticsModule\WebhookStrava\Table\WebhookStravaTableBuilder;
use Anomaly\Streams\Platform\Http\Controller\AdminController;

class WebhookStravaController extends AdminController
{

    /**
     * Display an index of existing entries.
     *
     * @param WebhookStravaTableBuilder $table
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(WebhookStravaTableBuilder $table)
    {
        return $table->render();
    }

    /**
     * Create a new entry.
     *
     * @param WebhookStravaFormBuilder $form
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(WebhookStravaFormBuilder $form)
    {
        return $form->render();
    }

    /**
     * Edit an existing entry.
     *
     * @param WebhookStravaFormBuilder $form
     * @param        $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(WebhookStravaFormBuilder $form, $id)
    {
        return $form->render($id);
    }
}
