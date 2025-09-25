<?php

namespace local_mxaimanager\unit\app\ai\feature;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

use base_testcase;
use Exception;
use local_mxaimanager\app\factory as base_factory;
use local_mxaimanager\app\ai\factory as ai_factory;
use local_mxaimanager\app\ai\provider\factory as provider_factory;
use local_mxaimanager\app\ai\feature\action_handler;
use local_mxaimanager\app\ai\provider\entity as provider_entity;

class action_handler_test extends base_testcase
{
    public function test_chat_completion_success(): void
    {
        // Create mock provider
        $provider_mock = $this->createMock(provider_entity::class);
        $provider_mock->method('get_classname')
            ->willReturn(mock_chat_completion_handler::class);
        $provider_mock->method('get_config_json')
            ->willReturn('{"api_key": "test"}');
        $provider_mock->method('get_id')
            ->willReturn(1);

        // Mock factory chains
        $provider_repository_mock = $this->createMock(\local_mxaimanager\app\ai\provider\repository::class);
        $provider_repository_mock->expects($this->once())
            ->method('get_by_id')
            ->with(1)
            ->willReturn($provider_mock);

        $provider_factory_mock = $this->createMock(provider_factory::class);
        $provider_factory_mock->method('repository')
            ->willReturn($provider_repository_mock);

        $ai_factory_mock = $this->createMock(ai_factory::class);
        $ai_factory_mock->method('provider')
            ->willReturn($provider_factory_mock);

        $base_factory_mock = $this->createMock(base_factory::class);
        $base_factory_mock->method('ai')
            ->willReturn($ai_factory_mock);

        // Create action handler
        $action_handler = new action_handler($base_factory_mock);

        // Execute test
        $messages = [['role' => 'user', 'content' => 'Hello']];
        $result = $action_handler->chat_completion($messages, 1, '{"model": "gpt-3.5"}');

        // Assert
        $this->assertEquals('Mock response', $result);
    }

    public function test_chat_completion_missing_model(): void
    {
        // Create mock provider
        $provider_mock = $this->createMock(provider_entity::class);
        $provider_mock->method('get_classname')
            ->willReturn(mock_chat_completion_handler::class);
        $provider_mock->method('get_config_json')
            ->willReturn('{"api_key": "test"}');
        $provider_mock->method('get_id')
            ->willReturn(1);

        // Mock factory chains
        $provider_repository_mock = $this->createMock(\local_mxaimanager\app\ai\provider\repository::class);
        $provider_repository_mock->method('get_by_id')
            ->willReturn($provider_mock);

        $provider_factory_mock = $this->createMock(provider_factory::class);
        $provider_factory_mock->method('repository')
            ->willReturn($provider_repository_mock);

        $ai_factory_mock = $this->createMock(ai_factory::class);
        $ai_factory_mock->method('provider')
            ->willReturn($provider_factory_mock);

        $base_factory_mock = $this->createMock(base_factory::class);
        $base_factory_mock->method('ai')
            ->willReturn($ai_factory_mock);

        // Create action handler
        $action_handler = new action_handler($base_factory_mock);

        // Execute test - should throw exception for missing model
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No model configured for chat completion with provider ID 1');

        $messages = [['role' => 'user', 'content' => 'Hello']];
        $action_handler->chat_completion($messages, 1, '{"other_setting": "value"}');
    }

    public function test_create_embedding_success(): void
    {
        // Create mock provider
        $provider_mock = $this->createMock(provider_entity::class);
        $provider_mock->method('get_classname')
            ->willReturn(mock_create_embedding_handler::class);
        $provider_mock->method('get_config_json')
            ->willReturn('{"api_key": "test"}');
        $provider_mock->method('get_id')
            ->willReturn(2);

        // Mock factory chains
        $provider_repository_mock = $this->createMock(\local_mxaimanager\app\ai\provider\repository::class);
        $provider_repository_mock->method('get_by_id')
            ->willReturn($provider_mock);

        $provider_factory_mock = $this->createMock(provider_factory::class);
        $provider_factory_mock->method('repository')
            ->willReturn($provider_repository_mock);

        $ai_factory_mock = $this->createMock(ai_factory::class);
        $ai_factory_mock->method('provider')
            ->willReturn($provider_factory_mock);

        $base_factory_mock = $this->createMock(base_factory::class);
        $base_factory_mock->method('ai')
            ->willReturn($ai_factory_mock);

        // Create action handler
        $action_handler = new action_handler($base_factory_mock);

        // Execute test
        $result = $action_handler->create_embedding('Hello world', 1536, 2, '{"model": "text-embedding"}');

        // Assert
        $this->assertEquals([0.1, 0.2, 0.3], $result);
    }

