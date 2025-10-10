<?php

namespace local_mxaimanager\integration\app\ai\default_provider;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

use local_mxaimanager\app\factory as base_factory;

class repository_test extends \advanced_testcase
{
    protected function setUp(): void
    {
        $this->resetAfterTest();
    }

    private function create_dummy_provider(): \local_mxaimanager\app\ai\provider\entity
    {
        static $counter = 0;
        $factory = base_factory::make();
        $provider_repo = $factory->ai()->provider()->repository();

        $time = time();
        $provider = $factory->ai()->provider()->entity()
            ->set_name('Test Provider ' . ++$counter)
            ->set_classname(\local_mxaimanager\app\ai\provider\providers\openai::class)
            ->set_timecreated($time)
            ->set_timemodified($time);
        $provider->set_id($provider_repo->insert($provider));

        return $provider;
    }

    private function create_dummy_action(): string
    {
        static $counter = 0;
        return 'action_' . ++$counter;
    }

    public function test_get_table(): void
    {
        $repository = base_factory::make()->ai()->default_provider()->repository();
        $this->assertEquals('local_mxaimanager_default_providers', $repository->get_table());
    }

    public function test_get_by_id(): void
    {
        $factory = base_factory::make();
        $repository = $factory->ai()->default_provider()->repository();

        $action = $this->create_dummy_action();
        $provider = $this->create_dummy_provider();

        $default_provider = $factory->ai()->default_provider()->entity()
            ->set_action_interface($action)
            ->set_provider_id($provider->get_id());
        $default_provider->set_id($repository->insert($default_provider));

        $retrieved_dp = $repository->get_by_id($default_provider->get_id());

        $this->assertEquals($default_provider->get_id(), $retrieved_dp->get_id());
        $this->assertEquals($action, $retrieved_dp->get_action_interface());
        $this->assertEquals($provider->get_id(), $retrieved_dp->get_provider_id());
    }

    public function test_get_by_action_interface(): void
    {
        $factory = base_factory::make();
        $repository = $factory->ai()->default_provider()->repository();

        $action = $this->create_dummy_action();
        $provider = $this->create_dummy_provider();

        $default_provider = $factory->ai()->default_provider()->entity()
            ->set_action_interface($action)
            ->set_provider_id($provider->get_id());
        $repository->insert($default_provider);

        $retrieved_dp = $repository->get_by_action_interface($action);

        $this->assertEquals($action, $retrieved_dp->get_action_interface());
        $this->assertEquals($provider->get_id(), $retrieved_dp->get_provider_id());
    }

    public function test_get_all_by_provider_id(): void
    {
        $factory = base_factory::make();
        $repository = $factory->ai()->default_provider()->repository();

        $action1 = $this->create_dummy_action();
        $action2 = $this->create_dummy_action();
        $provider = $this->create_dummy_provider();

        $dp1 = $factory->ai()->default_provider()->entity()
            ->set_action_interface($action1)
            ->set_provider_id($provider->get_id());
        $repository->insert($dp1);

        $dp2 = $factory->ai()->default_provider()->entity()
            ->set_action_interface($action2)
            ->set_provider_id($provider->get_id());
        $repository->insert($dp2);

        $dps = $repository->get_all_by_provider_id($provider->get_id());

        $this->assertCount(2, $dps);

        $actions_array = iterator_to_array($dps);
        $action_names = array_map(function ($dp) {
            return $dp->get_action_interface();
        }, $actions_array);
        sort($action_names);

        $this->assertEquals([$action1, $action2], $action_names);
    }

    public function test_get_by_action_interface_and_provider_id(): void
    {
        $factory = base_factory::make();
        $repository = $factory->ai()->default_provider()->repository();

        $action = $this->create_dummy_action();
        $provider = $this->create_dummy_provider();

        $default_provider = $factory->ai()->default_provider()->entity()
            ->set_action_interface($action)
            ->set_provider_id($provider->get_id());
        $repository->insert($default_provider);

        $retrieved_dp = $repository->get_by_action_interface_and_provider_id(
            $action,
            $provider->get_id()
        );

        $this->assertEquals($action, $retrieved_dp->get_action_interface());
        $this->assertEquals($provider->get_id(), $retrieved_dp->get_provider_id());
    }

