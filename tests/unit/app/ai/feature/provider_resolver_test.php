<?php

namespace local_mxaimanager\unit\app\ai\feature;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

use base_testcase;
use Exception;
use local_mxaimanager\app\ai\action\factory as ai_action_factory;
use local_mxaimanager\app\ai\default_provider\entity as default_provider_entity;
use local_mxaimanager\app\ai\default_provider\factory as default_provider_factory;
use local_mxaimanager\app\ai\factory as ai_factory;
use local_mxaimanager\app\ai\feature\action\entity as feature_action_entity;
use local_mxaimanager\app\ai\feature\action\factory as feature_action_factory;
use local_mxaimanager\app\ai\feature\entity;
use local_mxaimanager\app\ai\feature\factory as feature_factory;
use local_mxaimanager\app\ai\feature\provider_resolver;
use local_mxaimanager\app\ai\provider\action\entity as provider_action_entity;
use local_mxaimanager\app\ai\provider\action\factory as provider_action_factory;
use local_mxaimanager\app\ai\provider\factory as provider_factory;
use local_mxaimanager\app\factory as base_factory;

class provider_resolver_test extends base_testcase
{
    public function test_get_provider_and_settings_with_feature_action_complete(): void
    {
        // Create mocks
        $feature_action_entity_mock = $this->createMock(feature_action_entity::class);
        $feature_entity_mock = $this->createMock(entity::class);

        // Mock entities
        $feature_action_entity_mock
            ->method('get_provider_id')
            ->willReturn(1);
        $feature_action_entity_mock
            ->method('get_settings_json')
            ->willReturn('{"feature_settings": "value"}');

        // Mock factory chains
        $feature_action_repository_mock = $this->createMock(\local_mxaimanager\app\ai\feature\action\repository::class);
        $feature_action_factory_mock = $this->createMock(feature_action_factory::class);
        $feature_factory_mock = $this->createMock(feature_factory::class);
        $ai_factory_mock = $this->createMock(ai_factory::class);
        $base_factory_mock = $this->createMock(base_factory::class);

        // Set up feature entity id
        $feature_entity_mock
            ->method('get_id')
            ->willReturn(10);

        // Set up factory chain for feature action lookup
        $base_factory_mock
            ->method('ai')
            ->willReturn($ai_factory_mock);

        $ai_factory_mock
            ->method('feature')
            ->willReturn($feature_factory_mock);

        $feature_factory_mock
            ->method('action')
            ->willReturn($feature_action_factory_mock);

        $feature_action_factory_mock
            ->method('repository')
            ->willReturn($feature_action_repository_mock);

        $feature_action_repository_mock->expects($this->once())
            ->method('get_by_feature_id_and_action_id')
            ->with(10, 5)
            ->willReturn($feature_action_entity_mock);

        // Create resolver
        $resolver = new provider_resolver($base_factory_mock, $feature_entity_mock);

        // Execute test - should return feature-specific values without further calls
        $result = $resolver->get_provider_and_settings(5);

        // Assert results
        $this->assertEquals([1, '{"feature_settings": "value"}'], $result);
    }

