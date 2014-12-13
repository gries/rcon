<?php

namespace gries\Rcon;

/**
 * Class MessengerFactory
 *
 * Use this class to create a new instance of a Messenger.
 *
 * @package gries\Rcon
 */
class MessengerFactory
{

    /**
     * Create a new RconMessenger
     *
     * @param $host
     * @param $port
     * @param $password
     *
     * @return \gries\Rcon\Messenger
     */
    public static function create($host, $port, $password)
    {
        $connection = ConnectionFactory::create($host, $port, $password);

        return new Messenger($connection);
    }
}
