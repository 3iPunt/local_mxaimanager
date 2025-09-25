<?php

namespace local_mxaimanager\app\ai\action\default_provider;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();
// @codeCoverageIgnoreEnd

/**
 * @extends \local_mxaimanager\app\repository<entity>
 */
class repository extends \local_mxaimanager\app\repository
{
    public function get_table(): string
    {
        return 'local_mxaimanager_action_default_providers';
    }

    public function get_by_id(int $id): entity
    {
        return $this->base_factory->ai()->action()->default_provider()->entity(
            (array)$this->db->get_record($this->get_table(), ['id' => $id], strictness: MUST_EXIST)
        );
    }

    public function get_by_action_id(int $action_id): entity
    {
        return $this->base_factory->ai()->action()->default_provider()->entity(
            (array)$this->db->get_record($this->get_table(), ['action_id' => $action_id], strictness: MUST_EXIST)
        );
    }

    /**
     * @param int $provider_id
     * @return \local_mxaimanager\app\collection<entity>
     * @throws \dml_exception
     */
    public function get_all_by_provider_id(int $provider_id): \local_mxaimanager\app\collection
    {
        return $this->base_factory->collection(
            array_map(
                function (object $record) {
                    return $this->base_factory->ai()->action()->default_provider()->entity((array)$record);
                }, $this->db->get_records($this->get_table(), ['provider_id' => $provider_id])
            )
        );
    }

    public function get_by_action_id_and_provider_id(int $action_id, int $provider_id): entity
    {
        return $this->base_factory->ai()->action()->default_provider()->entity(
            (array)$this->db->get_record($this->get_table(), ['action_id' => $action_id, 'provider_id' => $provider_id], strictness: MUST_EXIST)
        );
    }

    public function get_all(): \local_mxaimanager\app\collection
    {
        return $this->base_factory->collection(
            array_map(
                function (object $record) {
                    return $this->base_factory->ai()->action()->entity((array)$record);
                }, $this->db->get_records($this->get_table())
            )
        );
    }
}
