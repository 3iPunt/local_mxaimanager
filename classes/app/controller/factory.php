<?php

namespace local_mxaimanager\app\controller;

// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

use local_mxaimanager\app\factory as base_factory;

class factory
{
    private base_factory $base_factory;

    public function __construct(base_factory $base_factory)
    {
        $this->base_factory = $base_factory;
    }

    public function view(string $view): interfaces\view
    {
        switch ($view) {
            case 'manage':
                return new manage($this->base_factory);
            case 'manage_providers':
                return new manage_providers($this->base_factory);
            case 'manage_features':
                return new manage_features($this->base_factory);
            default:
                send_file_not_found();
        }
    }
}
