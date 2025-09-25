<?php

namespace local_mxaimanager\integration\app\ai\provider;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

use local_mxaimanager\app\factory as base_factory;

class manager_test extends \advanced_testcase
{
    protected function setUp(): void
    {
        $this->resetAfterTest();

        $base_factory = base_factory::make();

        // Remove all providers since db/install.php is called during setUp, which seeds the default providers.
        foreach ($base_factory->ai()->provider()->repository()->get_all() as $provider) {
            $base_factory->ai()->provider()->repository()->delete($provider->get_id());
        }
    }

    public function test_install_defaults(): void
    {
        $factory = base_factory::make();
        $manager = $factory->ai()->provider()->manager();

        // Call install_defaults.
        $manager->install_defaults();

        // Verify providers were created.
        $providers = $factory->ai()->provider()->repository()->get_all();
        $this->assertCount(2, $providers);

        // Get providers by name.
        $openai_provider = $factory->ai()->provider()->repository()->get_by_name('Open AI');
        $mistral_provider = $factory->ai()->provider()->repository()->get_by_name('Mistral AI');

        // Verify OpenAI provider.
        $this->assertEquals('Open AI', $openai_provider->get_name());
        $this->assertEquals(
            \local_mxaimanager\app\ai\provider\providers\openai::class,
            $openai_provider->get_classname()
        );

        // Verify Mistral provider.
        $this->assertEquals('Mistral AI', $mistral_provider->get_name());
        $this->assertEquals(
            \local_mxaimanager\app\ai\provider\providers\mistral::class,
            $mistral_provider->get_classname()
        );

        // Verify provider actions for OpenAI provider.
        $this->verify_provider_actions($factory, $openai_provider->get_id());

        // Verify provider actions for Mistral provider.
        $this->verify_provider_actions($factory, $mistral_provider->get_id());
    }

    private function verify_provider_actions(base_factory $factory, int $provider_id): void
    {
        $provider_actions = $factory->ai()->provider()->action()->repository()->get_all_by_provider_id($provider_id);
        $this->assertCount(2, $provider_actions);

        $action_names = [];
        foreach ($provider_actions as $provider_action) {
            $action = $factory->ai()->action()->repository()->get_by_id($provider_action->get_action_id());
            $action_names[] = $action->get_name();
        }
        sort($action_names);

        $expected_actions = [
            \local_mxaimanager\app\ai\action\manager::ACTION_CHAT_COMPLETION,
            \local_mxaimanager\app\ai\action\manager::ACTION_CREATE_EMBEDDING,
        ];
        sort($expected_actions);

        $this->assertEquals($expected_actions, $action_names);
    }
}
