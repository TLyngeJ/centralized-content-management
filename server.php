<?php

require 'vendor/autoload.php';
require 'app/Framework.php';

$framework = new Framework();

$app = function ($request, $response) use($framework) {
    $framework->run($request, $response);
};

$loop = React\EventLoop\Factory::create();
$socket = new React\Socket\Server($loop);
$http = new React\Http\Server($socket, $loop);

$http->on('request', $app);
echo "Server running at http://0.0.0.0:8080\n";

$socket->listen(8080, '0.0.0.0');
$loop->run();