    public function test_get_provider_and_settings_with_feature_action_provider_only(): void
    {
        // Tests the case where feature action exists with provider but empty settings,
        // so it uses the feature provider + falls back to provider action settings
        $feature_action_entity_mock = $this->createMock(feature_action_entity::class);
        $provider_action_entity_mock = $this->createMock(provider_action_entity::class);
        $feature_entity_mock = $this->createMock(entity::class);

        // Mock feature action - has provider but empty settings
        $feature_action_entity_mock
            ->method('get_provider_id')
            ->willReturn(2);
        $feature_action_entity_mock
            ->method('get_settings_json')
            ->willReturn('');

        // Mock provider action settings
        $provider_action_entity_mock->expects($this->once())
            ->method('get_settings_json')
            ->willReturn('{"fallback": "settings"}');

        // Mock factory chains
        $feature_action_repository_mock = $this->createMock(\local_mxaimanager\app\ai\feature\action\repository::class);
        $feature_action_factory_mock = $this->createMock(feature_action_factory::class);
        $provider_action_repository_mock = $this->createMock(
            \local_mxaimanager\app\ai\provider\action\repository::class
        );
        $provider_action_factory_mock = $this->createMock(provider_action_factory::class);
        $feature_factory_mock = $this->createMock(feature_factory::class);
        $provider_factory_mock = $this->createMock(provider_factory::class);
        $ai_factory_mock = $this->createMock(ai_factory::class);
        $base_factory_mock = $this->createMock(base_factory::class);

        // Set up feature entity id
        $feature_entity_mock
            ->method('get_id')
            ->willReturn(10);

        // Set up factory chains
        $base_factory_mock
            ->method('ai')
            ->willReturn($ai_factory_mock);

        $ai_factory_mock
            ->method('feature')
            ->willReturn($feature_factory_mock);

        $ai_factory_mock
            ->method('provider')
            ->willReturn($provider_factory_mock);

        $feature_factory_mock
            ->method('action')
            ->willReturn($feature_action_factory_mock);

        $provider_factory_mock
            ->method('action')
            ->willReturn($provider_action_factory_mock);

        $feature_action_factory_mock
            ->method('repository')
            ->willReturn($feature_action_repository_mock);

        $provider_action_factory_mock
            ->method('repository')
            ->willReturn($provider_action_repository_mock);

        $feature_action_repository_mock->expects($this->once())
            ->method('get_by_feature_id_and_action_id')
            ->with(10, 5)
            ->willReturn($feature_action_entity_mock);

        $provider_action_repository_mock->expects($this->once())
            ->method('get_by_action_id_and_provider_id')
            ->with(5, 2)
            ->willReturn($provider_action_entity_mock);

        // Create resolver
        $resolver = new provider_resolver($base_factory_mock, $feature_entity_mock);

        // Execute test
        $result = $resolver->get_provider_and_settings(5);

        // Assert results - should use feature provider (2) + fallback settings
        $this->assertEquals([2, '{"fallback": "settings"}'], $result);
    }

