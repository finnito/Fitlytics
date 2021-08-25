<?php namespace Finnito\FitlyticsModule\Plan\Form;

use Anomaly\Streams\Platform\Ui\Form\FormBuilder;

class PlanFormBuilder extends FormBuilder
{

    /**
     * The form fields.
     *
     * @var array|string
     */
    protected $fields = [];

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
    protected $actions = [
        'save' => [
            'redirect' => '/{entry.date}#{entry.date}'
        ],
    ];

    /**
     * The form buttons.
     *
     * @var array|string
     */
    protected $buttons = [];

    /**
     * The form options.
     *
     * @var array
     */
    protected $options = [
        "class" => "pure-form",
        "redirect" => "/",
    ];

    /**
     * The form sections.
     *
     * @var array
     */
    protected $sections = [
        'note' => [
            'view'  => 'finnito.module.fitlytics::form/plan',
        ],
    ];

    /**
     * The form assets.
     *
     * @var array
     */
    protected $assets = [
        'scripts.js' => [
            "finnito.module.fitlytics::js/form-helpers.js",
        ],
    ];

}
