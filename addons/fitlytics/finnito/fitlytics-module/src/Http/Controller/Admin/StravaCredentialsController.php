<?php namespace Finnito\FitlyticsModule\Http\Controller\Admin;

use Finnito\FitlyticsModule\StravaCredential\Form\StravaCredentialFormBuilder;
use Finnito\FitlyticsModule\StravaCredential\Table\StravaCredentialTableBuilder;
use Anomaly\Streams\Platform\Http\Controller\AdminController;

class StravaCredentialsController extends AdminController
{

    /**
     * Display an index of existing entries.
     *
     * @param StravaCredentialTableBuilder $table
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(StravaCredentialTableBuilder $table)
    {
        return $table->render();
    }

    /**
     * Create a new entry.
     *
     * @param StravaCredentialFormBuilder $form
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(StravaCredentialFormBuilder $form)
    {
        return $form->render();
    }

    /**
     * Edit an existing entry.
     *
     * @param StravaCredentialFormBuilder $form
     * @param        $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(StravaCredentialFormBuilder $form, $id)
    {
        return $form->render($id);
    }
}
