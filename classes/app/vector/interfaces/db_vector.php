<?php

namespace local_mxaimanager\app\vector\interfaces;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();
// @codeCoverageIgnoreEnd

interface db_vector
{
    /**
     * @param string $collection
     * @param array $record Has to contain at least the 'id' field
     * @return int
     */
    public function insert(string $collection, array $record): int;

    /**
     * @param string $collection
     * @param array $record Has to contain at least the 'id' field
     * @return int
     */
    public function upsert(string $collection, array $record): int;

    /**
     * @param string $collection
     * @param array $records Each record has to contain at least the 'id' field
     * @return int[]
     */
    public function insert_bulk(string $collection, array $records): array;
    public function get_by_id(string $collection, int $id, array $fields): ?array;
    public function get_by_field(string $collection, string $field, string $value, array $fields): ?array;
    public function delete_by_id(string $collection, int $id): bool;
    public function delete_by_field(string $collection, string $field, string $value): bool;

    /**
     * @param string $collection
     * @param array $vector
     * @param string[] $fields
     * @param array $equal_conditions $field => $value and/or $field => [ $value1, $value2, ... ]
     * @param array $not_equal_conditions $field => $value and/or $field => [ $value1, $value2, ... ]
     * @param int $limit
     * @return array[]
     */
    public function search(string $collection, array $vector, array $fields, array $equal_conditions, array $not_equal_conditions, int $limit): array;
}
