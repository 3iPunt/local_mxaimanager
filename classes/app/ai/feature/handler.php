<?php

namespace local_mxaimanager\app\ai\feature;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

use Exception;
use JsonException;
use local_mxaimanager\app\ai\provider\message;
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
     * @throws JsonException
     * @throws Exception
     */
    public function chat_completion(array $messages): string
    {
        // Get the chat completion action.
        $action = $this->base_factory->ai()->action()->repository()->get_by_name(
            \local_mxaimanager\app\ai\action\manager::ACTION_CHAT_COMPLETION
        );

        // Get provider_id and settings_json for the action.
        [$provider_id, $settings_json] = $this->provider_resolver->get_provider_and_settings($action->get_id());

        return $this->action_handler->chat_completion($messages, $provider_id, $settings_json);
    }

    /**
     * @param string $input
     * @param int $dimension
     * @return float[]
     * @throws JsonException
     * @throws Exception
     */
    public function create_embedding(string $input, int $dimension): array
    {
        // Get the create embedding action.
        $action = $this->base_factory->ai()->action()->repository()->get_by_name(
            \local_mxaimanager\app\ai\action\manager::ACTION_CREATE_EMBEDDING
        );

        // Get provider_id and settings_json for the action.
        [$provider_id, $settings_json] = $this->provider_resolver->get_provider_and_settings($action->get_id());

        return $this->action_handler->create_embedding($input, $dimension, $provider_id, $settings_json);
    }
}
