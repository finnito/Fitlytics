<?php namespace Finnito\FitlyticsModule\Note\Form;

use Anomaly\Streams\Platform\Ui\Form\FormBuilder;

class NoteFormBuilder extends FormBuilder
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
        "save",
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
            'view'  => 'finnito.module.fitlytics::form/note',
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
