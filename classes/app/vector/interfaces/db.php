<?php

namespace local_mxaimanager\app\vector\interfaces;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();
// @codeCoverageIgnoreEnd

interface db
{
    public function collection(): db_collection;
    public function vector(): db_vector;
}
