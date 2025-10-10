<?php

namespace local_mxaimanager\app\ai\feature;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

use Exception;
use JsonException;
use local_mxaimanager\app\ai\provider\message;
use local_mxaimanager\app\exceptions\invalid_provider_instance_configuration;
use local_mxaimanager\app\exceptions\invalid_provider_instance_response;
use local_mxaimanager\app\exceptions\no_provider_instance_configured;
use local_mxaimanager\app\factory as base_factory;

class handler
{
    private base_factory $base_factory;
    private provider_resolver $provider_resolver;
    private action_handler $action_handler;

    public function __construct(base_factory $base_factory, entity $feature)
    {
        $this->base_factory = $base_factory;
        $this->provider_resolver = $this->base_factory->ai()->feature()->provider_resolver($base_factory, $feature);
        $this->action_handler = $this->base_factory->ai()->feature()->action_handler($base_factory);
    }

    /**
     * @param message[] $messages
     * @return string
     * @throws invalid_provider_instance_configuration
     * @throws invalid_provider_instance_response
     * @throws no_provider_instance_configured
     */
    public function chat_completion(array $messages): string
    {
        // Get provider_id and settings_json for the action.
        [$provider_id, $config_json] = $this->provider_resolver->get_provider_and_config(
            \local_mxaimanager\app\ai\provider\providers\interfaces\chat_completion::class
        );

        return $this->action_handler->chat_completion($messages, $provider_id, $config_json);
    }

    /**
     * @param string $input
     * @param int $dimension
     * @return float[]
     * @throws invalid_provider_instance_configuration
     * @throws invalid_provider_instance_response
     */
    public function create_embedding(string $input, int $dimension): array
    {
        // Get provider_id and settings_json for the action.
        [$provider_id, $config_json] = $this->provider_resolver->get_provider_and_config(
            \local_mxaimanager\app\ai\provider\providers\interfaces\create_embedding::class
        );

        return $this->action_handler->create_embedding($input, $dimension, $provider_id, $config_json);
    }
}
