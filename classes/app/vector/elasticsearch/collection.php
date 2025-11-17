<?php

namespace local_mxaimanager\app\vector\elasticsearch;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();
// @codeCoverageIgnoreEnd

global $CFG;
require_once $CFG->libdir . '/filelib.php';

use local_mxaimanager\app\vector;
use local_mxaimanager\app\factory as base_factory;

class collection implements vector\interfaces\db_collection
{
    private base_factory $base_factory;
    private vector\db_config $db_config;
    private \curl $curl;

    public function __construct(base_factory $base_factory, vector\db_config $db_config)
    {
        $this->base_factory = $base_factory;
        $this->db_config = $db_config;
        $this->curl = $this->base_factory->curl();
        $this->curl->setHeader([
            'Content-Type: application/json',
            "Authorization: Bearer {$this->db_config->get_username()}:{$this->db_config->get_password()}"
        ]);
    }

    private function get_base_url(): string
    {
        return "http://{$this->db_config->get_host()}:{$this->db_config->get_port()}/v2/vectordb/collections";
    }

    public function create(string $name, int $dimension, bool $auto_increment_id): bool
    {
        $response = $this->curl->post("{$this->get_base_url()}/create", json_encode([
            'dbName' => $this->db_config->get_dbname(),
            'collectionName' => $name,
            'schema' => [
                'autoID' => $auto_increment_id,
                'enableDynamicField' => true,
                'fields' => [
                    [
                        'fieldName' => 'id',
                        'dataType' => 'Int64',
                        'isPrimary' => true,
                    ],
                    [
                        'fieldName' => 'vector',
                        'dataType' => 'FloatVector',
                        'elementTypeParams' => [
                            'dim' => $dimension,
                        ]
                    ]
                ],
            ],
            'indexParams' => [
                [
                    'fieldName' => 'vector',
                    'metricType' => 'COSINE',
                    'indexName' => 'vector',
                    'indexType' => 'AUTOINDEX',
                ],
                [
                    'fieldName' => 'id',
                    'indexName' => 'id',
                    'indexType' => 'STL_SORT',
                ],
            ]
        ], JSON_THROW_ON_ERROR));

        $json = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        return ((int)$json['code']) === 0;
    }

    public function delete(string $name): bool
    {
        $response = $this->curl->post("{$this->get_base_url()}/drop", json_encode([
            'dbName' => $this->db_config->get_dbname(),
            'collectionName' => $name,
        ], JSON_THROW_ON_ERROR));

        $json = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        return ((int)$json['code']) === 0;
    }

    public function exists(string $name): bool
    {
        $response = $this->curl->post("{$this->get_base_url()}/has", json_encode([
            'dbName' => $this->db_config->get_dbname(),
            'collectionName' => $name,
        ], JSON_THROW_ON_ERROR));

        $json = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        return ((bool)$json['data']['has']) === true;
    }

    /**
     * @inheritDoc
     */
    public function list(): array
    {
        $response = $this->curl->post("{$this->get_base_url()}/list", json_encode([
            'dbName' => $this->db_config->get_dbname(),
        ], JSON_THROW_ON_ERROR));

        $json = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        return $json['data'];
    }
}
