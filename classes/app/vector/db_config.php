<?php

namespace local_mxaimanager\app\vector;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();
// @codeCoverageIgnoreEnd

class db_config
{
    private string $host;
    private string $port;
    private string $username;
    private string $password;
    private bool $tls;

    public function __construct(
        string $host,
        string $port,
        string $username,
        string $password,
        bool $tls
    ) {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->tls = $tls;
    }

    public function get_host(): string
    {
        return $this->host;
    }

    public function get_port(): string
    {
        return $this->port;
    }

    public function get_username(): string
    {
        return $this->username;
    }

    public function get_password(): string
    {
        return $this->password;
    }

    public function is_tls(): bool
    {
        return $this->tls;
    }
}
