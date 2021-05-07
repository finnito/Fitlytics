<?php namespace Finnito\FitlyticsModule\Activity\Form;

use Anomaly\Streams\Platform\Ui\Form\FormBuilder;

class ActivityFormBuilder extends FormBuilder
{

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
        "type" => [
            "type" => "anomaly.field_type.text",
            "label" => "Type",
        ],
        "distance" => [
            "type" => "anomaly.field_type.text",
            "label" => "Distance",
        ],
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
    protected $options = [
        'redirect' => '/'
    ];

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
