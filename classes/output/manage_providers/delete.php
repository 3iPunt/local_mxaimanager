<?php

namespace local_mxaimanager\output\manage_providers;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

use core\output\named_templatable;
use core\output\renderable;
use core\output\renderer_base;
use local_mxaimanager\app\factory as base_factory;

class delete implements named_templatable, renderable
{
    private base_factory $base_factory;
    private int $provider_id;

    public function __construct(base_factory $base_factory, int $provider_id)
    {
        $this->base_factory = $base_factory;
        $this->provider_id = $provider_id;
    }

    public function get_template_name(renderer_base $renderer): string
    {
        return 'local_mxaimanager/manage_providers/delete';
    }

    public function export_for_template(renderer_base $output): array
    {
        $provider = $this->base_factory->ai()->provider()->repository()->get_by_id($this->provider_id);

        return [
            'providers' => true,
            'id' => $this->provider_id,
            'name' => $provider->get_name(),
        ];
    }
}
