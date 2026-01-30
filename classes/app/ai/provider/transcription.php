<?php

namespace local_mxaimanager\app\ai\provider;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

class transcription implements \JsonSerializable
{
    protected string $text;
    protected array $segments;

    public function __construct(string $text, array $segments)
    {
        $this->text = $text;
        $this->segments = $segments;
    }

    public function get_text(): string
    {
        return $this->text;
    }

    public function get_segments(): array
    {
        return $this->segments;
    }

    public function jsonSerialize(): array
    {
        return [
            'text' => $this->get_text(),
            'segments' => $this->get_segments(),
        ];
    }
}
