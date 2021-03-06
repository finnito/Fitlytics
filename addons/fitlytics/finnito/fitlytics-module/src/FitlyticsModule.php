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
    protected $icon = 'glyphicons glyphicons-calendar';

    /**
     * The module sections.
     *
     * @var array
     */
    protected $sections = [
        'activities' => [],
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
        'strava_credentials' => [
            'buttons' => [
                'new_strava_credential',
            ],
        ],
        'webhook_strava' => [
            'buttons' => [
                'new_webhook_strava',
            ],
        ],
    ];

}