    public function test_get_provider_and_settings_with_no_feature_action(): void
    {
        // Create mocks
        $default_provider_entity_mock = $this->createMock(default_provider_entity::class);
        $provider_action_entity_mock = $this->createMock(provider_action_entity::class);
        $feature_entity_mock = $this->createMock(entity::class);

        // Mock exceptions - feature action not found
        $feature_action_repository_mock = $this->createMock(\local_mxaimanager\app\ai\feature\action\repository::class);
        $feature_action_repository_mock->expects($this->once())
            ->method('get_by_feature_id_and_action_id')
            ->with(10, 5)
            ->willThrowException(new \dml_missing_record_exception('feature_action', 'feature_id', [10, 5]));

        // Mock default provider
        $default_provider_entity_mock
            ->method('get_provider_id')
            ->willReturn(3);

        // Mock provider action settings
        $provider_action_entity_mock->expects($this->once())
            ->method('get_settings_json')
            ->willReturn('{"default_settings": "value"}');

        // Mock factory chains
        $feature_action_factory_mock = $this->createMock(feature_action_factory::class);
        $default_provider_repository_mock = $this->createMock(
            \local_mxaimanager\app\ai\default_provider\repository::class
        );
        $default_provider_factory_mock = $this->createMock(default_provider_factory::class);
        $provider_action_repository_mock = $this->createMock(
            \local_mxaimanager\app\ai\provider\action\repository::class
        );
        $provider_action_factory_mock = $this->createMock(provider_action_factory::class);
        $feature_factory_mock = $this->createMock(feature_factory::class);
        $ai_action_factory_mock = $this->createMock(ai_action_factory::class);
        $provider_factory_mock = $this->createMock(provider_factory::class);
        $ai_factory_mock = $this->createMock(ai_factory::class);
        $base_factory_mock = $this->createMock(base_factory::class);

        // Set up feature entity id
        $feature_entity_mock
            ->method('get_id')
            ->willReturn(10);

        // Set up factory chains
        $base_factory_mock
            ->method('ai')
            ->willReturn($ai_factory_mock);

        $ai_factory_mock
            ->method('feature')
            ->willReturn($feature_factory_mock);

        $ai_factory_mock
            ->method('action')
            ->willReturn($ai_action_factory_mock);

        $ai_factory_mock
            ->method('provider')
            ->willReturn($provider_factory_mock);

        $feature_factory_mock
            ->method('action')
            ->willReturn($feature_action_factory_mock);

        $ai_action_factory_mock
            ->method('default_provider')
            ->willReturn($default_provider_factory_mock);

        $provider_factory_mock
            ->method('action')
            ->willReturn($provider_action_factory_mock);

        $feature_action_factory_mock
            ->method('repository')
            ->willReturn($feature_action_repository_mock);

        $default_provider_factory_mock
            ->method('repository')
            ->willReturn($default_provider_repository_mock);

        $provider_action_factory_mock
            ->method('repository')
            ->willReturn($provider_action_repository_mock);

        $default_provider_repository_mock->expects($this->once())
            ->method('get_by_action_id')
            ->with(5)
            ->willReturn($default_provider_entity_mock);

        $provider_action_repository_mock->expects($this->once())
            ->method('get_by_action_id_and_provider_id')
            ->with(5, 3)
            ->willReturn($provider_action_entity_mock);

        // Create resolver
        $resolver = new provider_resolver($base_factory_mock, $feature_entity_mock);

        // Execute test - should use default provider + provider action settings
        $result = $resolver->get_provider_and_settings(5);

        // Assert results
        $this->assertEquals([3, '{"default_settings": "value"}'], $result);
    }

    public function test_get_provider_and_settings_missing_default_provider_exception(): void
    {
        $feature_entity_mock = $this->createMock(entity::class);

        // Mock exceptions
        $feature_action_repository_mock = $this->createMock(\local_mxaimanager\app\ai\feature\action\repository::class);
        $feature_action_repository_mock->expects($this->once())
            ->method('get_by_feature_id_and_action_id')
            ->with(10, 5)
            ->willThrowException(new \dml_missing_record_exception('feature_action', 'feature_id', [10, 5]));

        $default_provider_repository_mock = $this->createMock(
            \local_mxaimanager\app\ai\default_provider\repository::class
        );
        $default_provider_repository_mock->expects($this->once())
            ->method('get_by_action_id')
            ->with(5)
            ->willThrowException(new \dml_missing_record_exception('default_provider', 'action_id', [5]));

        // Mock factory chains
        $feature_action_factory_mock = $this->createMock(feature_action_factory::class);
        $default_provider_factory_mock = $this->createMock(default_provider_factory::class);
        $feature_factory_mock = $this->createMock(feature_factory::class);
        $ai_action_factory_mock = $this->createMock(ai_action_factory::class);
        $ai_factory_mock = $this->createMock(ai_factory::class);
        $base_factory_mock = $this->createMock(base_factory::class);

        // Set up feature entity id
        $feature_entity_mock
            ->method('get_id')
            ->willReturn(10);

        // Set up factory chains
        $base_factory_mock
            ->method('ai')
            ->willReturn($ai_factory_mock);

        $ai_factory_mock
            ->method('feature')
            ->willReturn($feature_factory_mock);

        $ai_factory_mock
            ->method('action')
            ->willReturn($ai_action_factory_mock);

        $feature_factory_mock
            ->method('action')
            ->willReturn($feature_action_factory_mock);

        $ai_action_factory_mock
            ->method('default_provider')
            ->willReturn($default_provider_factory_mock);

        $feature_action_factory_mock
            ->method('repository')
            ->willReturn($feature_action_repository_mock);

        $default_provider_factory_mock
            ->method('repository')
            ->willReturn($default_provider_repository_mock);

        // Create resolver
        $resolver = new provider_resolver($base_factory_mock, $feature_entity_mock);

        // Execute test
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No default provider configured for action ID 5');

        $resolver->get_provider_and_settings(5);
    }

