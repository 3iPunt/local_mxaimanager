<?php

namespace local_mxaimanager\output\manage_features;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

use local_mxaimanager\app\factory as base_factory;

class table extends \local_mxaimanager\app\table
{
    private base_factory $base_factory;

    public function __construct(base_factory $base_factory, \moodle_url $url)
    {
        $this->base_factory = $base_factory;

        $headers = [];
        $columns = [];

        $columns[] = 'component';
        $headers[] = get_string('manage_features:table:component', 'local_mxaimanager');

        $columns[] = 'name';
        $headers[] = get_string('manage_features:table:name', 'local_mxaimanager');

        $columns[] = 'description';
        $headers[] = get_string('manage_features:table:description', 'local_mxaimanager');

        $columns[] = 'ai_actions';
        $headers[] = get_string('manage_features:table:ai_actions', 'local_mxaimanager');
        $this->no_sorting('ai_actions');

        $columns[] = 'actions';
        $headers[] = get_string('manage_features:table:actions', 'local_mxaimanager');
        $this->no_sorting('actions');

        $this->define_columns($columns);
        $this->define_headers($headers);
        $this->collapsible(false);
        $this->sortable(true);

        $this->set_sql("*", "{local_mxaimanager_features}", "1=1");

        parent::__construct($url, "manage_features_table");
    }

    protected function col_component(object $record): string
    {
        return get_string('pluginname', $record->component) . ' (' . $record->component . ')';
    }

    protected function col_name(object $record): string
    {
        return get_string($record->name_identifier, $record->component);
    }

    protected function col_description(object $record): string
    {
        return get_string($record->description_identifier, $record->component);
    }

    protected function col_ai_actions(object $record): string
    {
        return $this->base_factory->output()->render(
            new table_ai_actions($this->base_factory, $record->id)
        );
    }

    protected function col_actions(object $record): string
    {
        return $this->base_factory->output()->render(
            new table_actions($record->id)
        );
    }
}
