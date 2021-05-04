<?php namespace Finnito\FitlyticsModule\Note\Table;

use Anomaly\Streams\Platform\Ui\Table\TableBuilder;

class NoteTableBuilder extends TableBuilder
{

    /**
     * The table views.
     *
     * @var array|string
     */
    protected $views = [];

    /**
     * The table filters.
     *
     * @var array|string
     */
    protected $filters = [];

    /**
     * The table columns.
     *
     * @var array|string
     */
    protected $columns = [
        'note'          => [
            'sort_column' => 'date',
            'wrapper'     => '
                    <strong>{value.date}</strong>
                    <br>
                    <span>{value.note}</span>',
            'value'       => [
                'date'     => 'entry.date',
                'note'   => 'entry.note',
            ],
        ],
    ];

    /**
     * The table buttons.
     *
     * @var array|string
     */
    protected $buttons = [
        'edit'
    ];

    /**
     * The table actions.
     *
     * @var array|string
     */
    protected $actions = [
        'delete'
    ];

    /**
     * The table options.
     *
     * @var array
     */
    protected $options = [
        "order_by" => [
            "date" => "desc",
        ],
    ];

    /**
     * The table assets.
     *
     * @var array
     */
    protected $assets = [];

}
