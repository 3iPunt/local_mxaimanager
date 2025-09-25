<?php

namespace local_mxaimanager\app\ai\feature;


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
        return 'local_mxaimanager_features';
    }

    public function get_by_id(int $id): entity
    {
        return $this->base_factory->ai()->feature()->entity(
            (array)$this->db->get_record($this->get_table(), ['id' => $id], strictness: MUST_EXIST)
        );
    }

    /**
     * @param string $name_identifier
     * @return \local_mxaimanager\app\collection<entity>
     * @throws \dml_exception
     */
    public function get_all_by_name_identifier(string $name_identifier): \local_mxaimanager\app\collection
    {
        return $this->base_factory->collection(
            array_map(
                function (object $record) {
                    return $this->base_factory->ai()->feature()->entity((array)$record);
                },
                $this->db->get_records($this->get_table(), ['name_identifier' => $name_identifier])
            )
        );
    }

    /**
     * @param string $component
     * @return \local_mxaimanager\app\collection<entity>
     * @throws \dml_exception
     */
    public function get_all_by_component(string $component): \local_mxaimanager\app\collection
    {
        return $this->base_factory->collection(
            array_map(
                function (object $record) {
                    return $this->base_factory->ai()->feature()->entity((array)$record);
                },
                $this->db->get_records($this->get_table(), ['component' => $component])
            )
        );
    }

    public function get_by_component_and_name_identifier(string $component, string $name_identifier): entity
    {
        return $this->base_factory->ai()->feature()->entity(
            (array)$this->db->get_record(
                $this->get_table(),
                ['component' => $component, 'name_identifier' => $name_identifier],
                strictness: MUST_EXIST
            )
        );
    }

    public function get_all(): \local_mxaimanager\app\collection
    {
        return $this->base_factory->collection(
            array_map(
                function (object $record) {
                    return $this->base_factory->ai()->feature()->entity((array)$record);
                },
                $this->db->get_records($this->get_table())
            )
        );
    }
}
