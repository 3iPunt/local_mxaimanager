<?php

namespace local_mxaimanager\app\ai\action\default_provider;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();
// @codeCoverageIgnoreEnd

class entity extends \local_mxaimanager\app\entity
{
    public function get_action_id(): int
    {
        return $this->record['action_id'] ?? 0;
    }

    public function set_action_id(int $value): self
    {
        $this->record['action_id'] = $value;
        return $this;
    }

    public function get_provider_id(): int
    {
        return $this->record['provider_id'] ?? 0;
    }

    public function set_provider_id(int $value): self
    {
        $this->record['provider_id'] = $value;
        return $this;
    }

    public function to_array(): array
    {
        return [
            'id' => $this->get_id(),
            'action_id' => $this->get_action_id(),
            'provider_id' => $this->get_provider_id(),
        ];
    }
}
