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

        $base_factory = base_factory::make();

        // Remove all feature actions since db/install.php is called during setUp, which may seed feature actions.
        foreach ($base_factory->ai()->feature()->action()->repository()->get_all() as $feature_action) {
            $base_factory->ai()->feature()->action()->repository()->delete($feature_action->get_id());
        }
    }

    private function create_dummy_feature(): \local_mxaimanager\app\ai\feature\entity
    {
        static $counter = 0;
        $factory = base_factory::make();
        $feature_repo = $factory->ai()->feature()->repository();

        $feature = $factory->ai()->feature()->entity()
            ->set_component('mod_test')
            ->set_name_identifier('feature_' . ++$counter)
            ->set_description_identifier('Feature Desc ' . $counter);
        $feature->set_id($feature_repo->insert($feature));

        return $feature;
    }

    private function create_dummy_action(): \local_mxaimanager\app\ai\action\entity
    {
        static $counter = 0;
        $factory = base_factory::make();
        $action_repo = $factory->ai()->action()->repository();

        $action = $factory->ai()->action()->entity()
            ->set_name('action_' . ++$counter);
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
            ->set_name('Provider ' . ++$counter)
            ->set_classname(\local_mxaimanager\app\ai\provider\providers\openai::class)
            ->set_timecreated($time)
            ->set_timemodified($time);
        $provider->set_id($provider_repo->insert($provider));

        return $provider;
    }

    public function test_get_table(): void
    {
        $repository = base_factory::make()->ai()->feature()->action()->repository();
        $this->assertEquals('local_mxaimanager_feature_actions', $repository->get_table());
    }

    public function test_get_by_id(): void
    {
        $factory = base_factory::make();
        $repository = $factory->ai()->feature()->action()->repository();

        $feature = $this->create_dummy_feature();
        $action = $this->create_dummy_action();
        $provider = $this->create_dummy_provider();

        $feature_action = $factory->ai()->feature()->action()->entity()
            ->set_feature_id($feature->get_id())
            ->set_action_id($action->get_id())
            ->set_provider_id($provider->get_id())
            ->set_settings_json(null);
        $feature_action->set_id($repository->insert($feature_action));

        $retrieved_fa = $repository->get_by_id($feature_action->get_id());

        $this->assertEquals($feature->get_id(), $retrieved_fa->get_feature_id());
        $this->assertEquals($action->get_id(), $retrieved_fa->get_action_id());
        $this->assertEquals($provider->get_id(), $retrieved_fa->get_provider_id());
    }

    public function test_get_all_by_feature_id(): void
    {
        $factory = base_factory::make();
        $repository = $factory->ai()->feature()->action()->repository();

        $feature = $this->create_dummy_feature();
        $action1 = $this->create_dummy_action();
        $action2 = $this->create_dummy_action();
        $provider = $this->create_dummy_provider();

        $fa1 = $factory->ai()->feature()->action()->entity()
            ->set_feature_id($feature->get_id())
            ->set_action_id($action1->get_id())
            ->set_provider_id($provider->get_id());
        $repository->insert($fa1);

        $fa2 = $factory->ai()->feature()->action()->entity()
            ->set_feature_id($feature->get_id())
            ->set_action_id($action2->get_id())
            ->set_provider_id($provider->get_id());
        $repository->insert($fa2);

        $feature_actions = $repository->get_all_by_feature_id($feature->get_id());

        $this->assertCount(2, $feature_actions);

        $actions_array = iterator_to_array($feature_actions);
        $action_ids = array_map(function ($fa) {
            return $fa->get_action_id();
        }, $actions_array);
        sort($action_ids);

        $this->assertEquals([$action1->get_id(), $action2->get_id()], $action_ids);
    }

    public function test_get_all_by_action_id(): void
    {
        $factory = base_factory::make();
        $repository = $factory->ai()->feature()->action()->repository();

        $feature1 = $this->create_dummy_feature();
        $feature2 = $this->create_dummy_feature();
        $action = $this->create_dummy_action();
        $provider = $this->create_dummy_provider();

        $fa1 = $factory->ai()->feature()->action()->entity()
            ->set_feature_id($feature1->get_id())
            ->set_action_id($action->get_id())
            ->set_provider_id($provider->get_id());
        $repository->insert($fa1);

        $fa2 = $factory->ai()->feature()->action()->entity()
            ->set_feature_id($feature2->get_id())
            ->set_action_id($action->get_id())
            ->set_provider_id($provider->get_id());
        $repository->insert($fa2);

        $feature_actions = $repository->get_all_by_action_id($action->get_id());

        $this->assertCount(2, $feature_actions);

        $actions_array = iterator_to_array($feature_actions);
        $feature_ids = array_map(function ($fa) {
            return $fa->get_feature_id();
        }, $actions_array);
        sort($feature_ids);

        $this->assertEquals([$feature1->get_id(), $feature2->get_id()], $feature_ids);
    }

    public function test_get_all_by_provider_id(): void
    {
        $factory = base_factory::make();
        $repository = $factory->ai()->feature()->action()->repository();

        $feature = $this->create_dummy_feature();
        $action1 = $this->create_dummy_action();
        $action2 = $this->create_dummy_action();
        $provider = $this->create_dummy_provider();

        $fa1 = $factory->ai()->feature()->action()->entity()
            ->set_feature_id($feature->get_id())
            ->set_action_id($action1->get_id())
            ->set_provider_id($provider->get_id());
        $repository->insert($fa1);

        $fa2 = $factory->ai()->feature()->action()->entity()
            ->set_feature_id($feature->get_id())
            ->set_action_id($action2->get_id())
            ->set_provider_id($provider->get_id());
        $repository->insert($fa2);

        $feature_actions = $repository->get_all_by_provider_id($provider->get_id());

        $this->assertCount(2, $feature_actions);
    }

    public function test_get_all_by_provider_id_null(): void
    {
        $factory = base_factory::make();
        $repository = $factory->ai()->feature()->action()->repository();

        $feature = $this->create_dummy_feature();
        $action = $this->create_dummy_action();

        $fa = $factory->ai()->feature()->action()->entity()
            ->set_feature_id($feature->get_id())
            ->set_action_id($action->get_id())
            ->set_provider_id(null);
        $repository->insert($fa);

        $feature_actions = $repository->get_all_by_provider_id(null);

        $this->assertCount(1, $feature_actions);
        $this->assertNull($feature_actions->first()->get_provider_id());
    }

    public function test_get_by_feature_id_and_action_id(): void
    {
        $factory = base_factory::make();
        $repository = $factory->ai()->feature()->action()->repository();

        $feature = $this->create_dummy_feature();
        $action = $this->create_dummy_action();
        $provider = $this->create_dummy_provider();

        $feature_action = $factory->ai()->feature()->action()->entity()
            ->set_feature_id($feature->get_id())
            ->set_action_id($action->get_id())
            ->set_provider_id($provider->get_id());
        $repository->insert($feature_action);

        $retrieved_fa = $repository->get_by_feature_id_and_action_id($feature->get_id(), $action->get_id());

        $this->assertEquals($feature->get_id(), $retrieved_fa->get_feature_id());
        $this->assertEquals($action->get_id(), $retrieved_fa->get_action_id());
        $this->assertEquals($provider->get_id(), $retrieved_fa->get_provider_id());
    }

    public function test_get_all(): void
    {
        $factory = base_factory::make();
        $repository = $factory->ai()->feature()->action()->repository();

        $feature1 = $this->create_dummy_feature();
        $feature2 = $this->create_dummy_feature();
        $action = $this->create_dummy_action();
        $provider = $this->create_dummy_provider();

        $fa1 = $factory->ai()->feature()->action()->entity()
            ->set_feature_id($feature1->get_id())
            ->set_action_id($action->get_id())
            ->set_provider_id($provider->get_id());
        $repository->insert($fa1);

        $fa2 = $factory->ai()->feature()->action()->entity()
            ->set_feature_id($feature2->get_id())
            ->set_action_id($action->get_id())
            ->set_provider_id($provider->get_id());
        $repository->insert($fa2);

        $all_feature_actions = $repository->get_all();

        $this->assertCount(2, $all_feature_actions);
    }
}
