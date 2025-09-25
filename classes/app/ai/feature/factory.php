<?php

namespace local_mxaimanager\app\ai\feature;


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

    public function action(): action\factory
    {
        return new action\factory($this->base_factory);
    }

    public function handler(entity $feature): handler
    {
        return new handler($this->base_factory, $feature);
    }

    public function provider_resolver(\local_mxaimanager\app\factory $base_factory, entity $feature): provider_resolver
    {
        return new provider_resolver($base_factory, $feature);
    }

    public function action_handler(\local_mxaimanager\app\factory $base_factory): action_handler
    {
        return new action_handler($base_factory);
    }
}
