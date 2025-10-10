<?php

namespace local_mxaimanager\integration\app\ai\feature\action;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

use local_mxaimanager\app\factory as base_factory;

class repository_test extends \advanced_testcase
{
    protected function setUp(): void
    {
        $this->resetAfterTest();

        $base_factory = \local_mxaimanager\app\factory::make();

        // Clean up any existing feature actions to ensure a fresh state.
        $actions = $base_factory->ai()->feature()->action()->repository()->get_all();
        foreach ($actions as $action) {
            $base_factory->ai()->feature()->action()->repository()->delete($action->get_id());
        }

        // Clean up any existing features.
        $features = $base_factory->ai()->feature()->repository()->get_all();
        foreach ($features as $feature) {
            $base_factory->ai()->feature()->repository()->delete($feature->get_id());
        }
    }

    public function test_get_by_id(): void
    {
        // Setup test data
        $record_id = $this->insert_test_record([
            'feature_id' => 100,
            'action_interface' => 'chat_completion',
            'provider_id' => 1,
            'settings_json' => null,
        ]);

        $entity = \local_mxaimanager\app\factory::make()->ai()->feature()->action()->repository()->get_by_id(
            $record_id
        );

        // Assert entity properties
        $this->assertEquals(100, $entity->get_feature_id());
        $this->assertEquals('chat_completion', $entity->get_action_interface());
        $this->assertEquals(1, $entity->get_provider_id());
    }

    public function test_get_by_id_not_found(): void
    {
        $this->expectException(\dml_missing_record_exception::class);
        \local_mxaimanager\app\factory::make()->ai()->feature()->action()->repository()->get_by_id(999999);
    }

    public function test_get_all_by_feature_id(): void
    {
        // Setup multiple records for same feature
        $this->insert_test_record([
            'feature_id' => 50,
            'action_interface' => 'chat_completion',
            'provider_id' => 1,
            'settings_json' => null,
        ]);
        $this->insert_test_record([
            'feature_id' => 50,
            'action_interface' => 'image_generation',
            'provider_id' => 2,
            'settings_json' => null,
        ]);
        // Different feature
        $this->insert_test_record([
            'feature_id' => 51,
            'action_interface' => 'chat_completion',
            'provider_id' => 1,
            'settings_json' => null,
        ]);

        $collection = \local_mxaimanager\app\factory::make()->ai()->feature()->action()->repository(
        )->get_all_by_feature_id(50);

        $this->assertCount(2, $collection);
        $entities = iterator_to_array($collection, false);

        // Assert both entities belong to feature 50
        $this->assertEquals(50, $entities[0]->get_feature_id());
        $this->assertEquals(50, $entities[1]->get_feature_id());

        // Assert different action interfaces
        $interfaces = array_map(fn($e) => $e->get_action_interface(), $entities);
        $this->assertContains('chat_completion', $interfaces);
        $this->assertContains('image_generation', $interfaces);
    }

    public function test_get_all_by_feature_id_no_records(): void
    {
        $collection = \local_mxaimanager\app\factory::make()->ai()->feature()->action()->repository(
        )->get_all_by_feature_id(999);

        $this->assertCount(0, $collection);
    }

    public function test_get_all_by_action_interface(): void
    {
        // Setup multiple records for same action interface
        $this->insert_test_record([
            'feature_id' => 10,
            'action_interface' => 'chat_completion',
            'provider_id' => 1,
            'settings_json' => null,
        ]);
        $this->insert_test_record([
            'feature_id' => 20,
            'action_interface' => 'chat_completion',
            'provider_id' => 2,
            'settings_json' => null,
        ]);
        // Different action interface
        $this->insert_test_record([
            'feature_id' => 30,
            'action_interface' => 'embedding',
            'provider_id' => 1,
            'settings_json' => null,
        ]);

        $collection = \local_mxaimanager\app\factory::make()->ai()->feature()->action()->repository(
        )->get_all_by_action_interface('chat_completion');

        $this->assertCount(2, $collection);
        $entities = iterator_to_array($collection, false);

        // All should have chat_completion action
        foreach ($entities as $entity) {
            $this->assertEquals('chat_completion', $entity->get_action_interface());
        }

        // Should have different feature_ids
        $feature_ids = array_map(fn($e) => $e->get_feature_id(), $entities);
        $this->assertContains(10, $feature_ids);
        $this->assertContains(20, $feature_ids);
    }

