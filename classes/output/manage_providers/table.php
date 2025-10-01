<?php

namespace local_mxaimanager\output\manage_providers;


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

        $columns[] = 'name';
        $headers[] = get_string('manage_providers:table:name', 'local_mxaimanager');

        $columns[] = 'type';
        $headers[] = get_string('manage_providers:table:type', 'local_mxaimanager');

        $columns[] = 'supported_actions';
        $headers[] = get_string('manage_providers:table:supported_actions', 'local_mxaimanager');
        $this->no_sorting('supported_actions');

        $columns[] = 'actions';
        $headers[] = get_string('manage_providers:table:actions', 'local_mxaimanager');
        $this->no_sorting('actions');

        $this->define_columns($columns);
        $this->define_headers($headers);
        $this->collapsible(false);
        $this->sortable(true);

        $this->set_sql("*", "{local_mxaimanager_providers}", "1=1");

        parent::__construct($url, "manage_providers_table");
    }

    protected function col_type(object $record): string
    {
        $providers = $this->base_factory->ai()->provider()->get_providers();

        foreach ($providers as $provider => $name) {
            if ($record->classname === $provider) {
                return $name;
            }
        }

        return '';
    }

    protected function col_supported_actions(object $record): string
    {
        return $this->base_factory->output()->render(
            new form_provider_supports($record->classname)
        );
    }

    protected function col_actions(object $record): string
    {
        return $this->base_factory->output()->render(
            new table_actions($record->id)
        );
    }
}
