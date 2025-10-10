<?php

namespace local_mxaimanager\unit\app\ai\feature;

// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

use base_testcase;
use Exception;
use local_mxaimanager\app\exceptions\invalid_provider_instance_configuration;
use local_mxaimanager\app\factory as base_factory;
use local_mxaimanager\app\ai\provider\message;
use local_mxaimanager\app\ai\feature\action_handler;
use local_mxaimanager\app\ai\provider\providers\interfaces\chat_completion;
use local_mxaimanager\app\ai\provider\providers\interfaces\create_embedding;

/**
 * Mock handler class that has methods but doesn't implement interfaces
 */
class MockHandlerWithoutInterface
{
    public function chat_completion(array $messages): string
    {
        return 'mocked response';
    }

    public function get_embedding(string $input, int $dimension): array
    {
        return [0.1, 0.2, 0.3];
    }
}

class action_handler_test extends base_testcase
{
    public function test_chat_completion_success(): void
    {
        // Create minimal mocks needed
        $base_factory_mock = $this->createMock(base_factory::class);
        $handler_mock = $this->createMock(chat_completion::class);

        // Mock the protected provider instantiation method
        $handler = $this->getMockBuilder(action_handler::class)
            ->setConstructorArgs([$base_factory_mock])
            ->onlyMethods(['get_provider_handler_provider_and_settings_json'])
            ->getMock();

        $handler->expects($this->once())
            ->method('get_provider_handler_provider_and_settings_json')
            ->with(1, ['api_key' => 'test'])
            ->willReturn($handler_mock);

        // Mock the chat completion call
        $handler_mock->expects($this->once())
            ->method('chat_completion')
            ->with([
                new message('user', 'Hello'),
                new message('assistant', 'Hi there')
            ])
            ->willReturn('Response from AI');

        // Execute test
        $messages = [
            new message('user', 'Hello'),
            new message('assistant', 'Hi there')
        ];

        $result = $handler->chat_completion($messages, 1, ['api_key' => 'test']);

        // Assert
        $this->assertEquals('Response from AI', $result);
    }

    public function test_chat_completion_provider_not_supporting_interface(): void
    {
        // Create instance of mock handler class that has methods but doesn't implement interfaces
        $handler_instance = new MockHandlerWithoutInterface();

        // Setup minimal mocks
        $base_factory_mock = $this->createMock(base_factory::class);

        // Mock handler instantiation
        $handler = $this->getMockBuilder(action_handler::class)
            ->setConstructorArgs([$base_factory_mock])
            ->onlyMethods(['get_provider_handler_provider_and_settings_json'])
            ->getMock();

        $handler->expects($this->once())
            ->method('get_provider_handler_provider_and_settings_json')
            ->with(2, ['api_key' => 'test'])
            ->willReturn($handler_instance);

        // Execute test and expect exception
        $messages = [new message('user', 'Hello')];

        $this->expectException(invalid_provider_instance_configuration::class);
        $this->expectExceptionMessage('Provider instance ID: 2 does not support chat completion');

        $handler->chat_completion($messages, 2, ['api_key' => 'test']);
    }

    public function test_create_embedding_success(): void
    {
        // Create minimal mocks needed
        $base_factory_mock = $this->createMock(base_factory::class);
        $handler_mock = $this->createMock(create_embedding::class);

        // Mock the protected provider instantiation method
        $handler = $this->getMockBuilder(action_handler::class)
            ->setConstructorArgs([$base_factory_mock])
            ->onlyMethods(['get_provider_handler_provider_and_settings_json'])
            ->getMock();

        $handler->expects($this->once())
            ->method('get_provider_handler_provider_and_settings_json')
            ->with(3, ['model' => 'embedding-model'])
            ->willReturn($handler_mock);

        // Mock the embedding call
        $handler_mock->expects($this->once())
            ->method('get_embedding')
            ->with('Hello world', 512)
            ->willReturn([0.123, -0.456, 0.789]);

        // Execute test
        $result = $handler->create_embedding('Hello world', 512, 3, ['model' => 'embedding-model']);

        // Assert
        $this->assertEquals([0.123, -0.456, 0.789], $result);
    }

    public function test_create_embedding_provider_not_supporting_interface(): void
    {
        // Create instance of mock handler class that has methods but doesn't implement interfaces
        $handler_instance = new MockHandlerWithoutInterface();

        // Setup minimal mocks
        $base_factory_mock = $this->createMock(base_factory::class);

        // Mock handler instantiation
        $handler = $this->getMockBuilder(action_handler::class)
            ->setConstructorArgs([$base_factory_mock])
            ->onlyMethods(['get_provider_handler_provider_and_settings_json'])
            ->getMock();

        $handler->expects($this->once())
            ->method('get_provider_handler_provider_and_settings_json')
            ->with(4, ['model' => 'test'])
            ->willReturn($handler_instance);

        // Execute test and expect exception
        $this->expectException(invalid_provider_instance_configuration::class);
        $this->expectExceptionMessage('Provider instance ID: 4 does not support embedding creation');

        $handler->create_embedding('Hello world', 256, 4, ['model' => 'test']);
    }


}
