<?php

namespace local_mxaimanager\app\ai\provider\providers;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

use local_mxaimanager\app\factory as base_factory;

class ollama extends provider
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
            throw new \Exception('OpenAI provider is not configured');
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

    public function chat_completion(array $messages, string $model): string
    {
        // TODO: Implement chat_completion() method.
        /*
         * https://ollama.readthedocs.io/en/api/#generate-a-chat-completion
         * Example chat completion curl request:
           curl http://dashboard:11434/api/chat -d '{
             "model": "llama3",
             "stream": false,
             "messages": [
               {
                 "role": "user",
                 "content": "why is the sky blue?"
               }
             ]
           }'
         */
    }

    public function get_embedding(string $input, string $model, int $dimension): array
    {
        // TODO: Implement get_embedding() method.
        /*
         * https://ollama.readthedocs.io/en/api/#generate-embeddings
         * Example embedding curl request:
            curl http://dashboard:11434/api/embed -d '{
              "model": "all-minilm",
              "input": "Why is the sky blue?"
            }'
         */
    }
}
