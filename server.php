<?php

require 'vendor/autoload.php';

use CCM\Core\Application;

$ccm = new Application();

$app = function ($request, $response) use($ccm) {
    $ccm->run($request, $response);
};

$loop = React\EventLoop\Factory::create();
$socket = new React\Socket\Server($loop);
$http = new React\Http\Server($socket, $loop);

$http->on('request', $app);
echo "Server running at http://0.0.0.0:8080\n";

$socket->listen(8080, '0.0.0.0');
$loop->run();