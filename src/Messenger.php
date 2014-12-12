<?php

namespace gries\Rcon;

class Messenger
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Send text to the server.
     *
     * @param          $messageText
     *
     * @param callable $callable
     *
     * @return string
     */
    public function send($messageText, callable $callable = null)
    {
        $message = new Message($messageText);

        $response = $this->connection
            ->sendMessage($message)
            ->getBody()
        ;

        if ($callable) {
            $response = call_user_func($callable, $response);
        }

        return $response;
    }
}
