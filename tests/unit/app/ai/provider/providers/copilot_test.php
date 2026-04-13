<?php

namespace local_mxaimanager\unit\app\ai\provider\providers;

// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();
// @codeCoverageIgnoreEnd

global $CFG;
require_once $CFG->libdir . '/formslib.php';

use local_mxaimanager\app\ai\provider\providers\copilot;
use local_mxaimanager\app\ai\provider\providers\interfaces\chat_completion;
use local_mxaimanager\app\ai\provider\providers\interfaces\create_embedding;
use local_mxaimanager\app\ai\provider\providers\interfaces\create_image;
use local_mxaimanager\app\ai\provider\providers\interfaces\create_transcription;
use local_mxaimanager\app\exceptions\invalid_provider_instance_response;
use local_mxaimanager\app\factory;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Unit test class for the Copilot (Azure OpenAI Service) provider.
 *
 * Contains tests for the constructor, chat, embeddings, image generation,
 * audio transcription methods, and Moodle form integration.
 */
class copilot_test extends \base_testcase
{
    /** @var MockObject|factory Mock of the base factory. */
    private MockObject $mock_base_factory;
    /** @var MockObject|\curl Mock of the cURL class. */
    private MockObject $mock_curl;

    /**
     * Set up for each test.
     * Creates mocks for cURL and the base factory.
     */
    protected function setUp(): void
    {
        $this->mock_curl = $this->createMock(\curl::class);
        $this->mock_base_factory = $this->createMock(factory::class);
        $this->mock_base_factory->method('curl')->willReturn($this->mock_curl);
    }

    /**
     * Tests that the constructor correctly initializes the provider with valid configuration.
     */
    public function test_constructor_with_valid_config(): void
    {
        $json_config = [
            'base_url' => 'https://test.openai.azure.com/',
            'api_key' => 'test_key',
            'api_version' => '2023-05-15',
            'chat_model' => 'gpt-35-turbo',
            'embedding_model' => 'text-embedding-ada-002',
            'image_model' => 'dall-e-3',
            'transcription_model' => 'whisper'
        ];

        $provider = new copilot(
            $this->mock_base_factory,
            $json_config
        );

        $this->assertInstanceOf(copilot::class, $provider);
    }

