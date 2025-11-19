<?php

namespace local_mxaimanager\app\vector\test;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();
// @codeCoverageIgnoreEnd

global $CFG;
require_once $CFG->libdir . '/filelib.php';

class vector implements \local_mxaimanager\app\vector\interfaces\db_vector
{
    public function insert(string $collection, array $record): int
    {
        return 1;
    }

    public function upsert(string $collection, array $record): int
    {
        return 1;
    }

    public function insert_bulk(string $collection, array $records): array
    {
        return [1];
    }

    public function get_by_id(string $collection, int $id, array $fields): ?array
    {
        return null;
    }

    public function get_by_field(string $collection, string $field, string $value, array $fields): ?array
    {
        return null;
    }

    public function delete_by_id(string $collection, int $id): bool
    {
        return true;
    }

    public function delete_by_field(string $collection, string $field, string $value): bool
    {
        return true;
    }

    public function search(
        string $collection,
        array $vector,
        array $fields,
        array $equal_conditions,
        array $not_equal_conditions,
        int $limit
    ): array {
        return [];
    }
}
