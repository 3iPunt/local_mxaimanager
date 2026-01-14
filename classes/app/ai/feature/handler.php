<?php

namespace local_mxaimanager\app\ai\feature;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

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
    private entity $feature;

    public function __construct(base_factory $base_factory, entity $feature)
    {
        $this->base_factory = $base_factory;
        $this->feature = $feature;
        $this->provider_resolver = $this->base_factory->ai()->feature()->provider_resolver(
            $base_factory,
            $this->feature
        );
        $this->action_handler = $this->base_factory->ai()->feature()->action_handler($base_factory);
    }

    /**
     * @param message[] $messages
     * @param bool $json_mode Whether to enable JSON mode (forces the response to be valid JSON).
     * @param array|null $json_schema Optional JSON schema to enforce structured output (implies JSON mode).
     * @return string
     * @throws invalid_provider_instance_configuration
     * @throws invalid_provider_instance_response
     * @throws no_provider_instance_configured
     */
    public function chat_completion(array $messages, bool $json_mode = false, ?array $json_schema = null): string
    {
        // Get provider_id and settings_json for the action.
        [$provider_id, $config_json] = $this->provider_resolver->get_provider_and_config(
            \local_mxaimanager\app\ai\provider\providers\interfaces\chat_completion::class
        );

        return $this->action_handler->chat_completion(
            $this->feature,
            $messages,
            $json_mode,
            $json_schema,
            $provider_id,
            $config_json
        );
    }

    /**
     * @param string $input
     * @param int $dimension
     * @return float[]
     * @throws invalid_provider_instance_configuration
     * @throws invalid_provider_instance_response
     * @throws no_provider_instance_configured
     */
    public function create_embedding(string $input, int $dimension): array
    {
        // Get provider_id and settings_json for the action.
        [$provider_id, $config_json] = $this->provider_resolver->get_provider_and_config(
            \local_mxaimanager\app\ai\provider\providers\interfaces\create_embedding::class
        );

        return $this->action_handler->create_embedding($this->feature, $input, $dimension, $provider_id, $config_json);
    }

    /**
     * @param string $prompt
     * @param bool $return_b64
     * @return string
     * @throws invalid_provider_instance_configuration
     * @throws invalid_provider_instance_response
     * @throws no_provider_instance_configured
     */
    public function create_image(string $prompt, bool $return_b64 = false): string
    {
        // Get provider_id and settings_json for the action.
        [$provider_id, $config_json] = $this->provider_resolver->get_provider_and_config(
            \local_mxaimanager\app\ai\provider\providers\interfaces\create_image::class
        );

        return $this->action_handler->create_image(
            $this->feature,
            $prompt,
            $return_b64,
            $provider_id,
            $config_json
        );
    }
}
