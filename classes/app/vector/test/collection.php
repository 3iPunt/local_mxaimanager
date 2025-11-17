<?php

namespace local_mxaimanager\app\vector\test;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();
// @codeCoverageIgnoreEnd

global $CFG;
require_once $CFG->libdir . '/filelib.php';


class collection implements \local_mxaimanager\app\vector\interfaces\db_collection
{
    public function create(string $name, int $dimension, bool $auto_increment_id): bool
    {
        return true;
    }

    public function delete(string $name): bool
    {
        return true;
    }

    public function exists(string $name): bool
    {
        return true;
    }

    public function list(): array
    {
        return [];
    }
}
