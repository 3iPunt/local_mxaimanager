<?php

namespace local_mxaimanager\app\ai\feature;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

use Exception;
use JsonException;
use local_mxaimanager\app\ai\provider\message;
use local_mxaimanager\app\ai\provider\providers\interfaces\chat_completion;
use local_mxaimanager\app\ai\provider\providers\interfaces\create_embedding;
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
     * @throws Exception
     * @throws JsonException
     */
    private function get_provider_handler_provider_and_settings_json(
        int $provider_id,
        array $config_json
    ): array {
        // Get the provider.
        $provider = $this->base_factory->ai()->provider()->repository()->get_by_id($provider_id);

        // Get the provider handler classname.
        $provider_handler_classname = $provider->get_classname();

        // Create the provider handler.
        return new $provider_handler_classname($this->base_factory, $config_json);
    }

    /**
     * @param message[] $messages
     * @param int $provider_id
     * @param string $settings_json
     * @return string
     * @throws JsonException
     * @throws Exception
     */
    public function chat_completion(array $messages, int $provider_id, array $config_json): string
    {
        // Get the provider handler.
        $handler = $this->get_provider_handler_provider_and_settings_json(
            $provider_id,
            $config_json
        );

        return $handler->chat_completion($messages);
    }

    /**
     * @param string $input
     * @param int $dimension
     * @param int $provider_id
     * @param string $settings_json
     * @return float[]
     * @throws JsonException
     * @throws Exception
     */
    public function create_embedding(string $input, int $dimension, int $provider_id, array $config_json): array
    {
        // Get the provider handler.
        $handler = $this->get_provider_handler_provider_and_settings_json(
            $provider_id,
            $config_json
        );

        return $handler->get_embedding($input, $dimension);
    }
}
