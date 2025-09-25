<?php

namespace local_mxaimanager\app\ai\feature\action;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();
// @codeCoverageIgnoreEnd

class entity extends \local_mxaimanager\app\entity
{
    public function get_feature_id(): int
    {
        return $this->record['feature_id'] ?? 0;
    }

    public function set_feature_id(int $value): self
    {
        $this->record['feature_id'] = $value;
        return $this;
    }

    public function get_action_id(): int
    {
        return $this->record['action_id'] ?? 0;
    }

    public function set_action_id(int $value): self
    {
        $this->record['action_id'] = $value;
        return $this;
    }

    public function get_provider_id(): ?int
    {
        return $this->record['provider_id'] ?? null;
    }

    public function set_provider_id(?int $value): self
    {
        $this->record['provider_id'] = $value;
        return $this;
    }

    public function get_settings_json(): ?string
    {
        return $this->record['settings_json'] ?? null;
    }

    public function set_settings_json(?string $value): self
    {
        $this->record['settings_json'] = $value;
        return $this;
    }

    public function to_array(): array
    {
        return [
            'id' => $this->get_id(),
            'feature_id' => $this->get_feature_id(),
            'action_id' => $this->get_action_id(),
            'provider_id' => $this->get_provider_id(),
            'settings_json' => $this->get_settings_json(),
        ];
    }
}