    public function test_get_provider_and_settings_missing_provider_action_settings_exception(): void
    {
        // This tests the branch where provider action settings are not found
        $default_provider_entity_mock = $this->createMock(default_provider_entity::class);
        $feature_entity_mock = $this->createMock(entity::class);

        // Mock feature action not found
        $feature_action_repository_mock = $this->createMock(\local_mxaimanager\app\ai\feature\action\repository::class);
        $feature_action_repository_mock->expects($this->once())
            ->method('get_by_feature_id_and_action_id')
            ->with(10, 5)
            ->willThrowException(new \dml_missing_record_exception('feature_action', 'feature_id', [10, 5]));

        // Mock default provider
        $default_provider_entity_mock
            ->method('get_provider_id')
            ->willReturn(3);

        // Mock provider action settings not found
        $provider_action_repository_mock = $this->createMock(
            \local_mxaimanager\app\ai\provider\action\repository::class
        );
        $provider_action_repository_mock->expects($this->once())
            ->method('get_by_action_id_and_provider_id')
            ->with(5, 3)
            ->willThrowException(new \dml_missing_record_exception('provider_action', 'action_id', [5, 3]));

        // Mock factory chains
        $feature_action_factory_mock = $this->createMock(feature_action_factory::class);
        $default_provider_repository_mock = $this->createMock(
            \local_mxaimanager\app\ai\default_provider\repository::class
        );
        $default_provider_factory_mock = $this->createMock(default_provider_factory::class);
        $provider_action_factory_mock = $this->createMock(provider_action_factory::class);
        $feature_factory_mock = $this->createMock(feature_factory::class);
        $ai_action_factory_mock = $this->createMock(ai_action_factory::class);
        $provider_factory_mock = $this->createMock(provider_factory::class);
        $ai_factory_mock = $this->createMock(ai_factory::class);
        $base_factory_mock = $this->createMock(base_factory::class);

        // Set up feature entity id
        $feature_entity_mock
            ->method('get_id')
            ->willReturn(10);

        // Set up factory chains
        $base_factory_mock
            ->method('ai')
            ->willReturn($ai_factory_mock);

        $ai_factory_mock
            ->method('feature')
            ->willReturn($feature_factory_mock);

        $ai_factory_mock
            ->method('action')
            ->willReturn($ai_action_factory_mock);

        $ai_factory_mock
            ->method('provider')
            ->willReturn($provider_factory_mock);

        $feature_factory_mock
            ->method('action')
            ->willReturn($feature_action_factory_mock);

        $ai_action_factory_mock
            ->method('default_provider')
            ->willReturn($default_provider_factory_mock);

        $provider_factory_mock
            ->method('action')
            ->willReturn($provider_action_factory_mock);

        $feature_action_factory_mock
            ->method('repository')
            ->willReturn($feature_action_repository_mock);

        $default_provider_factory_mock
            ->method('repository')
            ->willReturn($default_provider_repository_mock);

        $provider_action_factory_mock
            ->method('repository')
            ->willReturn($provider_action_repository_mock);

        $default_provider_repository_mock->expects($this->once())
            ->method('get_by_action_id')
            ->with(5)
            ->willReturn($default_provider_entity_mock);

        // Create resolver
        $resolver = new provider_resolver($base_factory_mock, $feature_entity_mock);

        // Execute test - should throw exception for missing provider action settings
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No default action settings configured for action ID 5 with provider ID 3');

        $resolver->get_provider_and_settings(5);
    }
}