    public function test_get_all(): void
    {
        $factory = base_factory::make();
        $repository = $factory->ai()->default_provider()->repository();

        $action1 = $this->create_dummy_action();
        $action2 = $this->create_dummy_action();
        $provider1 = $this->create_dummy_provider();
        $provider2 = $this->create_dummy_provider();

        $dp1 = $factory->ai()->default_provider()->entity()
            ->set_action_interface($action1)
            ->set_provider_id($provider1->get_id());
        $repository->insert($dp1);

        $dp2 = $factory->ai()->default_provider()->entity()
            ->set_action_interface($action2)
            ->set_provider_id($provider2->get_id());
        $repository->insert($dp2);

        $all_dps = $repository->get_all();

        $this->assertCount(2, $all_dps);
    }

    public function test_insert_or_update_insert(): void
    {
        $factory = base_factory::make();
        $repository = $factory->ai()->default_provider()->repository();

        $action = $this->create_dummy_action();
        $provider = $this->create_dummy_provider();

        // Create entity for new action interface
        $default_provider = $factory->ai()->default_provider()->entity()
            ->set_action_interface($action)
            ->set_provider_id($provider->get_id());

        // Before insert_or_update, this action should not exist
        $this->expectException(\dml_missing_record_exception::class);
        $repository->get_by_action_interface($action);

        // Call insert_or_update - should insert new record
        $returned_entity = $repository->insert_or_update($default_provider);

        // Should return the same entity with ID set
        $this->assertSame($default_provider, $returned_entity);
        $this->assertNotNull($returned_entity->get_id());

        // Now we should be able to retrieve it
        $retrieved = $repository->get_by_action_interface($action);
        $this->assertEquals($action, $retrieved->get_action_interface());
        $this->assertEquals($provider->get_id(), $retrieved->get_provider_id());
    }

    public function test_insert_or_update_update(): void
    {
        $factory = base_factory::make();
        $repository = $factory->ai()->default_provider()->repository();

        $action = $this->create_dummy_action();
        $provider1 = $this->create_dummy_provider();
        $provider2 = $this->create_dummy_provider();

        // Insert initial record
        $initial_dp = $factory->ai()->default_provider()->entity()
            ->set_action_interface($action)
            ->set_provider_id($provider1->get_id());
        $repository->insert_or_update($initial_dp);

        // Verify initial state
        $retrieved = $repository->get_by_action_interface($action);
        $this->assertEquals($action, $retrieved->get_action_interface());
        $this->assertEquals($provider1->get_id(), $retrieved->get_provider_id());
        $initial_id = $retrieved->get_id();

        // Create entity with same action_interface but different provider_id
        $updated_dp = $factory->ai()->default_provider()->entity()
            ->set_action_interface($action)
            ->set_provider_id($provider2->get_id());

        // Call insert_or_update - should update existing record
        $returned_entity = $repository->insert_or_update($updated_dp);

        // Should return entity with original ID and properties
        $this->assertEquals($initial_id, $returned_entity->get_id());
        $this->assertEquals($action, $returned_entity->get_action_interface());
        $this->assertEquals($provider2->get_id(), $returned_entity->get_provider_id());

        // Verify the record was updated, not inserted
        $all_records = $repository->get_all();
        $this->assertCount(1, $all_records); // Still only one record

        // Verify provider_id was updated
        $updated_retrieved = $repository->get_by_action_interface($action);
        $this->assertEquals($action, $updated_retrieved->get_action_interface());
        $this->assertEquals($provider2->get_id(), $updated_retrieved->get_provider_id());
        $this->assertEquals($initial_id, $updated_retrieved->get_id());
    }
}
