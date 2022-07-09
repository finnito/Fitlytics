<?php namespace Finnito\FitlyticsModule\Activity\Form;

use Anomaly\Streams\Platform\Ui\Form\FormBuilder;
use Finnito\FitlyticsModule\Strava\Strava;
use Finnito\FitlyticsModule\Activity\ActivityModel;

class ActivityFormBuilder extends FormBuilder
{

    public function onSaved()
    {
        $changed = [];
        $model = ActivityModel::where("strava_id", $this->getFormEntry()->strava_id)->get()->first();
        if ($this->getFormEntry()->wasChanged("name")) {
            $changed["name"]        = $this->getFormEntry()->name;
        }
        if ($this->getFormEntry()->wasChanged("description")) {
            $changed["description"]        = $this->getFormEntry()->name;
        }

        $strava = new Strava();
        $resp = $strava->put(
            "/activities/{$model->strava_id}",
            $changed
        );

        $this->setFormResponse(redirect('/' . $this->getFormEntry()->ymdDate() . "#" . $this->getFormEntry()->ymdDate()));
    }

    /**
     * The form fields.
     *
     * @var array|string
     */
    protected $fields = [
        "name" => [
            "type" => "anomaly.field_type.text",
            "label" => "Name",
        ],
        "activity_json->description" => [
            "type" => "anomaly.field_type.text",
            "label" => "Description",
        ],
        // "moving_time",
        // "elapsed_time",
    ];

    /**
     * Additional validation rules.
     *
     * @var array|string
     */
    protected $rules = [];

    /**
     * Fields to skip.
     *
     * @var array|string
     */
    protected $skips = [];

    /**
     * The form actions.
     *
     * @var array|string
     */
    protected $actions = [];

    /**
     * The form buttons.
     *
     * @var array|string
     */
    protected $buttons = [
        'cancel',
    ];

    /**
     * The form options.
     *
     * @var array
     */
    protected $options = [];

    /**
     * The form sections.
     *
     * @var array
     */
    protected $sections = [];

    /**
     * The form assets.
     *
     * @var array
     */
    protected $assets = [];

}
