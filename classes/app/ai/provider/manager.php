<?php

namespace local_mxaimanager\app\ai\provider;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();
// @codeCoverageIgnoreEnd

use local_mxaimanager\app\factory as base_factory;

class manager
{
    private base_factory $base_factory;

    public function __construct(base_factory $base_factory)
    {
        $this->base_factory = $base_factory;
    }

    public function install_defaults(): void
    {
        $this->create_openai_provider();
        $this->create_mistral_provider();
    }

    private function create_mistral_provider(): void {
        // Create Mistral provider.
        $time = time();
        $provider = $this->base_factory->ai()->provider()->entity()
            ->set_name('Mistral AI')
            ->set_classname(\local_mxaimanager\app\ai\provider\providers\mistral::class)
            ->set_timecreated($time)
            ->set_timemodified($time);
        $provider->set_id($this->base_factory->ai()->provider()->repository()->insert($provider));

        // Link actions to provider.
        $actions_names = [
            \local_mxaimanager\app\ai\action\manager::ACTION_CHAT_COMPLETION,
            \local_mxaimanager\app\ai\action\manager::ACTION_CREATE_EMBEDDING,
        ];
        foreach ($actions_names as $action_name) {
            $action = $this->base_factory->ai()->action()->repository()->get_by_name($action_name);
            $provider_action = $this->base_factory->ai()->provider()->action()->entity()
                ->set_provider_id($provider->get_id())
                ->set_action_id($action->get_id());
            $this->base_factory->ai()->provider()->action()->repository()->insert($provider_action);
        }
    }

    private function create_openai_provider(): void
    {
        // Create OpenAI provider.
        $time = time();
        $provider = $this->base_factory->ai()->provider()->entity()
            ->set_name('Open AI')
            ->set_classname(\local_mxaimanager\app\ai\provider\providers\openai::class)
            ->set_timecreated($time)
            ->set_timemodified($time);
        $provider->set_id($this->base_factory->ai()->provider()->repository()->insert($provider));

        // Link actions to provider.
        $actions_names = [
            \local_mxaimanager\app\ai\action\manager::ACTION_CHAT_COMPLETION,
            \local_mxaimanager\app\ai\action\manager::ACTION_CREATE_EMBEDDING,
        ];
        foreach ($actions_names as $action_name) {
            $action = $this->base_factory->ai()->action()->repository()->get_by_name($action_name);
            $provider_action = $this->base_factory->ai()->provider()->action()->entity()
                ->set_provider_id($provider->get_id())
                ->set_action_id($action->get_id());
            $this->base_factory->ai()->provider()->action()->repository()->insert($provider_action);
        }
    }
}
