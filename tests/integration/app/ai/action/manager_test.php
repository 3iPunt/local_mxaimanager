<?php

namespace local_mxaimanager\integration\app\ai\action;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

use local_mxaimanager\app\factory as base_factory;

class manager_test extends \advanced_testcase
{
    protected function setUp(): void
    {
        $this->resetAfterTest();
    }

    public function test_install_defaults_already_existing_actions(): void
    {
        $base_factory = base_factory::make();

        $manager = $base_factory->ai()->action()->manager();

        // Call install_defaults.
        $manager->install_defaults();

        // Verify actions were created.
        $actions = $base_factory->ai()->action()->repository()->get_all();
        $this->assertCount(2, $actions);

        // Get actions by name.
        $chat_completion_action = $base_factory->ai()->action()->repository()->get_by_name(
            \local_mxaimanager\app\ai\action\manager::ACTION_CHAT_COMPLETION
        );
        $embeddings_action = $base_factory->ai()->action()->repository()->get_by_name(
            \local_mxaimanager\app\ai\action\manager::ACTION_CREATE_EMBEDDING
        );

        // Verify chat completion action.
        $this->assertEquals(
            \local_mxaimanager\app\ai\action\manager::ACTION_CHAT_COMPLETION,
            $chat_completion_action->get_name()
        );

        // Verify embeddings action.
        $this->assertEquals(
            \local_mxaimanager\app\ai\action\manager::ACTION_CREATE_EMBEDDING,
            $embeddings_action->get_name()
        );
    }

    public function test_install_defaults_no_existing_actions(): void
    {
        $base_factory = base_factory::make();

        // Remove all actions since db/install.php is called during setUp, which seeds the default actions.
        foreach ($base_factory->ai()->action()->repository()->get_all() as $action) {
            $base_factory->ai()->action()->repository()->delete($action->get_id());
        }

        $manager = $base_factory->ai()->action()->manager();

        // Call install_defaults.
        $manager->install_defaults();

        // Verify actions were created.
        $actions = $base_factory->ai()->action()->repository()->get_all();
        $this->assertCount(2, $actions);

        // Get actions by name.
        $chat_completion_action = $base_factory->ai()->action()->repository()->get_by_name(
            \local_mxaimanager\app\ai\action\manager::ACTION_CHAT_COMPLETION
        );
        $embeddings_action = $base_factory->ai()->action()->repository()->get_by_name(
            \local_mxaimanager\app\ai\action\manager::ACTION_CREATE_EMBEDDING
        );

        // Verify chat completion action.
        $this->assertEquals(
            \local_mxaimanager\app\ai\action\manager::ACTION_CHAT_COMPLETION,
            $chat_completion_action->get_name()
        );

        // Verify embeddings action.
        $this->assertEquals(
            \local_mxaimanager\app\ai\action\manager::ACTION_CREATE_EMBEDDING,
            $embeddings_action->get_name()
        );
    }
}
