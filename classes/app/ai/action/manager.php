<?php

namespace local_mxaimanager\app\ai\action;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

use local_mxaimanager\app\factory as base_factory;

class manager
{
    public const ACTION_CHAT_COMPLETION = 'chat_completion';
    public const ACTION_CREATE_EMBEDDING = 'create_embedding';

    private base_factory $base_factory;

    public function __construct(base_factory $base_factory)
    {
        $this->base_factory = $base_factory;
    }

    public function install_defaults(): void
    {
        $repository = $this->base_factory->ai()->action()->repository();

        $default_actions = [
            $this->base_factory->ai()->action()->entity()->set_name(self::ACTION_CHAT_COMPLETION),
            $this->base_factory->ai()->action()->entity()->set_name(self::ACTION_CREATE_EMBEDDING),
        ];

        foreach ($default_actions as $action) {
            try {
                $repository->get_by_name($action->get_name());
            } catch (\dml_missing_record_exception) {
                $repository->insert($action);
            }
        }
    }
}
