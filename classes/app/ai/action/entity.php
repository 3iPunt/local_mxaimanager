<?php

namespace local_mxaimanager\app\ai\action;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();
// @codeCoverageIgnoreEnd

class entity extends \local_mxaimanager\app\entity
{
    public function get_name(): string
    {
        return $this->record['name'] ?? '';
    }

    public function set_name(string $value): self
    {
        $this->record['name'] = $value;
        return $this;
    }

    public function to_array(): array
    {
        return [
            'id' => $this->get_id(),
            'name' => $this->get_name(),
        ];
    }
}
