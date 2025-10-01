<?php

namespace local_mxaimanager\app\ai\provider\providers;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

use local_mxaimanager\app\factory as base_factory;

class mistral extends provider implements interfaces\chat_completion, interfaces\create_embedding
{
    private \curl $curl;
    private string $base_url;
    private string $api_key;

    public function __construct(base_factory $base_factory, array $json_config)
    {
        $this->base_factory = $base_factory;
        $this->base_url = $json_config['base_url'] ?? '';
        $this->api_key = $json_config['api_key'] ?? '';

        if (empty($this->base_url) || empty($this->api_key)) {
            throw new \Exception('Mistral provider is not configured');
        }

        $this->curl = $this->base_factory->curl();
        $this->curl->setHeader([
            "Authorization: Bearer {$this->api_key}",
            'Content-Type: application/json'
        ]);
    }

    public static function moodleform_definition(\MoodleQuickForm $mform, string $element_name_prefix): void
    {
        // Add base_url field
        $mform->addElement('text', "{$element_name_prefix}base_url", get_string('base_url', 'local_mxaimanager'));
        $mform->setType("{$element_name_prefix}base_url", PARAM_URL);
        $mform->setDefault("{$element_name_prefix}base_url", 'https://api.mistral.ai');

        // Add api_key field
        $mform->addElement('text', "{$element_name_prefix}api_key", get_string('api_key', 'local_mxaimanager'));
        $mform->setType("{$element_name_prefix}api_key", PARAM_TEXT);
        $mform->setDefault("{$element_name_prefix}api_key", '');
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

        return $errors;
    }

    public function chat_completion(array $messages): string
    {
        $response = $this->curl->post("{$this->base_url}/v1/chat/completions", json_encode([
            'model' => $model,
            'messages' => $messages,
        ], JSON_THROW_ON_ERROR));

        $json = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        return $json['choices'][0]['message']['content'];
    }

    public function get_embedding(string $input, ?int $dimension): array
    {
        $response = $this->curl->post("{$this->base_url}/v1/embeddings", json_encode([
            'model' => $model,
            'input' => $input,
            'output_dimension' => $dimension
        ], JSON_THROW_ON_ERROR));

        $json = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        return $json['data'][0]['embedding'];
    }

}
