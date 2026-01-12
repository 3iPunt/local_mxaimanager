<?php

namespace local_mxaimanager\app\ai\feature;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

use local_mxaimanager\app\ai\provider\message;
use local_mxaimanager\app\ai\provider\providers\interfaces\chat_completion;
use local_mxaimanager\app\ai\provider\providers\interfaces\create_embedding;
use local_mxaimanager\app\exceptions\invalid_provider_instance_configuration;
use local_mxaimanager\app\exceptions\invalid_provider_instance_response;
use local_mxaimanager\app\factory as base_factory;

class action_handler
{
    private base_factory $base_factory;

    public function __construct(base_factory $base_factory)
    {
        $this->base_factory = $base_factory;
    }

    /**
     * @param int $provider_id
     * @param array $config_json
     * @return chat_completion|create_embedding
     */
    protected function get_provider_handler_provider_and_settings_json(
        int $provider_id,
        array $config_json
    ): mixed {
        // Get the provider.
        $provider = $this->base_factory->ai()->provider()->repository()->get_by_id($provider_id);

        // Get the provider handler classname.
        $provider_handler_classname = $provider->get_classname();

        // Create the provider handler.
        return new $provider_handler_classname($this->base_factory, $config_json);
    }

    /**
     * @param message[] $messages
     * @param bool $json_mode Whether to enable JSON mode (forces the response to be valid JSON).
     * @param array|null $json_schema Optional JSON schema to enforce structured output (implies JSON mode).
     * @param int $provider_id
     * @param array $config_json
     * @return string
     * @throws invalid_provider_instance_configuration
     * @throws invalid_provider_instance_response
     */
    public function chat_completion(
        array $messages,
        bool $json_mode,
        ?array $json_schema,
        int $provider_id,
        array $config_json
    ): string {
        // Get the provider handler.
        $handler = $this->get_provider_handler_provider_and_settings_json(
            $provider_id,
            $config_json
        );

        // Validate interface.
        if (!($handler instanceof chat_completion)) {
            throw new invalid_provider_instance_configuration(
                'Provider instance ID: ' . $provider_id . ' does not support chat completion'
            );
        }

        return $handler->chat_completion($messages, $json_mode, $json_schema);
    }

    /**
     * @param string $input
     * @param int $dimension
     * @param int $provider_id
     * @param array $config_json
     * @return float[]
     * @throws invalid_provider_instance_configuration
     * @throws invalid_provider_instance_response
     */
    public function create_embedding(string $input, int $dimension, int $provider_id, array $config_json): array
    {
        // Get the provider handler.
        $handler = $this->get_provider_handler_provider_and_settings_json(
            $provider_id,
            $config_json
        );

        // Validate interface.
        if (!($handler instanceof create_embedding)) {
            throw new invalid_provider_instance_configuration(
                'Provider instance ID: ' . $provider_id . ' does not support embedding creation'
            );
        }

        return $handler->get_embedding($input, $dimension);
    }
}
