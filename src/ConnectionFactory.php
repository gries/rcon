<?php

namespace gries\Rcon;

/**
 * Class ConnectionFactory
 *
 * @package gries\Rcon
 */
class ConnectionFactory
{
    /**
     * Create a new RconConnection
     *
     * @param $host
     * @param $port
     * @param $password
     *
     * @return \gries\Rcon\Connection
     */
    public static function create($host, $port, $password)
    {
        $factory = new \Socket\Raw\Factory();
        $socket = $factory->createClient(sprintf('%s:%s', $host, $port));

        return new \gries\Rcon\Connection($socket, $password);
    }
}
