<?php

namespace local_mxaimanager\app\vector\interfaces;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

interface db_collection
{
    public function create(string $name, int $dimension, bool $auto_increment_id): bool;
    public function delete(string $name): bool;
    public function exists(string $name): bool;

    /**
     * @return string[]
     */
    public function list(): array;
}
