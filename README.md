RCON
========

This is a simple RCON-Client for php.

Installation
------------

RCON can be installed via. Composer:

    composer require "gries/rcon"

Usage
-----------

    use gries\Rcon\ConnectionFactory;
    use gries\Rcon\Messenger;

    require_once __DIR__.'/vendor/autoload.php';

    // setup the messenger
    $connection = ConnectionFactory::create('example.com', 25575, 'mypass');
    $messenger         = new Messenger($connection);

    // send a simple message
    $response = $messenger->send('list');
    echo $response; // a,b,c

    // send a message and parse the command via. a callable
    $response = $messenger->send('list', function($arg) {
        return explode(',', $arg);
    });
    echo $response; // ['a', 'b', 'c']

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
