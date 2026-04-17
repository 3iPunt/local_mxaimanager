<?php

namespace local_mxaimanager\app\ai\provider\providers;

// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();
// @codeCoverageIgnoreEnd

use local_mxaimanager\app\ai\provider\chat_completion_request;
use local_mxaimanager\app\ai\provider\create_embedding_request;
use local_mxaimanager\app\ai\provider\create_transcription_request;
use local_mxaimanager\app\ai\provider\image_generation_request;
use local_mxaimanager\app\ai\provider\transcription;
use local_mxaimanager\app\exceptions\invalid_provider_instance_configuration;
use local_mxaimanager\app\exceptions\invalid_provider_instance_response;
use local_mxaimanager\app\factory as base_factory;

/**
 * Class for interacting with Microsoft Copilot (Azure OpenAI Service) API as an AI provider.
 *
 * Implements chat, embeddings, image generation, and audio transcription interfaces.
 */
class copilot extends provider implements interfaces\chat_completion, interfaces\create_embedding,
                                         interfaces\create_image, interfaces\create_transcription
{
    /** @var \curl cURL instance for making HTTP requests. */
    private \curl $curl;
    /** @var string Base URL for the Azure OpenAI Service. */
    private string $base_url;
    /** @var string API key for authentication with Azure OpenAI. */
    private string $api_key;
    /** @var string API version to use for requests. */
    private string $api_version;
    /** @var string Chat model (deployment name) to use. */
    private string $chat_model;
    /** @var string Embedding model (deployment name) to use. */
    private string $embedding_model;
    /** @var string Image generation model (deployment name) to use. */
    private string $image_model;
    /** @var string Audio transcription model (deployment name) to use. */
    private string $transcription_model;

    /**
     * Constructor for the Copilot (Azure OpenAI Service) class.
     *
     * Initializes the provider with the provided configuration.
     *
     * @param base_factory $base_factory The base factory to get dependencies.
     * @param array $json_config Provider configuration in JSON format.
     * @throws invalid_provider_instance_configuration If the base URL or API key is missing.
     */
    public function __construct(base_factory $base_factory, array $json_config)
    {
        $this->base_factory = $base_factory;
        $this->base_url = $json_config['base_url'] ?? '';
        $this->api_key = $json_config['api_key'] ?? '';
        $this->api_version = $json_config['api_version'] ?? '2023-05-15'; // Default API version for Azure OpenAI.
        $this->chat_model = $json_config['chat_model'] ?? '';
        $this->embedding_model = $json_config['embedding_model'] ?? '';
        $this->image_model = $json_config['image_model'] ?? '';
        $this->transcription_model = $json_config['transcription_model'] ?? '';

        if (empty($this->base_url) || empty($this->api_key)) {
            throw new invalid_provider_instance_configuration(get_string('copilot_missing_base_url_or_api_key', 'local_mxaimanager'));
        }

        $this->curl = $this->base_factory->curl();
        $this->curl->setHeader([
            "api-key: {$this->api_key}",
            'Content-Type: application/json'
        ]);
    }

    /**
     * Adds a field for chat model (deployment name) configuration to the Moodle form.
     *
     * @param \MoodleQuickForm $mform The Moodle form.
     * @param string $element_name_prefix Prefix for form element names.
     */
    private static function add_chat_model_field(\MoodleQuickForm $mform, string $element_name_prefix): void
    {
        $mform->addElement(
            'text',
            "{$element_name_prefix}chat_model",
            get_string('default_chat_model', 'local_mxaimanager'),
            [
                'action' => interfaces\chat_completion::class
            ]
        );
        $mform->setType("{$element_name_prefix}chat_model", PARAM_TEXT);
        $mform->addHelpButton("{$element_name_prefix}chat_model", 'copilot_chat_model', 'local_mxaimanager');
    }

    /**
     * Adds a field for embedding model (deployment name) configuration to the Moodle form.
     *
     * @param \MoodleQuickForm $mform The Moodle form.
     * @param string $element_name_prefix Prefix for form element names.
     */
    private static function add_embedding_model_field(\MoodleQuickForm $mform, string $element_name_prefix): void
    {
        $mform->addElement(
            'text',
            "{$element_name_prefix}embedding_model",
            get_string('default_embedding_model', 'local_mxaimanager'),
            [
                'action' => interfaces\create_embedding::class
            ]
        );
        $mform->setType("{$element_name_prefix}embedding_model", PARAM_TEXT);
        $mform->addHelpButton("{$element_name_prefix}embedding_model", 'copilot_embedding_model', 'local_mxaimanager');
    }

    /**
     * Adds a field for image model (deployment name) configuration to the Moodle form.
     *
     * @param \MoodleQuickForm $mform The Moodle form.
     * @param string $element_name_prefix Prefix for form element names.
     */
    private static function add_image_model_field(\MoodleQuickForm $mform, string $element_name_prefix): void
    {
        $mform->addElement(
            'text',
            "{$element_name_prefix}image_model",
            get_string('default_image_model', 'local_mxaimanager'),
            [
                'action' => interfaces\create_image::class
            ]
        );
        $mform->setType("{$element_name_prefix}image_model", PARAM_TEXT);
        $mform->addHelpButton("{$element_name_prefix}image_model", 'copilot_image_model', 'local_mxaimanager');
    }

    /**
     * Adds a field for transcription model (deployment name) configuration to the Moodle form.
     *
     * @param \MoodleQuickForm $mform The Moodle form.
     * @param string $element_name_prefix Prefix for form element names.
     */
    private static function add_transcription_model_field(\MoodleQuickForm $mform, string $element_name_prefix): void
    {
        $mform->addElement(
            'text',
            "{$element_name_prefix}transcription_model",
            get_string('default_transcription_model', 'local_mxaimanager'),
            [
                'action' => interfaces\create_transcription::class
            ]
        );
        $mform->setType("{$element_name_prefix}transcription_model", PARAM_TEXT);
        $mform->addHelpButton(
            "{$element_name_prefix}transcription_model",
            'copilot_transcription_model',
            'local_mxaimanager'
        );
    }

    /**
     * Defines the Moodle form fields for the general Copilot provider configuration.
     *
     * @param \MoodleQuickForm $mform The Moodle form.
     * @param string $element_name_prefix Prefix for form element names.
     */
    public static function moodleform_definition(\MoodleQuickForm $mform, string $element_name_prefix): void
    {
        // Add base_url field
        $mform->addElement('text', "{$element_name_prefix}base_url", get_string('base_url', 'local_mxaimanager'));
        $mform->setType("{$element_name_prefix}base_url", PARAM_URL);
        $mform->setDefault("{$element_name_prefix}base_url", 'https://YOUR_RESOURCE_NAME.openai.azure.com/');

        // Add api_key field
        $mform->addElement('text', "{$element_name_prefix}api_key", get_string('api_key', 'local_mxaimanager'));
        $mform->setType("{$element_name_prefix}api_key", PARAM_TEXT);
        $mform->setDefault("{$element_name_prefix}api_key", '');

        // Add api_version field
        $mform->addElement('text', "{$element_name_prefix}api_version", get_string('api_version', 'local_mxaimanager'));
        $mform->setType("{$element_name_prefix}api_version", PARAM_TEXT);
        $mform->setDefault("{$element_name_prefix}api_version", '2023-05-15');

        // Add chat model field
        self::add_chat_model_field($mform, $element_name_prefix);

        // Add embedding model field
        self::add_embedding_model_field($mform, $element_name_prefix);

        // Add image model field
        self::add_image_model_field($mform, $element_name_prefix);

        // Add transcription model field
        self::add_transcription_model_field($mform, $element_name_prefix);
    }

    /**
     * Performs validation of Moodle form data for the Copilot provider.
     *
     * @param array $data Form data.
     * @param string $element_name_prefix Prefix for form element names.
     * @return array Validation errors.
     */
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

        if (empty($data["{$element_name_prefix}image_model"])) {
            $errors["{$element_name_prefix}image_model"] = get_string('required');
        }

        if (empty($data["{$element_name_prefix}transcription_model"])) {
            $errors["{$element_name_prefix}transcription_model"] = get_string('required');
        }

        return $errors;
    }

    /**
     * Defines Moodle form fields for specific actions of the Copilot provider.
     *
     * @param \MoodleQuickForm $mform The Moodle form.
     * @param string $interface The action interface for which fields are defined.
     * @param string $element_name_prefix Prefix for form element names.
     */
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
            case interfaces\create_image::class:
                self::add_image_model_field($mform, $element_name_prefix);
                break;
            case interfaces\create_transcription::class:
                self::add_transcription_model_field($mform, $element_name_prefix);
                break;
            default:
        }
    }

    /**
     * Performs a chat completion request to the Azure OpenAI Service.
     *
     * @param array $messages Array of messages in API format.
     * @param bool $json_mode Whether the response should be in JSON format.
     * @param array|null $json_schema JSON schema for the response.
     * @return chat_completion_request Object with the chat response.
     * @throws invalid_provider_instance_configuration If the chat model is not configured.
     * @throws invalid_provider_instance_response If the Azure OpenAI response is invalid.
     */
    public function chat_completion(
        array $messages,
        bool $json_mode = false,
        ?array $json_schema = null
    ): chat_completion_request {
        if (empty($this->chat_model)) {
            throw new invalid_provider_instance_configuration(get_string('copilot_chat_model_not_configured', 'local_mxaimanager'));
        }

        $payload = [
            'messages' => $messages,
        ];

        if ($json_schema !== null) {
            $payload['response_format'] = [
                'type' => 'json_schema',
                'json_schema' => [
                    'name' => 'response_schema',
                    'schema' => $json_schema
                ],
            ];
        } elseif ($json_mode) {
            $payload['response_format'] = [
                'type' => 'json_object',
            ];
        }

        try {
            $url = "{$this->base_url}/openai/deployments/{$this->chat_model}/chat/completions?api-version={$this->api_version}";
            $response = $this->curl->post($url, json_encode($payload, JSON_THROW_ON_ERROR));
            $json = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

            if (!isset($json['choices'][0]['message']['content'])) {
                throw new \Exception(get_string('copilot_missing_chat_content', 'local_mxaimanager', $response));
            }

            return new chat_completion_request(
                $payload,
                $json,
                $json['choices'][0]['message']['content'],
                $json['usage']['prompt_tokens'] ?? 0,
                $json['usage']['completion_tokens'] ?? 0
            );
        } catch (\Throwable $t) {
            throw new invalid_provider_instance_response(
                get_string('copilot_invalid_response', 'local_mxaimanager', $t->getMessage()),
                previous: $t
            );
        }
    }

    /**
     * Obtains an embedding for the input text using the Azure OpenAI Service.
     *
     * @param string $input Text for which to generate the embedding.
     * @param int|null $dimension Desired dimension for the embedding (may not be supported by all APIs).
     * @return create_embedding_request Object with the generated embedding.
     * @throws invalid_provider_instance_response If the Azure OpenAI response is invalid.
     * @throws invalid_provider_instance_configuration If the embedding model is not configured.
     */
    public function get_embedding(string $input, ?int $dimension): create_embedding_request
    {
        if (empty($this->embedding_model)) {
            throw new invalid_provider_instance_configuration(get_string('copilot_embedding_model_not_configured', 'local_mxaimanager'));
        }

        $payload = [
            'input' => $input,
        ];

        // Azure OpenAI embedding API might support dimensions, but it's not a standard parameter in all versions.
        // For now, we'll omit it unless specifically required by a version.
        // if ($dimension !== null) {
        //     $payload['dimensions'] = $dimension;
        // }

        try {
            $url = "{$this->base_url}/openai/deployments/{$this->embedding_model}/embeddings?api-version={$this->api_version}";
            $response = $this->curl->post($url, json_encode($payload, JSON_THROW_ON_ERROR));
            $json = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

            if (!isset($json['data'][0]['embedding'])) {
                throw new \Exception(get_string('copilot_missing_embedding_data', 'local_mxaimanager', $response));
            }

            return new create_embedding_request(
                $payload,
                $json,
                $json['data'][0]['embedding'],
                $json['usage']['prompt_tokens'] ?? 0,
                $json['usage']['total_tokens'] ?? 0
            );
        } catch (\Throwable $t) {
            throw new invalid_provider_instance_response(
                get_string('copilot_invalid_response', 'local_mxaimanager', $t->getMessage()),
                previous: $t
            );
        }
    }

    /**
     * Generates an image from a prompt using the Azure OpenAI Service (DALL-E).
     *
     * @param string $prompt Textual description for image generation.
     * @param bool $return_b64 Whether the image should be returned in base64 format.
     * @return image_generation_request Object with the generated image.
     * @throws invalid_provider_instance_configuration If the image model is not configured.
     * @throws invalid_provider_instance_response If the Azure OpenAI response is invalid.
     */
    public function create_image(
        string $prompt,
        bool $return_b64 = false
    ): image_generation_request {
        if (empty($this->image_model)) {
            throw new invalid_provider_instance_configuration(get_string('copilot_image_model_not_configured', 'local_mxaimanager'));
        }

        $payload = [
            'prompt' => $prompt,
            'n' => 1, // Number of images to generate
            'size' => '1024x1024', // Image size
            'response_format' => $return_b64 ? 'b64_json' : 'url',
        ];

        try {
            // Azure OpenAI DALL-E endpoint might be slightly different, often uses a specific deployment name.
            // Assuming image_model holds the deployment name for DALL-E.
            $url = "{$this->base_url}/openai/deployments/{$this->image_model}/images/generations?api-version={$this->api_version}";
            $response = $this->curl->post($url, json_encode($payload, JSON_THROW_ON_ERROR));
            $json = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

            if (!isset($json['data'][0][$return_b64 ? 'b64_json' : 'url'])) {
                throw new \Exception(get_string('copilot_missing_image_data', 'local_mxaimanager', $response));
            }

            return new image_generation_request(
                $payload,
                $json,
                $json['data'][0][$return_b64 ? 'b64_json' : 'url'],
                0, // Token counts for image generation are typically not applicable or returned.
                0
            );
        } catch (\Throwable $t) {
            throw new invalid_provider_instance_response(
                get_string('copilot_invalid_response_image_generation', 'local_mxaimanager', $t->getMessage()),
                previous: $t
            );
        }
    }

    /**
     * Transcribes an audio file to text using the Azure OpenAI Service (Whisper).
     *
     * @param string $audio_filepath Path to the audio file.
     * @return create_transcription_request Object with the audio transcription.
     * @throws invalid_provider_instance_configuration If the transcription model is not configured.
     * @throws invalid_provider_instance_response If the Azure OpenAI response is invalid.
     */
    public function create_transcription(string $audio_filepath): create_transcription_request
    {
        if (empty($this->transcription_model)) {
            throw new invalid_provider_instance_configuration(get_string('copilot_transcription_model_not_configured', 'local_mxaimanager'));
        }

        $filedata = [
            'file' => new \CURLFile($audio_filepath, mime_content_type($audio_filepath), basename($audio_filepath)),
            'model' => $this->transcription_model, // Deployment name for Whisper
            'response_format' => 'verbose_json', // Or 'text'
        ];

        // Temporarily remove content type headers for multipart upload
        $this->curl->resetHeader();
        $this->curl->setHeader([
            "api-key: {$this->api_key}"
        ]);

        try {
            $url = "{$this->base_url}/openai/deployments/{$this->transcription_model}/audio/transcriptions?api-version={$this->api_version}";
            $response = $this->curl->post(
                $url,
                $filedata
            );

            $json = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

            if (!isset($json['text'])) {
                throw new \Exception(get_string('copilot_missing_transcription_data', 'local_mxaimanager', $response));
            }

            $transcription = new transcription(
                $json['text'],
                $json['segments'] ?? []
            );

            return new create_transcription_request(
                $filedata,
                $json,
                $transcription,
                $json['usage']['prompt_tokens'] ?? 0,
                $json['usage']['completion_tokens'] ?? 0
            );
        } catch (\Throwable $t) {
            throw new invalid_provider_instance_response(
                get_string('copilot_invalid_response_transcription', 'local_mxaimanager', $t->getMessage()),
                previous: $t
            );
        } finally {
            // Reset headers back to JSON
            $this->curl->resetHeader();
            $this->curl->setHeader([
                "api-key: {$this->api_key}",
                'Content-Type: application/json'
            ]);
        }
    }
}
