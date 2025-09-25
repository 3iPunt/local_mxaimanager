<?php

namespace local_mxaimanager\app;

// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();
// @codeCoverageIgnoreEnd

/**
 * @template T of entity
 */
abstract class repository {
    protected \local_mxaimanager\app\factory $base_factory;
    protected \moodle_database $db;

    abstract public function get_table(): string;

    /**
     * @param int $id
     * @return T
     */
    abstract public function get_by_id(int $id): entity;

    /**
     * @return collection<T>
     */
    abstract public function get_all(): collection;

    public function __construct(\local_mxaimanager\app\factory $base_factory)
    {
        $this->base_factory = $base_factory;
        $this->db = $this->base_factory->db();
    }

    /**
     * @param T $entity
     * @return int
     * @throws \dml_exception
     */
    public function insert(entity $entity): int
    {
        return $this->db->insert_record($this->get_table(), (object)$entity->to_array());
    }

    /**
     * @param T $entity
     * @return bool
     * @throws \dml_exception
     */
    public function update(entity $entity): bool
    {
        return $this->db->update_record($this->get_table(), (object)$entity->to_array());
    }

    /**
     * @param int $id
     * @return bool
     * @throws \dml_exception
     */
    public function delete(int $id): bool
    {
        return $this->db->delete_records($this->get_table(), ['id' => $id]);
    }
}