    public function test_create_embedding_missing_model(): void
    {
        // Create mock provider
        $provider_mock = $this->createMock(provider_entity::class);
        $provider_mock->method('get_classname')
            ->willReturn(mock_create_embedding_handler::class);
        $provider_mock->method('get_config_json')
            ->willReturn('{"api_key": "test"}');
        $provider_mock->method('get_id')
            ->willReturn(2);

        // Mock factory chains
        $provider_repository_mock = $this->createMock(\local_mxaimanager\app\ai\provider\repository::class);
        $provider_repository_mock->method('get_by_id')
            ->willReturn($provider_mock);

        $provider_factory_mock = $this->createMock(provider_factory::class);
        $provider_factory_mock->method('repository')
            ->willReturn($provider_repository_mock);

        $ai_factory_mock = $this->createMock(ai_factory::class);
        $ai_factory_mock->method('provider')
            ->willReturn($provider_factory_mock);

        $base_factory_mock = $this->createMock(base_factory::class);
        $base_factory_mock->method('ai')
            ->willReturn($ai_factory_mock);

        // Create action handler
        $action_handler = new action_handler($base_factory_mock);

        // Execute test - should throw exception for missing model
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No model configured for embedding creation with provider ID 2');

        $action_handler->create_embedding('Hello world', 1536, 2, '{"other_setting": "value"}');
    }

    public function test_chat_completion_unsupported_interface(): void
    {
        // Create mock provider that returns unsupported handler
        $provider_mock = $this->createMock(provider_entity::class);
        $provider_mock->method('get_classname')
            ->willReturn(mock_unsupported_handler::class);
        $provider_mock->method('get_config_json')
            ->willReturn('{"api_key": "test"}');
        $provider_mock->method('get_id')
            ->willReturn(3);

        // Mock factory chains
        $provider_repository_mock = $this->createMock(\local_mxaimanager\app\ai\provider\repository::class);
        $provider_repository_mock->method('get_by_id')
            ->willReturn($provider_mock);

        $provider_factory_mock = $this->createMock(provider_factory::class);
        $provider_factory_mock->method('repository')
            ->willReturn($provider_repository_mock);

        $ai_factory_mock = $this->createMock(ai_factory::class);
        $ai_factory_mock->method('provider')
            ->willReturn($provider_factory_mock);

        $base_factory_mock = $this->createMock(base_factory::class);
        $base_factory_mock->method('ai')
            ->willReturn($ai_factory_mock);

        // Create action handler
        $action_handler = new action_handler($base_factory_mock);

        // Execute test - should throw exception for unsupported interface
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Provider ID 3 does not support the required interface');

        $messages = [['role' => 'user', 'content' => 'Hello']];
        $action_handler->chat_completion($messages, 3, '{"model": "gpt-3.5"}');
    }
}

// Mock classes for dynamic instantiation
class mock_chat_completion_handler implements \local_mxaimanager\app\ai\provider\providers\interfaces\chat_completion
{
    public function __construct(\local_mxaimanager\app\factory $factory, array $config_json)
    {
    }

    public function chat_completion(array $messages, string $model): string
    {
        return 'Mock response';
    }
}

class mock_create_embedding_handler implements \local_mxaimanager\app\ai\provider\providers\interfaces\create_embedding
{
    public function __construct(\local_mxaimanager\app\factory $factory, array $config_json)
    {
    }

    public function get_embedding(string $input, string $model, int $dimension): array
    {
        return [0.1, 0.2, 0.3];
    }
}

class mock_unsupported_handler
{
    public function __construct(\local_mxaimanager\app\factory $factory, array $config_json)
    {
    }
}
