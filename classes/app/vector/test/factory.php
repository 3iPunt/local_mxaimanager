<?php

namespace local_mxaimanager\app\vector\test;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();
// @codeCoverageIgnoreEnd

use local_mxaimanager\app\factory as base_factory;
use local_mxaimanager\app\vector;

class factory implements vector\interfaces\db
{
    private base_factory $base_factory;

    public function __construct(base_factory $base_factory)
    {
        $this->base_factory = $base_factory;
    }

    public function collection(): vector\interfaces\db_collection
    {
        return new collection();
    }

    public function vector(): vector\interfaces\db_vector
    {
        return new vector\test\vector();
    }
}
