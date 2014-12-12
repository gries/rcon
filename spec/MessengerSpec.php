<?php

namespace spec\gries\Rcon;

use gries\Rcon\Connection;
use gries\Rcon\Message;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MessengerSpec extends ObjectBehavior
{
    function let(\gries\Rcon\Connection $connection)
    {
        $this->beConstructedWith($connection);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('gries\Rcon\Messenger');
    }

    function it_sends_a_message(\gries\Rcon\Connection $connection)
    {
        $expectedMessage = new Message('list');
        $expectedResponseMessage = new Message('a,b,c');
        $connection->sendMessage($expectedMessage)->willReturn($expectedResponseMessage);

        $this->send('list')->shouldReturn('a,b,c');
    }

    function it_parses_the_response_with_a_given_callable(\gries\Rcon\Connection $connection)
    {
        $expectedMessage = new Message('list');
        $expectedResponseMessage = new Message('a,b,c');
        $connection->sendMessage($expectedMessage)->willReturn($expectedResponseMessage);

        $callable = function($arg) {
            return explode(',', $arg);
        };

        $this->send('list', $callable)->shouldReturn(['a', 'b', 'c']);
    }
}
