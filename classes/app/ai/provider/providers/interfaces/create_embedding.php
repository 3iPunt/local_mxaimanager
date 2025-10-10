<?php

namespace local_mxaimanager\app\ai\provider\providers\interfaces;

// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

use local_mxaimanager\app\exceptions\invalid_provider_instance_configuration;
use local_mxaimanager\app\exceptions\invalid_provider_instance_response;

interface create_embedding
{
    /**
     * @param string $input
     * @param ?int $dimension
     * @return float[]
     * @throws invalid_provider_instance_configuration
     * @throws invalid_provider_instance_response
     */
    public function get_embedding(string $input, ?int $dimension): array;
}
