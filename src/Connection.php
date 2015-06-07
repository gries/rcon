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

        return $this->getResponseMessage();
    }

    /**
     * Get the response value of the server.
     *
     * @return string
     */
    protected function getResponseMessage()
    {

        // read the first 4 bytes which include the length of the response
        $lengthEncoded = $this->client->read(4);

        if (strlen($lengthEncoded) < 4) {
            return new Message('', Message::TYPE_RESPONSE_VALUE);
        }

        $lengthInBytes = unpack('V1size', $lengthEncoded)['size'];

        if ($lengthInBytes <= 0) {
            return new Message('', Message::TYPE_RESPONSE_VALUE);
        }

        $responseData = $this->client->read($lengthInBytes);

        // return an empty message if the server did not send any response
        if (null === $responseData) {
            return new Message('', Message::TYPE_RESPONSE_VALUE);
        }

        $responseMessage = new Message();
        $responseMessage->initializeFromRconData($responseData);

        if ($lengthInBytes >= 4000) {
            $this->handleFragmentedResponse($responseMessage);
        }

        return $responseMessage;
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

    /**
     * This handles a fragmented response.
     * (https://developer.valvesoftware.com/wiki/Source_RCON_Protocol#Multiple-packet_Responses)
     *
     * We basically send a RESPONSE_VALUE Message to the server to force the response of the rest of the package,
     * until we receive another package or an empty response.
     *
     * All the received data is then appended to the current ResponseMessage.
     *
     * @param $responseMessage
     * @throws Exception\InvalidPacketException
     */
    protected function handleFragmentedResponse(Message $responseMessage)
    {
        do {
            usleep(20000); // some servers stop responding if we send to many packages so we wait 20ms
            $this->client->write(Message::TYPE_RESPONSE_VALUE);

            $responseData = $this->client->read(4096);
            if (empty($responseData)) {
                break;
            }
            $fragmentedMessage = new Message();
            $fragmentedMessage->initializeFromRconData($responseData, true);

            $responseMessage->append($fragmentedMessage);

            if ($fragmentedMessage->getType() !== Message::TYPE_RESPONSE_VALUE) {
                break;
            }

        } while (true);
    }
}
