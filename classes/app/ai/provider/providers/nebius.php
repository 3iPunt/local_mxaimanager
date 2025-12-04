<?php

namespace local_mxaimanager\app\ai\provider\providers;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();
// @codeCoverageIgnoreEnd

use local_mxaimanager\app\exceptions\invalid_provider_instance_configuration;
use local_mxaimanager\app\exceptions\invalid_provider_instance_response;
use local_mxaimanager\app\factory as base_factory;

class nebius extends provider implements interfaces\chat_completion, interfaces\create_embedding
{
    private \curl $curl;
    private string $base_url;
    private string $api_key;
    private string $chat_model;
    private string $embedding_model;

    /**
     * @throws invalid_provider_instance_configuration
     */
    public function __construct(base_factory $base_factory, array $json_config)
    {
        $this->base_factory = $base_factory;
        $this->base_url = $json_config['base_url'] ?? '';
        $this->api_key = $json_config['api_key'] ?? '';
        $this->chat_model = $json_config['chat_model'] ?? '';
        $this->embedding_model = $json_config['embedding_model'] ?? '';

        if (empty($this->base_url) || empty($this->api_key)) {
            throw new invalid_provider_instance_configuration('Nebius is missing base url and/or api key');
        }

        $this->curl = $this->base_factory->curl();
        $this->curl->setHeader([
            "Authorization: Bearer {$this->api_key}",
            'Content-Type: application/json'
        ]);
    }

    private static function add_chat_model_field(\MoodleQuickForm $mform, string $element_name_prefix): void
    {
        $mform->addElement(
            'text',
            "{$element_name_prefix}chat_model",
            get_string('default_chat_model', 'local_mxaimanager')
        );
        $mform->setType("{$element_name_prefix}chat_model", PARAM_TEXT);
        $mform->addHelpButton("{$element_name_prefix}chat_model", 'nebius_chat_model', 'local_mxaimanager');
    }

    private static function add_embedding_model_field(\MoodleQuickForm $mform, string $element_name_prefix): void
    {
        $mform->addElement(
            'text',
            "{$element_name_prefix}embedding_model",
            get_string('default_embedding_model', 'local_mxaimanager')
        );
        $mform->setType("{$element_name_prefix}embedding_model", PARAM_TEXT);
        $mform->addHelpButton("{$element_name_prefix}embedding_model", 'nebius_embedding_model', 'local_mxaimanager');
    }

    public static function moodleform_definition(\MoodleQuickForm $mform, string $element_name_prefix): void
    {
        // Add base_url field
        $mform->addElement('text', "{$element_name_prefix}base_url", get_string('base_url', 'local_mxaimanager'));
        $mform->setType("{$element_name_prefix}base_url", PARAM_URL);
        $mform->setDefault("{$element_name_prefix}base_url", 'https://api.tokenfactory.nebius.com');

        // Add api_key field
        $mform->addElement('text', "{$element_name_prefix}api_key", get_string('api_key', 'local_mxaimanager'));
        $mform->setType("{$element_name_prefix}api_key", PARAM_TEXT);
        $mform->setDefault("{$element_name_prefix}api_key", '');

        // Add chat model field
        self::add_chat_model_field($mform, $element_name_prefix);

        // Add embedding model field
        self::add_embedding_model_field($mform, $element_name_prefix);
    }

    public static function moodleform_validation(array $data, string $element_name_prefix): array
    {
        $errors = [];

        if (empty($data["{$element_name_prefix}base_url"])) {
            $errors["{$element_name_prefix}base_url"] = get_string('required');
        }

        if (empty($data["{$element_name_prefix}api_key"])) {
            $errors["{$element_name_prefix}api_key"] = get_string('required');
        }

        if (empty($data["{$element_name_prefix}chat_model"])) {
            $errors["{$element_name_prefix}chat_model"] = get_string('required');
        }

        if (empty($data["{$element_name_prefix}embedding_model"])) {
            $errors["{$element_name_prefix}embedding_model"] = get_string('required');
        }

        return $errors;
    }

    public static function action_moodleform_definition(
        \MoodleQuickForm $mform,
        string $interface,
        string $element_name_prefix
    ): void {
        switch ($interface) {
            case interfaces\chat_completion::class:
                self::add_chat_model_field($mform, $element_name_prefix);
                break;
            case interfaces\create_embedding::class:
                self::add_embedding_model_field($mform, $element_name_prefix);
                break;
            default:
        }
    }

    /**
     * @throws invalid_provider_instance_configuration
     * @throws invalid_provider_instance_response
     */
    public function chat_completion(array $messages): string
    {
        if (empty($this->chat_model)) {
            throw new invalid_provider_instance_configuration('Chat model is not configured');
        }

        try {
            $response = $this->curl->post("{$this->base_url}/v1/chat/completions", json_encode([
                'model' => $this->chat_model,
                'messages' => $messages,
            ], JSON_THROW_ON_ERROR));

            $json = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

            $content = $json['choices'][0]['message']['content'] ?? '';

            // Remove any <think>...</think> tags from the response. Some Qwen models include their internal reasoning.
            return trim(preg_replace('/<think>.*?<\/think>/s', '', $content));
        } catch (\Throwable $t) {
            throw new invalid_provider_instance_response(
                'Invalid response from Nebius: ' . $t->getMessage(),
                previous: $t
            );
        }
    }

    /**
     * @throws invalid_provider_instance_response
     * @throws invalid_provider_instance_configuration
     */
    public function get_embedding(string $input, ?int $dimension): array
    {
        if (empty($this->embedding_model)) {
            throw new invalid_provider_instance_configuration('Embedding model is not configured');
        }

        try {
            $response = $this->curl->post("{$this->base_url}/v1/embeddings", json_encode([
                'model' => $this->embedding_model,
                'input' => $input,
                'dimensions' => $dimension
            ], JSON_THROW_ON_ERROR));

            $json = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

            return $json['data'][0]['embedding'];
        } catch (\Throwable $t) {
            throw new invalid_provider_instance_response(
                'Invalid response from Nebius: ' . $t->getMessage(),
                previous: $t
            );
        }
    }
}
