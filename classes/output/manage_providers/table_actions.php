<?php

namespace local_mxaimanager\output\manage_providers;

use renderer_base;

class table_actions implements \renderable, \core\output\named_templatable
{
    private int $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function get_template_name(renderer_base $renderer): string
    {
        return 'local_mxaimanager/manage_providers/table_actions';
    }

    public function export_for_template(renderer_base $output): array
    {
        return [
            'id' => $this->id
        ];
    }
}
