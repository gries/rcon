<?php

namespace gries\Rcon;

use gries\Rcon\Exception\AuthenticationFailedException;
use Socket\Raw\Socket;

/**
 * Class Connection
 *
 * The Connection class wraps around a socket and is able to pass RconMessages
 * to a server.
 * Responses will be passed back as RconResponse.
 *
 *
 * @package gries\Rcon
 */
class Connection
{
    /**
     * The socket used to send messages to the server.
     *
     * @var \Socket\Raw\Socket
     */
    protected $client;

    /**
     * Current message id that is processed.
     *
     * @var int
     */
    protected $currentId = 0;

    /**
     * Initialize this connection.
     * The socket client is used to send and receive actual data to the rcon server.
     *
     * @param Socket $client
     * @param        $password
     */
    public function __construct(Socket $client, $password)
    {
        $this->client = $client;
        $this->authenticate($password);
    }

    /**
     * Send a RCON message.
     *
     *
     * @param Message $message
     *
     * @return Message
     */
    public function sendMessage(Message $message)
    {
        $this->currentId++;
        $messageData = $message->convertToRconData($this->currentId);
        $this->client->write($messageData);

        $responseData = $this->getResponseData();

        // return an empty message if the server did not send any response
        if (null === $responseData) {
            return new Message('', Message::TYPE_RESPONSE_VALUE);
        }

        $responseMessage = new Message();
        $responseMessage->initializeFromRconData($responseData);

        return $responseMessage;
    }

    /**
     * Get the response value of the server.
     *
     * @return string
     */
    protected function getResponseData()
    {
        // read the first 4 bytes which include the length of the response
        $lengthEncoded = $this->client->read(4);

        if (strlen($lengthEncoded) < 4) {
            return null;
        }

        $lengthInBytes = unpack('V1size', $lengthEncoded)['size'];

        if ($lengthInBytes <= 0) {
            return null;
        }

        return $this->client->read($lengthInBytes);
    }

    /**
     * Authenticate with the server.
     *
     * @param $password
     *
     * @throws Exception\AuthenticationFailedException
     */
    protected function authenticate($password)
    {
        $message = new Message($password, Message::TYPE_AUTH);
        $response = $this->sendMessage($message);

        if ($response->getType() === Message::TYPE_AUTH_FAILURE) {
            throw new AuthenticationFailedException('Could not authenticate to the server.');
        }
    }
}
