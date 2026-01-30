<?php

namespace local_mxaimanager\app\ai\provider\providers\interfaces;

// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

use local_mxaimanager\app\ai\provider\create_transcription_request;
use local_mxaimanager\app\exceptions\invalid_provider_instance_configuration;
use local_mxaimanager\app\exceptions\invalid_provider_instance_response;

interface create_transcription
{
    /**
     * @param string $audio_filepath Path to the audio file to transcribe. Supported formats depend on the provider.
     * @return create_transcription_request
     * @throws invalid_provider_instance_configuration
     * @throws invalid_provider_instance_response
     */
    public function create_transcription(
        string $audio_filepath
    ): create_transcription_request;
}
