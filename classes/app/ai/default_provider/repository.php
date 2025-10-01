<?php

namespace local_mxaimanager\app\ai\default_provider;


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
        return 'local_mxaimanager_default_providers';
    }

    public function insert_or_update(entity $entity): entity
    {
        try {
            $entity_to_be_updated = $this->get_by_action_interface($entity->get_action_interface());
            $entity_to_be_updated->set_provider_id($entity->get_provider_id());
            $this->update($entity_to_be_updated);
        } catch (\Exception) {
            $entity->set_id(
                $this->insert($entity)
            );
        }

        return $entity;
    }

    public function get_by_id(int $id): entity
    {
        return $this->base_factory->ai()->default_provider()->entity(
            (array)$this->db->get_record($this->get_table(), ['id' => $id], strictness: MUST_EXIST)
        );
    }

    public function get_by_action_interface(string $action_interface): entity
    {
        return $this->base_factory->ai()->default_provider()->entity(
            (array)$this->db->get_record(
                $this->get_table(),
                ['action_interface' => $action_interface],
                strictness: MUST_EXIST
            )
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
                    return $this->base_factory->ai()->default_provider()->entity((array)$record);
                },
                $this->db->get_records($this->get_table(), ['provider_id' => $provider_id])
            )
        );
    }

    public function get_by_action_interface_and_provider_id(string $action_interface, int $provider_id): entity
    {
        return $this->base_factory->ai()->default_provider()->entity(
            (array)$this->db->get_record(
                $this->get_table(),
                ['action_interface' => $action_interface, 'provider_id' => $provider_id],
                strictness: MUST_EXIST
            )
        );
    }

    public function get_all(): \local_mxaimanager\app\collection
    {
        return $this->base_factory->collection(
            array_map(
                function (object $record) {
                    return $this->base_factory->ai()->default_provider()->entity((array)$record);
                },
                $this->db->get_records($this->get_table())
            )
        );
    }
}
