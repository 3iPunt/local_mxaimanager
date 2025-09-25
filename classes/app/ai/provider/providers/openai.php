<?php

namespace local_mxaimanager\app\ai\provider\providers;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

use local_mxaimanager\app\factory as base_factory;

class openai implements interfaces\chat_completion, interfaces\create_embedding
{
    private base_factory $base_factory;
    private \curl $curl;
    private string $base_url = 'https://api.openai.com';
    private string $api_key;

    public function __construct(base_factory $base_factory, array $json_config)
    {
        $this->base_factory = $base_factory;
        $this->api_key = $json_config['api_key'] ?? '';

        $this->curl = $this->base_factory->curl();
        $this->curl->setHeader([
            "Authorization: Bearer {$this->api_key}",
            'Content-Type: application/json'
        ]);
    }

    public function chat_completion(array $messages, string $model): string
    {
        $response = $this->curl->post("{$this->base_url}/v1/chat/completions", json_encode([
            'model' => $model,
            'messages' => $messages,
        ], JSON_THROW_ON_ERROR));

        $json = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        return $json['choices'][0]['message']['content'];
    }

    public function get_embedding(string $input, string $model, ?int $dimension): array
    {
        $response = $this->curl->post("{$this->base_url}/v1/embeddings", json_encode([
            'model' => $model,
            'input' => $input,
            'dimensions' => $dimension
        ], JSON_THROW_ON_ERROR));

        $json = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        return $json['data'][0]['embedding'];
    }
}
