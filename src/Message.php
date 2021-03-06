<?php

namespace gries\Rcon;

use gries\Rcon\Exception\InvalidPacketException;

/**
 * Class Message
 *
 * This is a representation of a RCON Message.
 * A message MUST always have a type and an ID before sending it.
 *
 * @package gries\Rcon
 */
class Message
{
    /**
     * The reply type of  a failed authentication request.
     *
     * @var int
     */
    const TYPE_AUTH_FAILURE = -1;

    /**
     * Type for server responses.
     */
    const TYPE_RESPONSE_VALUE = 0;

    /**
     * Standard type for commands.
     *
     * @var int
     */
    const TYPE_COMMAND = 2;

    /**
     * Type for authentication with the server.
     *
     * @var int
     */
    const TYPE_AUTH = 3;

    /**
     * @var mixed The type of this message.
     */
    protected $type;

    /**
     * The body of this message.
     *
     * @var string
     */
    protected $body;

    /**
     * The id of this message.
     *
     * @var int
     */
    protected $id;

    /**
     * @param string $body
     * @param int    $type
     * @param null   $id
     */
    public function __construct($body = '', $type = Message::TYPE_COMMAND, $id = null)
    {
        $this->body = $body;
        $this->type = $type;
        $this->id = $id;
    }

    /**
     * The message id used for this message.
     * This will be encoded in the RconData.
     *
     * @param int $id
     *
     * @return string
     */
    public function convertToRconData($id)
    {
        $idAndTypePacked = pack('VV', $id, $this->type);
        $endOfMessage    = chr(0) . chr(0);

        $message = $idAndTypePacked . $this->body . $endOfMessage;

        $data = pack("V", strlen($message)) . $message;

        return $data;
    }

    /**
     * Get the type of this message.
     *
     * @return int|mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Parses data from a RCON Response.
     * Note: the first 4 bytes that indicate the size of the packet MUST not be included.
     *
     * @param $data
     * @param bool|$fragmented If the data comes from a fragmented response only the id and body will be present.
     *
     * @throws InvalidPacketException
     */
    public function initializeFromRconData($data, $fragmented = false)
    {
        if ($fragmented) {
            $packet = unpack("V1id/a*body", $data);
            $packet['type'] =  static::TYPE_RESPONSE_VALUE;
        } else {
            // 8 byte for id + type and atleast 2 byte if the body is empty
            if (mb_strlen($data) < 10) {
                throw new InvalidPacketException();
            }

            $packet = unpack("V1id/V1type/a*body", $data);
        }

        if (!is_array($packet) ||
            !isset($packet['id']) ||
            !isset($packet['body']) ||
            !isset($packet['type'])
        ) {
            throw new InvalidPacketException();
        }

        $this->id = $packet['id'];
        $this->type = $packet['type'];
        $this->body = rtrim($packet['body']); // removes null bytes at the end of the string
    }

    /**
     * The content of this message.
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Get the id of this message.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Append a message to this message.
     * This will only append the body, the id/type will be unchanged.
     *
     * @param Message $message
     */
    public function append(Message $message)
    {
        $this->body .= $message->getBody();
    }
}
