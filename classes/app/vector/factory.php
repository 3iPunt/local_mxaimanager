<?php

namespace local_mxaimanager\app\vector;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();
// @codeCoverageIgnoreEnd

use local_mxaimanager\app\factory as base_factory;

class factory
{
    private base_factory $base_factory;

    public function __construct(base_factory $base_factory)
    {
        $this->base_factory = $base_factory;
    }

    public function get_config(): ?object
    {
        return $this->base_factory->cfg()->moxis->vectordb ?? null;
    }

    public function db(): interfaces\db
    {
        if (defined('PHPUNIT_TEST') && PHPUNIT_TEST) {
            return new test\factory($this->base_factory);
        }

        if (!$config = $this->get_config()) {
            throw new \Exception('"$CFG->moxis->vectordb" is not configured!');
        }

        $db_config = new db_config(
            dbname: $config->dbname,
            host: $config->host,
            port: $config->port,
            username: $config->username,
            password: $config->password,
        );

        switch ($config->type) {
            case 'elasticsearch':
                return $this->elasticsearch($db_config);
            default:
                throw new \Exception("Unsupported vector db type: '{$this->get_config()->type}'");
        }
    }

    public function elasticsearch(db_config $db_config): elasticsearch\factory
    {
        return new elasticsearch\factory($this->base_factory, $db_config);
    }
}
