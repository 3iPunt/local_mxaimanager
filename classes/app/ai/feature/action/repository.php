<?php

namespace local_mxaimanager\app\ai\feature\action;


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
        return 'local_mxaimanager_feature_actions';
    }

    public function get_by_id(int $id): entity
    {
        return $this->base_factory->ai()->feature()->action()->entity(
            (array)$this->db->get_record($this->get_table(), ['id' => $id], strictness: MUST_EXIST)
        );
    }

    /**
     * @param int $feature_id
     * @return \local_mxaimanager\app\collection<entity>
     * @throws \dml_exception
     */
    public function get_all_by_feature_id(int $feature_id): \local_mxaimanager\app\collection
    {
        return $this->base_factory->collection(
            array_map(
                function (object $record) {
                    return $this->base_factory->ai()->feature()->action()->entity((array)$record);
                },
                $this->db->get_records($this->get_table(), ['feature_id' => $feature_id])
            )
        );
    }

    /**
     * @param string $action_interface
     * @return \local_mxaimanager\app\collection<entity>
     * @throws \dml_exception
     */
    public function get_all_by_action_interface(string $action_interface): \local_mxaimanager\app\collection
    {
        return $this->base_factory->collection(
            array_map(
                function (object $record) {
                    return $this->base_factory->ai()->feature()->action()->entity((array)$record);
                },
                $this->db->get_records($this->get_table(), ['action_interface' => $action_interface])
            )
        );
    }

    /**
     * @param ?int $provider_id
     * @return \local_mxaimanager\app\collection<entity>
     * @throws \dml_exception
     */
    public function get_all_by_provider_id(?int $provider_id): \local_mxaimanager\app\collection
    {
        return $this->base_factory->collection(
            array_map(
                function (object $record) {
                    return $this->base_factory->ai()->feature()->action()->entity((array)$record);
                },
                $this->db->get_records($this->get_table(), ['provider_id' => $provider_id])
            )
        );
    }

    public function get_by_feature_id_and_action_interface(int $feature_id, string $action_interface): entity
    {
        return $this->base_factory->ai()->feature()->action()->entity(
            (array)$this->db->get_record(
                $this->get_table(),
                ['feature_id' => $feature_id, 'action_interface' => $action_interface],
                strictness: MUST_EXIST
            )
        );
    }

    public function get_all(): \local_mxaimanager\app\collection
    {
        return $this->base_factory->collection(
            array_map(
                function (object $record) {
                    return $this->base_factory->ai()->feature()->action()->entity((array)$record);
                },
                $this->db->get_records($this->get_table())
            )
        );
    }
}
