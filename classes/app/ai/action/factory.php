<?php

namespace local_mxaimanager\app\ai\action;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();
// @codeCoverageIgnoreEnd

class factory
{
    protected \local_mxaimanager\app\factory $base_factory;

    public function __construct(\local_mxaimanager\app\factory $base_factory)
    {
        $this->base_factory = $base_factory;
    }

    public function entity(array $record = []): entity
    {
        return new entity($record);
    }

    public function repository(): repository
    {
        return new repository($this->base_factory);
    }

    public function manager(): manager
    {
        return new manager($this->base_factory);
    }

    public function default_provider(): default_provider\factory
    {
        return new default_provider\factory($this->base_factory);
    }
}
