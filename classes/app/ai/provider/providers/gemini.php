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
 * Class for interacting with the Gemini API as an AI provider.
 *
 * Implements chat, embeddings, image generation, and audio transcription interfaces.
 */
class gemini extends provider implements interfaces\chat_completion, interfaces\create_embedding,
                                         interfaces\create_image, interfaces\create_transcription
{
    /** @var \curl cURL instance for making HTTP requests. */
    private \curl $curl;
    /** @var string Base URL for the Gemini API. */
    private string $base_url;
    /** @var string API key for authentication with Gemini. */
    private string $api_key;
    /** @var string Chat model to use. */
    private string $chat_model;
    /** @var string Embedding model to use. */
    private string $embedding_model;
    /** @var string Image generation model to use. */
    private string $image_model;
    /** @var string Audio transcription model to use. */
    private string $transcription_model;

    /**
     * Constructor for the Gemini class.
     *
     * Initializes the provider with the provided configuration.
     *
     * @param base_factory $base_factory The base factory to get dependencies.
     * @param array $json_config Provider configuration in JSON format.
     * @throws invalid_provider_instance_configuration If the API key is missing.
     */
    public function __construct(base_factory $base_factory, array $json_config)
    {
        $this->base_factory = $base_factory;
        $this->base_url = $json_config['base_url'] ?? '';
        $this->api_key = $json_config['api_key'] ?? '';
        $this->chat_model = $json_config['chat_model'] ?? '';
        $this->embedding_model = $json_config['embedding_model'] ?? '';
        $this->image_model = $json_config['image_model'] ?? '';
        $this->transcription_model = $json_config['transcription_model'] ?? '';

        if (empty($this->api_key) || empty($this->base_url)) {
            throw new invalid_provider_instance_configuration(get_string('gemini_missing_api_key', 'local_mxaimanager'));
        }

        $this->curl = $this->base_factory->curl();
        $this->curl->setHeader([
            "x-goog-api-key: {$this->api_key}",
            'Content-Type: application/json'
        ]);
    }

    /**
     * Adds a field for chat model configuration to the Moodle form.
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
        $mform->addHelpButton("{$element_name_prefix}chat_model", 'gemini_chat_model', 'local_mxaimanager');
    }

    /**
     * Adds a field for embedding model configuration to the Moodle form.
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
        $mform->addHelpButton("{$element_name_prefix}embedding_model", 'gemini_embedding_model', 'local_mxaimanager');
    }

    /**
     * Adds a field for image model configuration to the Moodle form.
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
        $mform->addHelpButton("{$element_name_prefix}image_model", 'gemini_image_model', 'local_mxaimanager');
    }

    /**
     * Adds a field for transcription model configuration to the Moodle form.
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
            'gemini_transcription_model',
            'local_mxaimanager'
        );
    }

    /**
     * Converts an array of messages to the format expected by the Gemini API.
     *
     * @param array $messages Array of messages with 'system', 'user', or 'assistant' roles.
     * @return array Messages formatted for the Gemini API.
     */
    private function convert_messages_to_gemini_format(array $messages): array
    {
        $contents = [];
        $systemprompt = '';
        foreach ($messages as $message) {
            if ($message->get_role() === 'system') {
                $systemprompt .= $message->get_content() . "\n";
                continue;
            }
            $role = $message->get_role() === 'assistant' ? 'model' : 'user';
            $contents[] = (object)[
                'role' => $role,
                'parts' => [(object)['text' => $message->get_content()]]
            ];
        }

        if (!empty($systemprompt) && !empty($contents)) {
            $contents[0]->parts[0]->text = $systemprompt . $contents[0]->parts[0]->text;
        }

        return $contents;
    }

    /**
     * Defines the Moodle form fields for the general Gemini provider configuration.
     *
     * @param \MoodleQuickForm $mform The Moodle form.
     * @param string $element_name_prefix Prefix for form element names.
     */
    public static function moodleform_definition(\MoodleQuickForm $mform, string $element_name_prefix): void
    {
        // Add base_url field
        $mform->addElement('text', "{$element_name_prefix}base_url", get_string('base_url', 'local_mxaimanager'));
        $mform->setType("{$element_name_prefix}base_url", PARAM_URL);
        $mform->setDefault("{$element_name_prefix}base_url", 'https://generativelanguage.googleapis.com/v1beta');

        // Add api_key field
        $mform->addElement('text', "{$element_name_prefix}api_key", get_string('api_key', 'local_mxaimanager'));
        $mform->setType("{$element_name_prefix}api_key", PARAM_TEXT);
        $mform->setDefault("{$element_name_prefix}api_key", '');

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
     * Performs validation of Moodle form data for the Gemini provider.
     *
     * @param array $data Form data.
     * @param string $element_name_prefix Prefix for form element names.
     * @return array Validation errors.
     */
    public static function moodleform_validation(array $data, string $element_name_prefix): array
    {
        $errors = [];

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
     * Defines Moodle form fields for specific actions of the Gemini provider.
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
     * Performs a chat completion request to the Gemini API.
     *
     * @param array $messages Array of messages in API format.
     * @param bool $json_mode Whether the response should be in JSON format.
     * @param array|null $json_schema JSON schema for the response.
     * @return chat_completion_request Object with the chat response.
     * @throws invalid_provider_instance_configuration If the chat model is not configured.
     * @throws invalid_provider_instance_response If the Gemini response is invalid.
     */
    public function chat_completion(
        array $messages,
        bool $json_mode = false,
        ?array $json_schema = null
    ): chat_completion_request {
        if (empty($this->chat_model)) {
            throw new invalid_provider_instance_configuration(get_string('gemini_chat_model_not_configured', 'local_mxaimanager'));
        }

        $payload = [
            'contents' => $this->convert_messages_to_gemini_format($messages),
        ];
        $request = (object)$payload;

        if ($json_mode || $json_schema) {
            $request->generationConfig = (object)[
                'response_mime_type' => 'application/json'
            ];
        }

        try {
            $url = "{$this->base_url}/models/{$this->chat_model}:generateContent";
            $response = $this->curl->post($url, json_encode($request, JSON_THROW_ON_ERROR));
            $json = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

            if (!isset($json['candidates'][0]['content']['parts'][0]['text'])) {
                throw new \Exception(get_string('gemini_missing_chat_content', 'local_mxaimanager', $response));
            }

            $content = $json['candidates'][0]['content']['parts'][0]['text'];
            $prompttokens = $json['usageMetadata']['promptTokenCount'] ?? 0;
            $completiontokens = $json['usageMetadata']['candidatesTokenCount'] ?? 0;

            return new chat_completion_request(
                $payload,
                $json,
                $content,
                $prompttokens,
                $completiontokens
            );
        } catch (\Throwable $t) {
            throw new invalid_provider_instance_response(
                get_string('gemini_invalid_response', 'local_mxaimanager', $t->getMessage()),
                previous: $t
            );
        }
    }

    /**
     * Obtains an embedding for the input text using the Gemini API.
     *
     * @param string $input Text for which to generate the embedding.
     * @param int|null $dimension Desired dimension for the embedding (may not be supported by all APIs).
     * @return create_embedding_request Object with the generated embedding.
     * @throws invalid_provider_instance_response If the Gemini response is invalid.
     * @throws invalid_provider_instance_configuration If the embedding model is not configured.
     */
    public function get_embedding(string $input, ?int $dimension): create_embedding_request
    {
        if (empty($this->embedding_model)) {
            throw new invalid_provider_instance_configuration(get_string('gemini_embedding_model_not_configured', 'local_mxaimanager'));
        }

        $payload = (object)[
            'model' => "models/{$this->embedding_model}",
            'content' => (object)[
                'parts' => [(object)['text' => $input]]
            ]
        ];

        try {
            $url = "{$this->base_url}/v1beta/models/{$this->embedding_model}:embedContent?key={$this->api_key}";
            $response = $this->curl->post($url, json_encode($payload, JSON_THROW_ON_ERROR));
            $json = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

            if (!isset($json['embedding']['values'])) {
                throw new \Exception(get_string('gemini_missing_embedding_data', 'local_mxaimanager', $response));
            }

            return new create_embedding_request(
                $payload,
                $json,
                $json['embedding']['values'],
                0,
                0
            );
        } catch (\Throwable $t) {
            throw new invalid_provider_instance_response(
                get_string('gemini_invalid_response', 'local_mxaimanager', $t->getMessage()),
                previous: $t
            );
        }
    }

    /**
     * Generates an image from a prompt using the Gemini API.
     *
     * @param string $prompt Textual description for image generation.
     * @param bool $return_b64 Whether the image should be returned in base64 format.
     * @return image_generation_request Object with the generated image.
     * @throws invalid_provider_instance_configuration If the image model is not configured.
     * @throws invalid_provider_instance_response If the Gemini response is invalid.
     * @throws \InvalidArgumentException If an unsupported return format is requested.
     */
    public function create_image(
        string $prompt,
        bool $return_b64 = false
    ): image_generation_request {
        if (empty($this->image_model)) {
            throw new invalid_provider_instance_configuration(get_string('gemini_image_model_not_configured', 'local_mxaimanager'));
        }

        if (!$return_b64) {
            throw new \InvalidArgumentException(get_string('gemini_image_b64_only', 'local_mxaimanager'));
        }

        $payload = (object)[
            'contents' => [
                (object)[
                    'parts' => [
                        (object)['text' => $prompt]
                    ]
                ]
            ],
            'tools' => [
                (object)[
                    'function_declarations' => [
                        (object)[
                            'name' => 'image_generator',
                            'description' => 'Generates an image based on a textual prompt.',
                            'parameters' => (object)[
                                'type' => 'OBJECT',
                                'properties' => (object)[
                                    'prompt' => (object)[
                                        'type' => 'STRING',
                                        'description' => 'The descriptive prompt for image generation.'
                                    ]
                                ],
                                'required' => ['prompt']
                            ]
                        ]
                    ]
                ]
            ]
        ];

        try {
            $url = "{$this->base_url}/v1beta/models/{$this->image_model}:generateContent?key={$this->api_key}";
            $response = $this->curl->post($url, json_encode($payload, JSON_THROW_ON_ERROR));
            $json = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

            if (!isset($json['candidates'][0]['content']['parts'][0]['inline_data']['data'])) {
                throw new \Exception(get_string('gemini_missing_image_data', 'local_mxaimanager', $response));
            }

            $imagedata = $json['candidates'][0]['content']['parts'][0]['inline_data']['data'];

            return new image_generation_request(
                $payload,
                $json,
                $imagedata,
                0,
                0
            );
        } catch (\Throwable $t) {
            throw new invalid_provider_instance_response(
                get_string('gemini_invalid_response', 'local_mxaimanager', $t->getMessage()),
                previous: $t
            );
        }
    }

    /**
     * Transcribes an audio file to text using the Gemini API.
     *
     * @param string $audio_filepath Path to the audio file.
     * @return create_transcription_request Object with the audio transcription.
     * @throws invalid_provider_instance_configuration If the transcription model is not configured.
     * @throws invalid_provider_instance_response If the Gemini response is invalid.
     */
    public function create_transcription(string $audio_filepath): create_transcription_request
    {
        if (empty($this->transcription_model)) {
            throw new invalid_provider_instance_configuration(get_string('gemini_transcription_model_not_configured', 'local_mxaimanager'));
        }

        $audiocontent = base64_encode(file_get_contents($audio_filepath));
        $mimetype = mime_content_type($audio_filepath);

        $payload = (object)[
            'contents' => [
                (object)[
                    'parts' => [
                        (object)['text' => 'Transcribe the audio.'],
                        (object)[
                            'inline_data' => [
                                'mime_type' => $mimetype,
                                'data' => $audiocontent
                            ]
                        ]
                    ]
                ]
            ]
        ];

        try {
            $url = "{$this->base_url}/v1beta/models/{$this->transcription_model}:generateContent?key={$this->api_key}";
            $response = $this->curl->post($url, json_encode($payload, JSON_THROW_ON_ERROR));
            $json = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

            if (!isset($json['candidates'][0]['content']['parts'][0]['text'])) {
                throw new \Exception(get_string('gemini_missing_transcription_data', 'local_mxaimanager', $response));
            }

            $transcriptiontext = $json['candidates'][0]['content']['parts'][0]['text'];
            $transcription = new transcription($transcriptiontext, []);

            return new create_transcription_request(
                $payload,
                $json,
                $transcription,
                0,
                0
            );
        } catch (\Throwable $t) {
            throw new invalid_provider_instance_response(
                get_string('gemini_invalid_response', 'local_mxaimanager', $t->getMessage()),
                previous: $t
            );
        }
    }
}
