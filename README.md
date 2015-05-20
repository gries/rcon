RCON
========

This is a simple RCON-Client for php.

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/49b6b2b4-06a6-40c3-af3f-a921e790e0c6/big.png)](https://insight.sensiolabs.com/projects/49b6b2b4-06a6-40c3-af3f-a921e790e0c6)

[![Build Status](https://travis-ci.org/gries/rcon.png?branch=master)](https://travis-ci.org/gries/rcon)

Installation
------------

RCON can be installed via. Composer:

    composer require "gries/rcon"

Usage
-----------
```php
use gries\Rcon\MessengerFactory;
use gries\Rcon\Messenger;

require_once __DIR__.'/vendor/autoload.php';

// setup the messenger
$messenger = MessengerFactory::create('example.com', 25575, 'mypass');

// send a simple message
$response = $messenger->send('list');
echo $response; // a,b,c

// send a message and parse the command via. a callable
$response = $messenger->send('list', function($arg) {
    return explode(',', $arg);
});
print_r($response); // ['a', 'b', 'c']
```
Running the tests
-----------------
    vendor/bin/phpspec run

Contribute!
-----------
Feel free to give me feedback/feature-request/bug-reports via. github issues.
Or just send me a pull-request :)


Author
------

- [Christoph Rosse](http://twitter.com/griesx)

License
-------

For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
