<?php namespace Finnito\FitlyticsModule;

use Anomaly\Streams\Platform\Addon\Module\Module;

class FitlyticsModule extends Module
{

    /**
     * The navigation display flag.
     *
     * @var bool
     */
    protected $navigation = true;

    /**
     * The addon icon.
     *
     * @var string
     */
    protected $icon = 'fa fa-puzzle-piece';

    /**
     * The module sections.
     *
     * @var array
     */
    protected $sections = [
        'activities' => [
            'buttons' => [
                'new_activity',
            ],
        ],
        'plans' => [
            'buttons' => [
                'new_plan',
            ],
        ],
        'notes' => [
            'buttons' => [
                'new_note',
            ],
        ],
    ];

}
