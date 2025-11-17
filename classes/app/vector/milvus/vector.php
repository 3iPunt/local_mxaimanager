<?php

namespace local_mxaimanager\app\vector\milvus;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();
// @codeCoverageIgnoreEnd

global $CFG;
require_once $CFG->libdir . '/filelib.php';

use local_mxaimanager\app\factory as base_factory;

class vector implements \local_mxaimanager\app\vector\interfaces\db_vector
{
    private base_factory $base_factory;
    private \local_mxaimanager\app\vector\db_config $db_config;
    private \curl $curl;

    public function __construct(base_factory $base_factory, \local_mxaimanager\app\vector\db_config $db_config)
    {
        $this->base_factory = $base_factory;
        $this->db_config = $db_config;
        $this->curl = $base_factory->curl();
        $this->curl->setHeader([
            'Content-Type: application/json',
            "Authorization: Bearer {$this->db_config->get_username()}:{$this->db_config->get_password()}"
        ]);

        $this->curl->setopt(['CURLOPT_TIMEOUT' => 10]);
    }

    private function get_base_url(): string
    {
        return "http://{$this->db_config->get_host()}:{$this->db_config->get_port()}/v2/vectordb/entities";
    }

    public function upsert(string $collection, array $record): int
    {
        $response = $this->curl->post("{$this->get_base_url()}/upsert", json_encode([
            'dbName' => $this->db_config->get_dbname(),
            'collectionName' => $collection,
            'data' => [
                $record
            ]
        ], JSON_THROW_ON_ERROR));

        $json = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        return $json['data']['upsertIds'][0];
    }
    public function insert(string $collection, array $record): int
    {
        $response = $this->curl->post("{$this->get_base_url()}/insert", json_encode([
            'dbName' => $this->db_config->get_dbname(),
            'collectionName' => $collection,
            'data' => [
                $record
            ]
        ], JSON_THROW_ON_ERROR));

        $json = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        return $json['data']['insertIds'][0];
    }
    public function insert_bulk(string $collection, array $records): array
    {
        $response = $this->curl->post("{$this->get_base_url()}/insert", json_encode([
            'dbName' => $this->db_config->get_dbname(),
            'collectionName' => $collection,
            'data' => $records
        ], JSON_THROW_ON_ERROR));

        $json = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        return $json['data']['insertIds'];
    }

    public function get_by_id(string $collection, int $id, array $fields): ?array
    {
        $response = $this->curl->post("{$this->get_base_url()}/get", json_encode([
            'dbName' => $this->db_config->get_dbname(),
            'collectionName' => $collection,
            'outputFields' => $fields,
            'id' => (string)$id
        ], JSON_THROW_ON_ERROR));

        $json = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        return $json['data'][0] ?? null;
    }

    public function get_by_field(string $collection, string $field, string $value, array $fields): ?array
    {
        $response = $this->curl->post("{$this->get_base_url()}/query", json_encode([
            'dbName' => $this->db_config->get_dbname(),
            'collectionName' => $collection,
            'outputFields' => $fields,
            'filter' => "{$field} == {$value}"
        ], JSON_THROW_ON_ERROR));

        $json = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        return $json['data'][0] ?? null;
    }

    public function delete_by_id(string $collection, int $id): bool
    {
        $response = $this->curl->post("{$this->get_base_url()}/delete", json_encode([
            'dbName' => $this->db_config->get_dbname(),
            'collectionName' => $collection,
            'filter' => "id == {$id}"
        ], JSON_THROW_ON_ERROR));

        $json = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        return ((int)$json['code']) === 0;
    }

    public function delete_by_field(string $collection, string $field, string $value): bool
    {
        $response = $this->curl->post("{$this->get_base_url()}/delete", json_encode([
            'dbName' => $this->db_config->get_dbname(),
            'collectionName' => $collection,
            'filter' => "{$field} == '{$value}'"
        ], JSON_THROW_ON_ERROR));

        $json = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        return ((int)$json['code']) === 0;
    }

    /**
     * @inheritDoc
     */
    public function search(string $collection, array $vector, array $fields, array $equal_conditions, array $not_equal_conditions, int $limit): array
    {
        $response = $this->curl->post("{$this->get_base_url()}/search", json_encode([
            'dbName' => $this->db_config->get_dbname(),
            'collectionName' => $collection,
            'data' => [
                $vector
            ],
            'filter' => $this->get_filter_by_conditions($equal_conditions, $not_equal_conditions),
            'outputFields' => $fields,
            'annsField' => 'vector',
            'limit' => $limit
        ], JSON_THROW_ON_ERROR));

        $json = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        return $json['data'];
    }

    private function get_filter(string $field, mixed $value, bool $first, bool $equal, bool $is_array): string
    {
        $equal_operator = $equal ? '==' : '!=';
        $condition_operator = $equal & $is_array ? '||' : '&&';

        // Escape string values
        if (is_string($value)) {
            $escaped_value = addslashes($value);
            $value = "'{$escaped_value}'";
        }

        if (!$first) {
            return " {$condition_operator} {$field} {$equal_operator} {$value}";
        }

        return "{$field} {$equal_operator} {$value}";
    }

    private function get_filter_by_conditions(array $equal_conditions, array $not_equal_conditions): string
    {
        $filter = '';

        $first = true;
        foreach ([true => $equal_conditions, false => $not_equal_conditions] as $equal => $conditionset) {
            foreach ($conditionset as $field => $value) {
                if ($value === null) {
                    continue;
                }

                if (!is_array($value)) {
                    $filter .= $this->get_filter($field, $value, $first, $equal, false);
                    $first = false;

                    continue;
                }

                if (empty($value)) {
                    continue;
                }

                $filter .= $first ? '(' : ' && (';
                $first_in_array = true;
                foreach ($value as $v) {
                    $filter .= $this->get_filter($field, $v, $first_in_array, $equal, true);
                    $first = false;
                    $first_in_array = false;
                }
                $filter .= ')';
            }

        }

        return $filter;
    }
}
