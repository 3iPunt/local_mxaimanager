<?php

namespace local_mxaimanager\app\vector\elasticsearch;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();
// @codeCoverageIgnoreEnd

use local_mxaimanager\app\vector;
use local_mxaimanager\app\factory as base_factory;

class factory implements vector\interfaces\db
{
    private base_factory $base_factory;
    private vector\db_config $db_config;

    public function __construct(base_factory $base_factory, vector\db_config $db_config)
    {
        $this->base_factory = $base_factory;
        $this->db_config = $db_config;
    }

    public function vector(): vector\interfaces\db_vector
    {
        return new vector\elasticsearch\vector($this->base_factory, $this->db_config);
    }
}
