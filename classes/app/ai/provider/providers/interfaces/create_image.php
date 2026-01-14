<?php

namespace local_mxaimanager\app\ai\provider\providers\interfaces;

// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

use local_mxaimanager\app\ai\provider\image_generation_request;
use local_mxaimanager\app\exceptions\invalid_provider_instance_configuration;
use local_mxaimanager\app\exceptions\invalid_provider_instance_response;

interface create_image
{
    /**
     * @param string $prompt The text prompt to generate the image from.
     * @param bool $return_b64 Whether to return the image as a base64 string. Default is false. If false, returns a URL to the image instead.
     * @return image_generation_request
     * @throws invalid_provider_instance_configuration
     * @throws invalid_provider_instance_response
     */
    public function create_image(
        string $prompt,
        bool $return_b64 = false
    ): image_generation_request;
}
