<?php

namespace spec\gries\Rcon;

use gries\Rcon\Message;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ConnectionSpec extends ObjectBehavior
{
    function let(\Socket\Raw\Socket $socket)
    {
        // length parameter 32 bit of the length of this packet
        // length is 19
        $lengthData = base64_decode('EwAAAA==');

        // body = 'mycommand', type='1'
        $rconData =  base64_decode('"AQAAAAIAAABteWNvbW1hbmQAAA==');

        $socket->read(4)->willReturn($lengthData);
        $socket->read(19)->willReturn($rconData);
        $socket->write('abc1234')->willReturn(null);

        // auth packet with password: mypass
        $authData = base64_decode('EAAAAAEAAAADAAAAbXlwYXNzAAA=');
        $socket->write($authData)->willReturn(null);

        $this->beConstructedWith($socket, 'mypass');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('gries\Rcon\Connection');
    }


    function it_sends_messages_to_the_server(\Socket\Raw\Socket $socket, \gries\Rcon\Message $message)
    {
        $rconData = 'abc1234';

        $message->convertToRconData(2)->willReturn($rconData);
        $socket->write($rconData)->shouldBeCalled();

        $this->sendMessage($message);
    }

    function it_sends_messages_with_incremented_ids(\gries\Rcon\Message $message)
    {
        $rconData = 'abc1234';

        $message->convertToRconData(2)->shouldBeCalled()->willReturn($rconData);
        $message->convertToRconData(3)->shouldBeCalled()->willReturn($rconData);
        $message->convertToRconData(4)->shouldBeCalled()->willReturn($rconData);

        $this->sendMessage($message);
        $this->sendMessage($message);
        $this->sendMessage($message);
    }

    function it_handles_empty_response_data(\Socket\Raw\Socket $socket, \gries\Rcon\Message $message)
    {
        $message->convertToRconData(2)->willReturn('abc1234');

        $lengthData = '';

        // call to find out the packet length
        $socket->read(4)->shouldBeCalled()->willReturn($lengthData);

        $this->sendMessage($message);
    }

    function it_gives_us_response_objects_for_our_sent_data(\Socket\Raw\Socket $socket, \gries\Rcon\Message $message)
    {
        $message->convertToRconData(2)->willReturn('abc1234');

        $expectedMessage = new Message('mycommand', Message::TYPE_COMMAND, 1);

        $this->sendMessage($message)->shouldBeAMessageLike($expectedMessage);
    }

    public function getMatchers()
    {
        return [
            'beAMessageLike' => function (Message $actual, Message $expected) {
                if (
                    $expected->getId() !== $actual->getId() ||
                    $expected->getType() !== $actual->getType() ||
                    $expected->getBody() !== $actual->getBody()

                ) {
                    return false;
                }

                return true;
             }
        ];
    }
}
