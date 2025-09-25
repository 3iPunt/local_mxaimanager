<?php

namespace local_mxaimanager\integration\app\ai\action;


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

        // Remove all actions since db/install.php is called during setUp, which seeds the default actions.
        foreach ($base_factory->ai()->action()->repository()->get_all() as $action) {
            $base_factory->ai()->action()->repository()->delete($action->get_id());
        }
    }

    public function test_get_table(): void
    {
        $repository = base_factory::make()->ai()->action()->repository();
        $this->assertEquals('local_mxaimanager_actions', $repository->get_table());
    }

    public function test_get_by_id(): void
    {
        $factory = base_factory::make();
        $repository = $factory->ai()->action()->repository();

        $action = $factory->ai()->action()->entity()
            ->set_name('test_action');
        $action->set_id($repository->insert($action));

        $retrieved_action = $repository->get_by_id($action->get_id());

        $this->assertEquals($action->get_id(), $retrieved_action->get_id());
        $this->assertEquals('test_action', $retrieved_action->get_name());
    }

    public function test_get_by_name(): void
    {
        $factory = base_factory::make();
        $repository = $factory->ai()->action()->repository();

        $action = $factory->ai()->action()->entity()
            ->set_name('unique_test_action');
        $action->set_id($repository->insert($action));

        $retrieved_action = $repository->get_by_name('unique_test_action');

        $this->assertEquals($action->get_id(), $retrieved_action->get_id());
        $this->assertEquals('unique_test_action', $retrieved_action->get_name());
    }

    public function test_get_all(): void
    {
        $factory = base_factory::make();
        $repository = $factory->ai()->action()->repository();

        $action1 = $factory->ai()->action()->entity()
            ->set_name('action1');
        $action1->set_id($repository->insert($action1));

        $action2 = $factory->ai()->action()->entity()
            ->set_name('action2');
        $action2->set_id($repository->insert($action2));

        $all_actions = $repository->get_all();

        $this->assertCount(2, $all_actions);

        $actions_array = iterator_to_array($all_actions);
        $names = array_map(function ($action) {
            return $action->get_name();
        }, $actions_array);
        sort($names);

        $this->assertEquals(['action1', 'action2'], $names);
    }
}
