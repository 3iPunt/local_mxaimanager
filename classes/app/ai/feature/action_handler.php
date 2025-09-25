<?php

namespace local_mxaimanager\app\ai\feature;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

use Exception;
use JsonException;
use local_mxaimanager\app\ai\provider\entity as provider_entity;
use local_mxaimanager\app\ai\provider\message;
use local_mxaimanager\app\factory as base_factory;

class action_handler
{
    private base_factory $base_factory;

    public function __construct(base_factory $base_factory)
    {
        $this->base_factory = $base_factory;
    }

    /**
     * @template T of \local_mxaimanager\app\ai\provider\providers\interfaces\chat_completion|\local_mxaimanager\app\ai\provider\providers\interfaces\create_embedding
     * @param int $provider_id
     * @param class-string<T> $interface
     * @return array{0: T, 1: provider_entity}
     * @throws Exception
     * @throws JsonException
     */
    private function get_provider_handler_provider_and_settings_json(
        int $provider_id,
        string $interface
    ): array {
        // Get the provider
        $provider = $this->base_factory->ai()->provider()->repository()->get_by_id($provider_id);

        // Get the provider handler classname.
        $provider_handler_classname = $provider->get_classname();

        // Decode the provider config.
        $provider_config = json_decode($provider->get_config_json() ?? '{}', true, 512, JSON_THROW_ON_ERROR);

        // Create the provider handler.
        $handler = new $provider_handler_classname($this->base_factory, $provider_config);

        // Ensure the handler supports the interface.
        if (!($handler instanceof $interface)) {
            throw new Exception("Provider ID {$provider_id} does not support the required interface");
        }

        return [$handler, $provider];
    }

    /**
     * @param message[] $messages
     * @param int $provider_id
     * @param string $settings_json
     * @return string
     * @throws JsonException
     * @throws Exception
     */
    public function chat_completion(array $messages, int $provider_id, string $settings_json): string
    {
        // Get the provider handler.
        [$handler, $provider] = $this->get_provider_handler_provider_and_settings_json(
            $provider_id,
            \local_mxaimanager\app\ai\provider\providers\interfaces\chat_completion::class
        );

        // Decode the settings json.
        $settings = json_decode($settings_json, true, 512, JSON_THROW_ON_ERROR);

        // Ensure a model is configured.
        if (!isset($settings['model'])) {
            throw new Exception("No model configured for chat completion with provider ID {$provider->get_id()}");
        }

        return $handler->chat_completion($messages, $settings['model']);
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
    public function create_embedding(string $input, int $dimension, int $provider_id, string $settings_json): array
    {
        // Get the provider handler.
        [$handler, $provider] = $this->get_provider_handler_provider_and_settings_json(
            $provider_id,
            \local_mxaimanager\app\ai\provider\providers\interfaces\create_embedding::class
        );

        // Decode the settings json.
        $settings = json_decode($settings_json, true, 512, JSON_THROW_ON_ERROR);

        // Ensure a model is configured.
        if (!isset($settings['model'])) {
            throw new Exception("No model configured for embedding creation with provider ID {$provider->get_id()}");
        }

        return $handler->get_embedding($input, $settings['model'], $dimension);
    }
}
