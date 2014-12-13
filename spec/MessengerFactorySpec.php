<?php

namespace spec\gries\Rcon;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MessengerFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('gries\Rcon\MessengerFactory');
    }
}