    /**
     * Tests that the constructor throws an exception if the base URL is missing.
     */
    public function test_constructor_missing_base_url(): void
    {
        $json_config = [
            'api_key' => 'test_key',
            'chat_model' => 'gpt-35-turbo',
            'embedding_model' => 'text-embedding-ada-002'
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Copilot (Azure OpenAI) is missing base url and/or api key');

        new copilot(
            $this->mock_base_factory,
            $json_config
        );
    }

    /**
     * Tests that the constructor throws an exception if the API key is missing.
     */
    public function test_constructor_missing_api_key(): void
    {
        $json_config = [
            'base_url' => 'https://test.openai.azure.com/',
            'chat_model' => 'gpt-35-turbo',
            'embedding_model' => 'text-embedding-ada-002'
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Copilot (Azure OpenAI) is missing base url and/or api key');

        new copilot(
            $this->mock_base_factory,
            $json_config
        );
    }

    /**
     * Tests that the chat completion request is successful and returns the expected content.
     */
    public function test_chat_completion_success(): void
    {
        $json_config = [
            'base_url' => 'https://test.openai.azure.com/',
            'api_key' => 'test_key',
            'api_version' => '2023-05-15',
            'chat_model' => 'gpt-35-turbo',
        ];

        $expected_response = '{"choices":[{"message":{"content":"Hello, world!"}}], "usage":{"prompt_tokens": 5, "completion_tokens": 5}}';

        $this->mock_curl->expects($this->once())
            ->method('post')
            ->with(
                'https://test.openai.azure.com/openai/deployments/gpt-35-turbo/chat/completions?api-version=2023-05-15',
                $this->callback(function ($data) {
                    $decoded = json_decode($data, true);
                    return isset($decoded['messages']);
                })
            )
            ->willReturn($expected_response);

        $provider = new copilot(
            $this->mock_base_factory,
            $json_config
        );

        $messages = [['role' => 'user', 'content' => 'Hello']];
        $result = $provider->chat_completion($messages);

        $this->assertEquals('Hello, world!', $result->get_response());
    }

    /**
     * Tests that `chat_completion` throws an exception if the chat model is not configured.
     */
    public function test_chat_completion_missing_chat_model(): void
    {
        $json_config = [
            'base_url' => 'https://test.openai.azure.com/',
            'api_key' => 'test_key',
            'api_version' => '2023-05-15',
        ];

        $provider = new copilot(
            $this->mock_base_factory,
            $json_config
        );

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Chat model is not configured');

        $messages = [['role' => 'user', 'content' => 'Hello']];
        $provider->chat_completion($messages);
    }

    /**
     * Tests that `chat_completion` throws an exception if the JSON response is invalid.
     */
    public function test_chat_completion_json_decode_error(): void
    {
        $json_config = [
            'base_url' => 'https://test.openai.azure.com/',
            'api_key' => 'test_key',
            'api_version' => '2023-05-15',
            'chat_model' => 'gpt-35-turbo',
        ];

        $this->mock_curl->expects($this->once())
            ->method('post')
            ->willReturn('invalid json');

        $provider = new copilot(
            $this->mock_base_factory,
            $json_config
        );

        $this->expectException(invalid_provider_instance_response::class);

        $messages = [['role' => 'user', 'content' => 'Hello']];
        $provider->chat_completion($messages);
    }

    /**
     * Tests that the embedding request is successful and returns the expected values.
     */
    public function test_get_embedding_success(): void
    {
        $json_config = [
            'base_url' => 'https://test.openai.azure.com/',
            'api_key' => 'test_key',
            'api_version' => '2023-05-15',
            'embedding_model' => 'text-embedding-ada-002'
        ];

        $expected_response = '{"data":[{"embedding":[0.1, 0.2, 0.3]}],"usage":{"prompt_tokens":1,"total_tokens":1}}';

        $this->mock_curl->expects($this->once())
            ->method('post')
            ->with(
                'https://test.openai.azure.com/openai/deployments/text-embedding-ada-002/embeddings?api-version=2023-05-15',
                $this->callback(function ($data) {
                    $decoded = json_decode($data, true);
                    return isset($decoded['input']);
                })
            )
            ->willReturn($expected_response);

        $provider = new copilot(
            $this->mock_base_factory,
            $json_config
        );

        $result = $provider->get_embedding('test input', null);

        $this->assertEquals([0.1, 0.2, 0.3], $result->get_response());
    }

    /**
     * Tests that `get_embedding` throws an exception if the embedding model is not configured.
     */
    public function test_get_embedding_missing_embedding_model(): void
    {
        $json_config = [
            'base_url' => 'https://test.openai.azure.com/',
            'api_key' => 'test_key',
            'api_version' => '2023-05-15',
        ];

        $provider = new copilot(
            $this->mock_base_factory,
            $json_config
        );

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Embedding model is not configured');

        $provider->get_embedding('test input', null);
    }

    /**
     * Tests that image creation is successful and returns the expected URL.
     */
    public function test_create_image_success_url(): void
    {
        $json_config = [
            'base_url' => 'https://test.openai.azure.com/',
            'api_key' => 'test_key',
            'api_version' => '2023-05-15',
            'image_model' => 'dall-e-3'
        ];

        $expected_response = '{"data":[{"url":"https://example.com/image.png"}]}';

        $this->mock_curl->expects($this->once())
            ->method('post')
            ->with(
                'https://test.openai.azure.com/openai/deployments/dall-e-3/images/generations?api-version=2023-05-15',
                $this->callback(function ($data) {
                    $decoded = json_decode($data, true);
                    return isset($decoded['prompt']) &&
                        $decoded['prompt'] === 'A test image' &&
                        $decoded['response_format'] === 'url';
                })
            )
            ->willReturn($expected_response);

        $provider = new copilot(
            $this->mock_base_factory,
            $json_config
        );

        $result = $provider->create_image('A test image', false);

        $this->assertEquals('https://example.com/image.png', $result->get_response());
    }

    /**
     * Tests that image creation is successful and returns the expected base64 data.
     */
    public function test_create_image_success_b64(): void
    {
        $json_config = [
            'base_url' => 'https://test.openai.azure.com/',
            'api_key' => 'test_key',
            'api_version' => '2023-05-15',
            'image_model' => 'dall-e-3'
        ];

        $expected_response = '{"data":[{"b64_json":"base64encodedimage"}]}';

        $this->mock_curl->expects($this->once())
            ->method('post')
            ->with(
                'https://test.openai.azure.com/openai/deployments/dall-e-3/images/generations?api-version=2023-05-15',
                $this->callback(function ($data) {
                    $decoded = json_decode($data, true);
                    return isset($decoded['prompt']) &&
                        $decoded['prompt'] === 'A test image' &&
                        $decoded['response_format'] === 'b64_json';
                })
            )
            ->willReturn($expected_response);

        $provider = new copilot(
            $this->mock_base_factory,
            $json_config
        );

        $result = $provider->create_image('A test image', true);

        $this->assertEquals('base64encodedimage', $result->get_response());
    }

    /**
     * Tests that `create_image` throws an exception if the image model is not configured.
     */
    public function test_create_image_missing_image_model(): void
    {
        $json_config = [
            'base_url' => 'https://test.openai.azure.com/',
            'api_key' => 'test_key',
            'api_version' => '2023-05-15',
        ];

        $provider = new copilot(
            $this->mock_base_factory,
            $json_config
        );

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Image model is not configured');

        $provider->create_image('A test image', false);
    }

    /**
     * Tests that audio transcription is successful and returns the expected text.
     */
    public function test_create_transcription_success(): void
    {
        $json_config = [
            'base_url' => 'https://test.openai.azure.com/',
            'api_key' => 'test_key',
            'api_version' => '2023-05-15',
            'transcription_model' => 'whisper'
        ];

        $expected_response = '{"text":"Hello, world!","segments":[{"start":0,"end":1,"text":"Hello"}],"usage":{"prompt_tokens":10,"completion_tokens":5}}';

        $this->mock_curl->expects($this->once())
            ->method('post')
            ->with(
                'https://test.openai.azure.com/openai/deployments/whisper/audio/transcriptions?api-version=2023-05-15',
                $this->callback(function ($data) {
                    return is_array($data) &&
                        isset($data['model']) && $data['model'] === 'whisper' &&
                        isset($data['file']) && $data['file'] instanceof \CURLFile &&
                        isset($data['response_format']) && $data['response_format'] === 'verbose_json';
                })
            )
            ->willReturn($expected_response);

        $provider = new copilot(
            $this->mock_base_factory,
            $json_config
        );

        $temp_file = tempnam(sys_get_temp_dir(), 'test_audio');
        file_put_contents($temp_file, 'fake audio content');

        $result = $provider->create_transcription($temp_file);

        $this->assertEquals('Hello, world!', $result->get_response()->get_text());
        $this->assertEquals([['start' => 0, 'end' => 1, 'text' => 'Hello']], $result->get_response()->get_segments());

        unlink($temp_file);
    }

    /**
     * Tests that `create_transcription` throws an exception if the transcription model is not configured.
     */
    public function test_create_transcription_missing_model(): void
    {
        $json_config = [
            'base_url' => 'https://test.openai.azure.com/',
            'api_key' => 'test_key',
            'api_version' => '2023-05-15',
        ];

        $provider = new copilot(
            $this->mock_base_factory,
            $json_config
        );

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Transcription model is not configured');

        $temp_file = tempnam(sys_get_temp_dir(), 'test_audio');
        $provider->create_transcription($temp_file);
        unlink($temp_file);
    }

    /**
     * Tests that the Moodle form definition for Copilot is correctly set up.
     */
    public function test_moodleform_definition(): void
    {
        $mform = $this->createMock(\MoodleQuickForm::class);

        $mform->expects($this->exactly(7))->method('addElement');
        $mform->expects($this->exactly(7))->method('setType');
        $mform->expects($this->exactly(3))->method('setDefault');

        $element_name_prefix = 'test_';

        copilot::moodleform_definition(
            $mform,
            $element_name_prefix
        );

        $this->assertTrue(true);
    }

    /**
     * Tests that Moodle form validation for Copilot works with valid data.
     */
    public function test_moodleform_validation_with_valid_data(): void
    {
        $data = [
            'prefix_base_url' => 'https://test.openai.azure.com/',
            'prefix_api_key' => 'test_key',
            'prefix_api_version' => '2023-05-15',
            'prefix_chat_model' => 'gpt-35-turbo',
            'prefix_embedding_model' => 'text-embedding-ada-002',
            'prefix_image_model' => 'dall-e-3',
            'prefix_transcription_model' => 'whisper'
        ];

        $errors = copilot::moodleform_validation(
            $data,
            'prefix_'
        );

        $this->assertEmpty($errors);
    }

    /**
     * Tests that Moodle form validation for Copilot detects a missing base URL.
     */
    public function test_moodleform_validation_missing_base_url(): void
    {
        $data = [
            'prefix_api_key' => 'test_key',
            'prefix_api_version' => '2023-05-15',
            'prefix_chat_model' => 'gpt-35-turbo',
            'prefix_embedding_model' => 'text-embedding-ada-002',
            'prefix_image_model' => 'dall-e-3',
            'prefix_transcription_model' => 'whisper'
        ];

        $errors = copilot::moodleform_validation(
            $data,
            'prefix_'
        );

        $this->assertArrayHasKey('prefix_base_url', $errors);
    }

    /**
     * Tests that Moodle form validation for Copilot detects a missing API key.
     */
    public function test_moodleform_validation_missing_api_key(): void
    {
        $data = [
            'prefix_base_url' => 'https://test.openai.azure.com/',
            'prefix_api_version' => '2023-05-15',
            'prefix_chat_model' => 'gpt-35-turbo',
            'prefix_embedding_model' => 'text-embedding-ada-002',
            'prefix_image_model' => 'dall-e-3',
            'prefix_transcription_model' => 'whisper'
        ];

        $errors = copilot::moodleform_validation(
            $data,
            'prefix_'
        );

        $this->assertArrayHasKey('prefix_api_key', $errors);
    }
}
