<?php

namespace local_mxaimanager\app\controller\interfaces;

// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

interface view
{
    public function action(string $action): string;
}
