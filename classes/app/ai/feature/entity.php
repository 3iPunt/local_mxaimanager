<?php

namespace local_mxaimanager\app\ai\feature;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

class entity extends \local_mxaimanager\app\entity
{
    public function get_component(): string
    {
        return $this->record['component'] ?? '';
    }

    public function set_component(string $value): self
    {
        $this->record['component'] = $value;
        return $this;
    }

    public function get_name_identifier(): string
    {
        return $this->record['name_identifier'] ?? '';
    }

    public function set_name_identifier(string $value): self
    {
        $this->record['name_identifier'] = $value;
        return $this;
    }

    public function get_description_identifier(): string
    {
        return $this->record['description_identifier'] ?? '';
    }

    public function set_description_identifier(string $value): self
    {
        $this->record['description_identifier'] = $value;
        return $this;
    }

    public function to_array(): array
    {
        return [
            'id' => $this->get_id(),
            'component' => $this->get_component(),
            'name_identifier' => $this->get_name_identifier(),
            'description_identifier' => $this->get_description_identifier(),
        ];
    }
}
