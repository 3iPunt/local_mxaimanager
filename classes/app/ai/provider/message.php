<?php

namespace local_mxaimanager\app\ai\provider;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();
// @codeCoverageIgnoreEnd

class message implements \JsonSerializable
{
    protected string $role;
    protected string $content;

    public function __construct(string $role, string $content)
    {
        $this->role = $role;
        $this->content = $content;
    }

    public function get_role(): string
    {
        return $this->role;
    }

    public function get_content(): string
    {
        return $this->content;
    }

    public function jsonSerialize(): array
    {
        return [
            'role' => $this->get_role(),
            'content' => $this->get_content(),
        ];
    }
}
