<?php

namespace local_mxaimanager\app\vector\elasticsearch;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();
// @codeCoverageIgnoreEnd

global $CFG;
require_once $CFG->libdir . '/filelib.php';

use local_mxaimanager\app\factory as base_factory;

class vector implements \local_mxaimanager\app\vector\interfaces\db_vector
{
    public const VECTOR_FIELD = 'vector';

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
            // Elasticsearch basic auth: username:password
            "Authorization: Basic " . base64_encode(
                $this->db_config->get_username() . ':' . $this->db_config->get_password()
            ),
        ]);

        $this->curl->setopt(['CURLOPT_TIMEOUT' => 10]);
    }

    private function get_base_url(): string
    {
        return "http://{$this->db_config->get_host()}:{$this->db_config->get_port()}";
    }

    public function insert(string $collection, array $record): int
    {
        // Elasticsearch does not have a dedicated "insert" – we just index.
        // If a document with the same ID already exists it will be overwritten.
        return $this->upsert($collection, $record);
    }

    public function upsert(string $collection, array $record): int
    {
        $id = $record['id'] ?? null;
        if ($id === null) {
            throw new \InvalidArgumentException('Record must contain an "id" field for Elasticsearch upsert.');
        }

        $url = "{$this->get_base_url()}/{$collection}/_doc/" . urlencode((string)$id);

        $response = $this->curl->put($url, json_encode($record, JSON_THROW_ON_ERROR));
        $json = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        // Elasticsearch returns the same ID we sent
        return (int)$json['_id'];
    }

    public function insert_bulk(string $collection, array $records): array
    {
        if (empty($records)) {
            return [];
        }

        $newline_delimeted_json = '';
        foreach ($records as $record) {
            $id = $record['id'] ?? null;
            if ($id === null) {
                throw new \InvalidArgumentException('Every record must contain an "id" field for bulk insert.');
            }

            $meta = ['index' => ['_index' => $collection, '_id' => $id]];
            $newline_delimeted_json .= json_encode($meta, JSON_THROW_ON_ERROR) . "\n";
            $newline_delimeted_json .= json_encode($record, JSON_THROW_ON_ERROR) . "\n";
        }

        $url = "{$this->get_base_url()}/_bulk";
        $response = $this->curl->post($url, $newline_delimeted_json);
        $json = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        // Collect the IDs that were successfully indexed
        $ids = [];
        foreach ($json['items'] ?? [] as $item) {
            if (isset($item['index']['_id'])) {
                $ids[] = (int)$item['index']['_id'];
            }
        }

        return $ids;
    }

    public function get_by_id(string $collection, int $id, array $fields): ?array
    {
        $url = "{$this->get_base_url()}/{$collection}/_doc/" . urlencode((string)$id);

        // _source filtering
        $params = empty($fields) ? [] : ['_source' => $fields];
        $url .= (empty($params) ? '' : '?' . http_build_query($params));

        $response = $this->curl->get($url);
        $json = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        return $json['found'] ?? false ? $json['_source'] : null;
    }

    public function get_by_field(string $collection, string $field, string $value, array $fields): ?array
    {
        $body = [
            'query' => [
                'term' => [
                    $field => $value,
                ],
            ],
        ];
        if (!empty($fields)) {
            $body['_source'] = $fields;
        }

        $response = $this->curl->post(
            "{$this->get_base_url()}/{$collection}/_search",
            json_encode($body, JSON_THROW_ON_ERROR)
        );
        $json = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        $hits = $json['hits']['hits'] ?? [];
        return !empty($hits) ? $hits[0]['_source'] : null;
    }

    public function delete_by_id(string $collection, int $id): bool
    {
        $url = "{$this->get_base_url()}/{$collection}/_doc/" . urlencode((string)$id);
        $response = $this->curl->delete($url);
        $json = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        return ($json['result'] ?? '') === 'deleted';
    }

    public function delete_by_field(string $collection, string $field, string $value): bool
    {
        $body = [
            'query' => [
                'term' => [
                    $field => $value,
                ],
            ],
        ];

        $response = $this->curl->post(
            "{$this->get_base_url()}/{$collection}/_delete_by_query",
            json_encode($body, JSON_THROW_ON_ERROR)
        );
        $json = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        return ($json['deleted'] ?? 0) > 0;
    }

    /**
     * @inheritDoc
     */
    public function search(
        string $collection,
        array $vector,
        array $fields,
        array $equal_conditions,
        array $not_equal_conditions,
        int $limit
    ): array {
        $body = [
            'size' => $limit,
            'knn' => [
                'field' => self::VECTOR_FIELD,
                'query_vector' => $vector,
                'k' => $limit,
                'num_candidates' => max($limit * 10, 100), // safe default
            ]
        ];

        // _source filtering
        if (!empty($fields)) {
            $body['_source'] = $fields;
        }

        // Build must / must_not clauses from the conditions
        $must = $this->build_conditions($equal_conditions);
        $must_not = $this->build_conditions($not_equal_conditions);

        if (!empty($must) || !empty($must_not)) {
            $body['query'] = ['bool' => []];
            if (!empty($must)) {
                $body['query']['bool']['must'] = $must;
            }
            if (!empty($must_not)) {
                $body['query']['bool']['must_not'] = $must_not;
            }
        }

        $response = $this->curl->post(
            "{$this->get_base_url()}/{$collection}/_search",
            json_encode($body, JSON_THROW_ON_ERROR)
        );
        $json = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        $results = [];
        foreach ($json['hits']['hits'] ?? [] as $hit) {
            $row = $hit['_source'] ?? [];
            // Attach the distance if the caller might need it
            $row['distance'] = $hit['_score'] ?? null;
            $row['id'] = $hit['_id'] ?? null;
            $results[] = $row;
        }

        return $results;
    }

    /**
     * @param array $conditions $field => $value | [$value1, $value2, ...]
     * @return array
     */
    private function build_conditions(array $conditions): array
    {
        $clauses = [];

        foreach ($conditions as $field => $value) {
            if ($value === null) {
                continue;
            }

            if (!is_array($value)) {
                $value = [$value];
            }

            if (empty($value)) {
                continue;
            }

            // "terms" works for both single value and array
            $clauses[] = [
                'terms' => [
                    $field => $value,
                ],
            ];
        }

        return $clauses;
    }
}
