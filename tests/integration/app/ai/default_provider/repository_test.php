<?php

namespace integration\app\ai\default_provider;


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

    public function test_get_table(): void
    {
        $repository = base_factory::make()->ai()->default_provider()->repository();
        $this->assertEquals('local_mxaimanager_action_default_providers', $repository->get_table());
    }

    public function test_get_by_id(): void
    {
        $factory = base_factory::make();
        $repository = $factory->ai()->default_provider()->repository();

        $default_provider = $factory->ai()->default_provider()->entity()
            ->set_action_id($action->get_id())
            ->set_provider_id($provider->get_id());
        $default_provider->set_id($repository->insert($default_provider));

        $retrieved_dp = $repository->get_by_id($default_provider->get_id());

        $this->assertEquals($default_provider->get_id(), $retrieved_dp->get_id());
        $this->assertEquals($action->get_id(), $retrieved_dp->get_action_id());
        $this->assertEquals($provider->get_id(), $retrieved_dp->get_provider_id());
    }

    public function test_get_by_action_id(): void
    {
        $factory = base_factory::make();
        $repository = $factory->ai()->default_provider()->repository();

        $action = $this->create_dummy_        $provider = $this->create_dummy_provider();

        $default_provider = $factory->ai()->default_provider()->entity()
            ->set_action_id($action->get_id())
            ->set_provider_id($provider->get_id());
        $repository->insert($default_provider);

        $retrieved_dp = $repository->get_by_action_id($action->get_id());

        $this->assertEquals($action->get_id(), $retrieved_dp->get_action_id());
        $this->assertEquals($provider->get_id(), $retrieved_dp->get_provider_id());
    }

    public function test_get_all_by_provider_id(): void
    {
        $factory = base_factory::make();
        $repository = $factory->ai()->default_provider()->repository();

        $action1 = $this->create_dummy_        $action2 = $this->create_dummy_        $provider = $this->create_dummy_provider();

        $dp1 = $factory->ai()->default_provider()->entity()
            ->set_action_id($action1->get_id())
            ->set_provider_id($provider->get_id());
        $repository->insert($dp1);

        $dp2 = $factory->ai()->default_provider()->entity()
            ->set_action_id($action2->get_id())
            ->set_provider_id($provider->get_id());
        $repository->insert($dp2);

        $dps = $repository->get_all_by_provider_id($provider->get_id());

        $this->assertCount(2, $dps);

        $actions_array = iterator_to_array($dps);
        $action_ids = array_map(function ($dp) {
            return $dp->get_action_id();
        }, $actions_array);
        sort($action_ids);

        $this->assertEquals([$action1->get_id(), $action2->get_id()], $action_ids);
    }

    public function test_get_by_action_id_and_provider_id(): void
    {
        $factory = base_factory::make();
        $repository = $factory->ai()->default_provider()->repository();

        $action = $this->create_dummy_        $provider = $this->create_dummy_provider();

        $default_provider = $factory->ai()->default_provider()->entity()
            ->set_action_id($action->get_id())
            ->set_provider_id($provider->get_id());
        $repository->insert($default_provider);

        $retrieved_dp = $repository->get_by_action_id_and_provider_id(
            $action->get_id(),
            $provider->get_id()
        );

        $this->assertEquals($action->get_id(), $retrieved_dp->get_action_id());
        $this->assertEquals($provider->get_id(), $retrieved_dp->get_provider_id());
    }

    public function test_get_all(): void
    {
        $factory = base_factory::make();
        $repository = $factory->ai()->default_provider()->repository();

        $action1 = $this->create_dummy_        $action2 = $this->create_dummy_        $provider1 = $this->create_dummy_provider();
        $provider2 = $this->create_dummy_provider();

        $dp1 = $factory->ai()->default_provider()->entity()
            ->set_action_id($action1->get_id())
            ->set_provider_id($provider1->get_id());
        $repository->insert($dp1);

        $dp2 = $factory->ai()->default_provider()->entity()
            ->set_action_id($action2->get_id())
            ->set_provider_id($provider2->get_id());
        $repository->insert($dp2);

        $all_dps = $repository->get_all();

        $this->assertCount(2, $all_dps);
    }
}
