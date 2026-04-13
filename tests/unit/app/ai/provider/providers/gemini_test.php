<?php

namespace local_mxaimanager\unit\app\ai\provider\providers;

// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();
// @codeCoverageIgnoreEnd

global $CFG;
require_once $CFG->libdir . '/formslib.php';

use local_mxaimanager\app\ai\provider\providers\gemini;
use local_mxaimanager\app\ai\provider\providers\interfaces\chat_completion;
use local_mxaimanager\app\ai\provider\providers\interfaces\create_embedding;
use local_mxaimanager\app\ai\provider\providers\interfaces\create_image;
use local_mxaimanager\app\ai\provider\providers\interfaces\create_transcription;
use local_mxaimanager\app\exceptions\invalid_provider_instance_response;
use local_mxaimanager\app\factory;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Unit test class for the Gemini provider.
 *
 * Contains tests for the constructor, chat, embeddings, image generation,
 * audio transcription methods, and Moodle form integration.
 */
class gemini_test extends \base_testcase
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
            'api_key' => 'test_key',
            'chat_model' => 'gemini-pro',
            'embedding_model' => 'text-embedding-004',
            'image_model' => 'gemini-3.1-flash-image-preview',
            'transcription_model' => 'gemini-3-flash-preview'
        ];

        $provider = new gemini(
            $this->mock_base_factory,
            $json_config
        );

        $this->assertInstanceOf(gemini::class, $provider);
    }

    /**
     * Tests that the constructor throws an exception if the API key is missing.
     */
    public function test_constructor_missing_api_key(): void
    {
        $json_config = [
            'chat_model' => 'gemini-pro',
            'embedding_model' => 'text-embedding-004',
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Gemini is missing api key');

        new gemini(
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
            'api_key' => 'test_key',
            'chat_model' => 'gemini-pro',
        ];

        $expected_response = '{"candidates":[{"content":{"parts":[{"text":"Hello, world!"}]}}], "usageMetadata":{"promptTokenCount": 5, "candidatesTokenCount": 5}}';

        $this->mock_curl->expects($this->once())
            ->method('post')
            ->with(
                'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=test_key',
                $this->callback(function ($data) {
                    $decoded = json_decode($data, true);
                    return isset($decoded['contents']);
                })
            )
            ->willReturn($expected_response);

        $provider = new gemini(
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
            'api_key' => 'test_key',
        ];

        $provider = new gemini(
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
            'api_key' => 'test_key',
            'chat_model' => 'gemini-pro',
        ];

        $this->mock_curl->expects($this->once())
            ->method('post')
            ->willReturn('invalid json');

        $provider = new gemini(
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
            'api_key' => 'test_key',
            'embedding_model' => 'text-embedding-004'
        ];

        $expected_response = '{"embedding":{"values":[0.1, 0.2, 0.3]}}';

        $this->mock_curl->expects($this->once())
            ->method('post')
            ->with(
                'https://generativelanguage.googleapis.com/v1beta/models/text-embedding-004:embedContent?key=test_key',
                $this->callback(function ($data) {
                    $decoded = json_decode($data, true);
                    return isset($decoded['model']) &&
                        $decoded['model'] === 'models/text-embedding-004' &&
                        isset($decoded['content']);
                })
            )
            ->willReturn($expected_response);

        $provider = new gemini(
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
            'api_key' => 'test_key',
        ];

        $provider = new gemini(
            $this->mock_base_factory,
            $json_config
        );

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Embedding model is not configured');

        $provider->get_embedding('test input', null);
    }

    /**
     * Tests that image creation is successful and returns the expected base64 data.
     */
    public function test_create_image_success(): void
    {
        $json_config = [
            'api_key' => 'test_key',
            'image_model' => 'gemini-3.1-flash-image-preview'
        ];

        $expected_response = '{"candidates":[{"content":{"parts":[{"inline_data":{"data":"base64encodedimage"}}]}}]}';

        $this->mock_curl->expects($this->once())
            ->method('post')
            ->with(
                'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.1-flash-image-preview:generateContent?key=test_key',
                $this->callback(function ($data) {
                    $decoded = json_decode($data, true);
                    return isset($decoded['contents'][0]['parts'][0]['text']) &&
                        $decoded['contents'][0]['parts'][0]['text'] === 'A test image';
                })
            )
            ->willReturn($expected_response);

        $provider = new gemini(
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
            'api_key' => 'test_key',
        ];

        $provider = new gemini(
            $this->mock_base_factory,
            $json_config
        );

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Image model is not configured');

        $provider->create_image('A test image', true);
    }

    /**
     * Tests that audio transcription is successful and returns the expected text.
     */
    public function test_create_transcription_success(): void
    {
        $json_config = [
            'api_key' => 'test_key',
            'transcription_model' => 'gemini-3-flash-preview'
        ];

        $expected_response = '{"candidates":[{"content":{"parts":[{"text":"Hello, world!"}]}}]}';

        $this->mock_curl->expects($this->once())
            ->method('post')
            ->with(
                'https://generativelanguage.googleapis.com/v1beta/models/gemini-3-flash-preview:generateContent?key=test_key',
                $this->callback(function ($data) {
                    $decoded = json_decode($data, true);
                    return isset($decoded['contents'][0]['parts'][1]['inline_data']['data']);
                })
            )
            ->willReturn($expected_response);

        $provider = new gemini(
            $this->mock_base_factory,
            $json_config
        );

        $temp_file = tempnam(sys_get_temp_dir(), 'test_audio');
        file_put_contents($temp_file, 'fake audio content');

        $result = $provider->create_transcription($temp_file);

        $this->assertEquals('Hello, world!', $result->get_response()->get_text());

        unlink($temp_file);
    }

    /**
     * Tests that `create_transcription` throws an exception if the transcription model is not configured.
     */
    public function test_create_transcription_missing_model(): void
    {
        $json_config = [
            'api_key' => 'test_key',
        ];

        $provider = new gemini(
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
     * Tests that the Moodle form definition for Gemini is correctly set up.
     */
    public function test_moodleform_definition(): void
    {
        $mform = $this->createMock(\MoodleQuickForm::class);

        $mform->expects($this->exactly(5))->method('addElement');
        $mform->expects($this->exactly(5))->method('setType');
        $mform->expects($this->exactly(1))->method('setDefault');

        $element_name_prefix = 'test_';

        gemini::moodleform_definition(
            $mform,
            $element_name_prefix
        );

        $this->assertTrue(true);
    }

    /**
     * Tests that Moodle form validation for Gemini works with valid data.
     */
    public function test_moodleform_validation_with_valid_data(): void
    {
        $data = [
            'prefix_api_key' => 'test_key',
            'prefix_chat_model' => 'gemini-pro',
            'prefix_embedding_model' => 'text-embedding-004',
            'prefix_image_model' => 'gemini-3.1-flash-image-preview',
            'prefix_transcription_model' => 'gemini-3-flash-preview'
        ];

        $errors = gemini::moodleform_validation(
            $data,
            'prefix_'
        );

        $this->assertEmpty($errors);
    }

    /**
     * Tests that Moodle form validation for Gemini detects a missing API key.
     */
    public function test_moodleform_validation_missing_api_key(): void
    {
        $data = [];

        $errors = gemini::moodleform_validation(
            $data,
            'prefix_'
        );

        $this->assertArrayHasKey('prefix_api_key', $errors);
    }
}
