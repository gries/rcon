<?php

namespace spec\gries\Rcon;

use gries\Rcon\Message;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MessageSpec extends ObjectBehavior
{
    protected $command;

    protected $encodedCommand;

    protected $encodedAuthCommand;

    protected $encodedResponseData;

    function let()
    {
        $this->command = 'mycommand';
        $this->encodedCommand = base64_decode('EwAAAAEAAAACAAAAbXljb21tYW5kAAA=');
        $this->encodedAuthCommand = base64_decode('EwAAAAEAAAADAAAAbXljb21tYW5kAAA=');
        $this->encodedResponseData = base64_decode('AQAAAAMAAABteWNvbW1hbmQAAA==');
        $this->beConstructedWith($this->command);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('gries\Rcon\Message');
    }

    function its_type_is_command_by_default()
    {
        $this->getType()->shouldBe(Message::TYPE_COMMAND);
    }

    function it_can_be_converted_to_a_rcon_proctocol_string()
    {
        $this->convertToRconData($id = 1)->shouldBe($this->encodedCommand);
    }

    function it_can_be_converted_to_a_rcon_proctocol_string_using_a_diffrent_message_type()
    {
        $this->beConstructedWith('mycommand', Message::TYPE_AUTH);
        $this->convertToRconData($id = 1)->shouldBe($this->encodedAuthCommand);
    }

    function it_can_initialize_itself_from_a_response()
    {
        $this->beConstructedWith('');

        $this->initializeFromRconData($this->encodedResponseData);

        $this->getType()->shouldBe(Message::TYPE_AUTH);
        $this->getBody()->shouldBe('mycommand');
        $this->getId()->shouldBeLike(1);
    }

    function it_should_not_allow_invalid_responses()
    {
        $this->beConstructedWith('');

        $this->shouldThrow('gries\Rcon\Exception\InvalidPacketException')
            ->during('initializeFromRconData', ['invalid']);
    }
}
