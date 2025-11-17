<?php

namespace local_mxaimanager\app;

// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

class factory
{
    private static self $instance;
    private array $instances = [];

    public static function make(): self
    {
        return self::$instance ??= new static();
    }

    public function db(): \moodle_database
    {
        global $DB;
        return $DB;
    }

    public function page(): \moodle_page
    {
        global $PAGE;
        return $PAGE;
    }

    public function output(): \bootstrap_renderer|\core_renderer
    {
        global $OUTPUT;
        return $OUTPUT;
    }

    public function collection(array $records = []): collection
    {
        return new collection($records);
    }

    public function ai(): ai\factory
    {
        return $this->instances[__FUNCTION__] ??= new ai\factory($this);
    }

    public function vector(): vector\factory
    {
        return $this->instances[__FUNCTION__] ??= new vector\factory($this);
    }

    public function cfg(): object {
        global $CFG;
        return $CFG;
    }

    public function curl(): \curl
    {
        return new \curl();
    }

    public function controller(): controller\factory
    {
        return new controller\factory($this);
    }
}
