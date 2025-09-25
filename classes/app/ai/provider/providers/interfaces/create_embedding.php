<?php

namespace local_mxaimanager\app\ai\provider\providers\interfaces;

// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();
// @codeCoverageIgnoreEnd

interface create_embedding
{
    /**
     * @param string $input
     * @param string $model
     * @param int $dimension
     * @return float[]
     */
    public function get_embedding(string $input, string $model, int $dimension): array;
}
