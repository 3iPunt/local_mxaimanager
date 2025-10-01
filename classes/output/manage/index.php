<?php

namespace local_mxaimanager\output\manage;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

use core\output\named_templatable;
use core\output\renderable;
use core\output\renderer_base;
use local_mxaimanager\app\factory as base_factory;

class index implements named_templatable, renderable
{
    private base_factory $base_factory;

    public function __construct(base_factory $base_factory)
    {
        $this->base_factory = $base_factory;
    }

    public function get_template_name(renderer_base $renderer): string
    {
        return 'local_mxaimanager/manage/index';
    }

    public function export_for_template(renderer_base $output): array
    {
        $no_providers = $this->base_factory->ai()->provider()->repository()->get_all()->empty();
        $no_default_providers = $this->base_factory->ai()->default_provider()->repository()->get_all()->empty();

        return [
            'general' => true,
            'no_providers' => $no_providers,
            'no_default_providers' => $no_default_providers
        ];
    }
}
