<?php

namespace local_mxaimanager\app\ai\provider\providers\interfaces;

// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

use local_mxaimanager\app\ai\provider\message;
use local_mxaimanager\app\exceptions\invalid_provider_instance_configuration;
use local_mxaimanager\app\exceptions\invalid_provider_instance_response;

interface chat_completion
{
    /**
     * @param message[] $messages
     * @return string
     * @throws invalid_provider_instance_configuration
     * @throws invalid_provider_instance_response
     */
    public function chat_completion(array $messages): string;
}
