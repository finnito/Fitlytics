<?php namespace Finnito\FitlyticsModule\User\Form;

use Anomaly\Streams\Platform\Ui\Form\FormBuilder;

class UserHRZonesFormBuilder extends FormBuilder
{
    protected $model = \Anomaly\UsersModule\User\UserModel::class;

    /**
     * The form fields.
     *
     * @var array|string
     */
    protected $fields = [
        // "resting_heart_rate",
        // "maximum_heart_rate",
        "z1","z2","z3","z4","z5",
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
    protected $skips = [
        // "sort_order", "str_id",
    ];

    /**
     * The form actions.
     *
     * @var array|string
     */
    protected $actions = ["update"];

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