    public function test_get_all_by_provider_id(): void
    {
        // Setup records with same provider
        $this->insert_test_record([
            'feature_id' => 100,
            'action_interface' => 'chat_completion',
            'provider_id' => 5,
        ]);
        $this->insert_test_record([
            'feature_id' => 101,
            'action_interface' => 'embedding',
            'provider_id' => 5,
        ]);
        // Different provider
        $this->insert_test_record([
            'feature_id' => 102,
            'action_interface' => 'chat_completion',
            'provider_id' => 6,
        ]);

        $collection = \local_mxaimanager\app\factory::make()->ai()->feature()->action()->repository(
        )->get_all_by_provider_id(5);

        $this->assertCount(2, $collection);
        $entities = iterator_to_array($collection, false);

        foreach ($entities as $entity) {
            $this->assertEquals(5, $entity->get_provider_id());
        }
    }

    public function test_get_all_by_provider_id_null(): void
    {
        // Setup records with null provider
        $this->insert_test_record([
            'feature_id' => 200,
            'action_interface' => 'chat_completion',
            'provider_id' => null,
        ]);
        $this->insert_test_record([
            'feature_id' => 201,
            'action_interface' => 'embedding',
            'provider_id' => null,
        ]);
        // Non-null provider
        $this->insert_test_record([
            'feature_id' => 202,
            'action_interface' => 'chat_completion',
            'provider_id' => 1,
        ]);

        $collection = \local_mxaimanager\app\factory::make()->ai()->feature()->action()->repository(
        )->get_all_by_provider_id(null);

        $this->assertCount(2, $collection);
        $entities = iterator_to_array($collection, false);

        foreach ($entities as $entity) {
            $this->assertNull($entity->get_provider_id());
        }
    }

    public function test_get_by_feature_id_and_action_interface(): void
    {
        // Setup record
        $this->insert_test_record([
            'feature_id' => 42,
            'action_interface' => 'custom_action',
            'provider_id' => 3,
        ]);

        $entity = \local_mxaimanager\app\factory::make()->ai()->feature()->action()->repository(
        )->get_by_feature_id_and_action_interface(42, 'custom_action');

        $this->assertEquals(42, $entity->get_feature_id());
        $this->assertEquals('custom_action', $entity->get_action_interface());
        $this->assertEquals(3, $entity->get_provider_id());
    }

    public function test_get_by_feature_id_and_action_interface_not_found(): void
    {
        $this->expectException(\dml_missing_record_exception::class);
        \local_mxaimanager\app\factory::make()->ai()->feature()->action()->repository(
        )->get_by_feature_id_and_action_interface(999, 'missing_action');
    }

    public function test_get_all(): void
    {
        // Setup multiple records
        $this->insert_test_record(['feature_id' => 1, 'action_interface' => 'action1', 'provider_id' => 1]);
        $this->insert_test_record(['feature_id' => 2, 'action_interface' => 'action2', 'provider_id' => 2]);
        $this->insert_test_record(['feature_id' => 3, 'action_interface' => 'action3', 'provider_id' => null]);

        $collection = \local_mxaimanager\app\factory::make()->ai()->feature()->action()->repository()->get_all();

        $this->assertCount(3, $collection);
        $entities = iterator_to_array($collection, false);

        // Check various properties across all entities
        $feature_ids = array_map(fn($e) => $e->get_feature_id(), $entities);
        $this->assertContains(1, $feature_ids);
        $this->assertContains(2, $feature_ids);
        $this->assertContains(3, $feature_ids);

        $action_interfaces = array_map(fn($e) => $e->get_action_interface(), $entities);
        $this->assertContains('action1', $action_interfaces);
        $this->assertContains('action2', $action_interfaces);
        $this->assertContains('action3', $action_interfaces);
    }

    public function test_get_all_empty_table(): void
    {
        $collection = \local_mxaimanager\app\factory::make()->ai()->feature()->action()->repository()->get_all();

        $this->assertCount(0, $collection);
    }

    /**
     * Helper to insert test records and return ID
     * @param array $data
     * @return int
     */
    private function insert_test_record(array $data): int
    {
        global $DB;
        return $DB->insert_record('local_mxaimanager_feature_actions', (object)$data);
    }
}
