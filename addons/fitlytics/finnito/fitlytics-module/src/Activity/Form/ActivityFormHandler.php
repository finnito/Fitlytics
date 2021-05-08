<?php namespace Finnito\FitlyticsModule\Activity\Form;

use Finnito\FitlyticsModule\Activity\Form\ActivityFormBuilder;

class ActivityFormHandler
{

    public function handle(ActivityFormBuilder $builder)
    {
        if (!$builder->canSave()) {
            return;
        }

        $builder->setFormValue("activity_json", $builder->getForm()->getEntry()->activity_json);
        $builder->saveForm();
    }
}
