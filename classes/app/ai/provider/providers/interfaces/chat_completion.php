<?php

namespace local_mxaimanager\app\ai\provider\providers\interfaces;

// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();
// @codeCoverageIgnoreEnd

use local_mxaimanager\app\ai\provider\message;

interface chat_completion
{
    /**
     * @param message[] $messages
     * @return string
     */
    public function chat_completion(array $messages, string $model): string;
}
