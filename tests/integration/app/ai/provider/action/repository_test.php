<?php

namespace local_mxaimanager\integration\app\ai\provider\action;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

use local_mxaimanager\app\factory as base_factory;

class repository_test extends \advanced_testcase
{
    protected function setUp(): void
    {
        $this->resetAfterTest();

        $base_factory = base_factory::make();

        // Remove all provider actions since db/install.php is called during setUp, which seeds the default providers and their actions.
        foreach ($base_factory->ai()->provider()->action()->repository()->get_all() as $provider_action) {
            $base_factory->ai()->provider()->action()->repository()->delete($provider_action->get_id());
        }
    }

    private function create_dummy_action(): \local_mxaimanager\app\ai\action\entity
    {
        static $counter = 0;
        $factory = base_factory::make();
        $action_repo = $factory->ai()->action()->repository();

        $action = $factory->ai()->action()->entity()
            ->set_name('test_action_' . ++$counter);
        $action->set_id($action_repo->insert($action));

        return $action;
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
        $repository = base_factory::make()->ai()->provider()->action()->repository();
        $this->assertEquals('local_mxaimanager_provider_actions', $repository->get_table());
    }

    public function test_get_by_id(): void
    {
        $factory = base_factory::make();
        $repository = $factory->ai()->provider()->action()->repository();

        $action = $this->create_dummy_action();
        $provider = $this->create_dummy_provider();

        $provider_action = $factory->ai()->provider()->action()->entity()
            ->set_provider_id($provider->get_id())
            ->set_action_id($action->get_id())
            ->set_settings_json(null);
        $provider_action->set_id($repository->insert($provider_action));

        $retrieved_provider_action = $repository->get_by_id($provider_action->get_id());

        $this->assertEquals($provider_action->get_id(), $retrieved_provider_action->get_id());
        $this->assertEquals($provider->get_id(), $retrieved_provider_action->get_provider_id());
        $this->assertEquals($action->get_id(), $retrieved_provider_action->get_action_id());
        $this->assertEquals(null, $retrieved_provider_action->get_settings_json());
    }

    public function test_get_all_by_provider_id(): void
    {
        $factory = base_factory::make();
        $repository = $factory->ai()->provider()->action()->repository();

        $action1 = $this->create_dummy_action();
        $action2 = $this->create_dummy_action();
        $provider = $this->create_dummy_provider();

        $provider_action1 = $factory->ai()->provider()->action()->entity()
            ->set_provider_id($provider->get_id())
            ->set_action_id($action1->get_id());
        $repository->insert($provider_action1);

        $provider_action2 = $factory->ai()->provider()->action()->entity()
            ->set_provider_id($provider->get_id())
            ->set_action_id($action2->get_id());
        $repository->insert($provider_action2);

        $provider_actions = $repository->get_all_by_provider_id($provider->get_id());

        $this->assertCount(2, $provider_actions);

        $actions_array = iterator_to_array($provider_actions);
        $action_ids = array_map(function ($pa) {
            return $pa->get_action_id();
        }, $actions_array);
        sort($action_ids);

        $this->assertEquals([$action1->get_id(), $action2->get_id()], $action_ids);
    }

    public function test_get_all_by_action_id(): void
    {
        $factory = base_factory::make();
        $repository = $factory->ai()->provider()->action()->repository();

        $action = $this->create_dummy_action();
        $provider1 = $this->create_dummy_provider();
        $provider2 = $this->create_dummy_provider();

        $provider_action1 = $factory->ai()->provider()->action()->entity()
            ->set_provider_id($provider1->get_id())
            ->set_action_id($action->get_id());
        $repository->insert($provider_action1);

        $provider_action2 = $factory->ai()->provider()->action()->entity()
            ->set_provider_id($provider2->get_id())
            ->set_action_id($action->get_id());
        $repository->insert($provider_action2);

        $provider_actions = $repository->get_all_by_action_id($action->get_id());

        $this->assertCount(2, $provider_actions);

        $actions_array = iterator_to_array($provider_actions);
        $provider_ids = array_map(function ($pa) {
            return $pa->get_provider_id();
        }, $actions_array);
        sort($provider_ids);

        $this->assertEquals([$provider1->get_id(), $provider2->get_id()], $provider_ids);
    }

    public function test_get_by_action_id_and_provider_id(): void
    {
        $factory = base_factory::make();
        $repository = $factory->ai()->provider()->action()->repository();

        $action = $this->create_dummy_action();
        $provider = $this->create_dummy_provider();

        $provider_action = $factory->ai()->provider()->action()->entity()
            ->set_provider_id($provider->get_id())
            ->set_action_id($action->get_id());
        $repository->insert($provider_action);

        $retrieved_provider_action = $repository->get_by_action_id_and_provider_id(
            $action->get_id(),
            $provider->get_id()
        );

        $this->assertEquals($provider->get_id(), $retrieved_provider_action->get_provider_id());
        $this->assertEquals($action->get_id(), $retrieved_provider_action->get_action_id());
    }

    public function test_get_all(): void
    {
        $factory = base_factory::make();
        $repository = $factory->ai()->provider()->action()->repository();

        $action1 = $this->create_dummy_action();
        $action2 = $this->create_dummy_action();
        $provider1 = $this->create_dummy_provider();
        $provider2 = $this->create_dummy_provider();

        $provider_action1 = $factory->ai()->provider()->action()->entity()
            ->set_provider_id($provider1->get_id())
            ->set_action_id($action1->get_id());
        $repository->insert($provider_action1);

        $provider_action2 = $factory->ai()->provider()->action()->entity()
            ->set_provider_id($provider2->get_id())
            ->set_action_id($action2->get_id());
        $repository->insert($provider_action2);

        $all_provider_actions = $repository->get_all();

        $this->assertCount(2, $all_provider_actions);
    }
}
