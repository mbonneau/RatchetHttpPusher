<?php

require __DIR__ . '/vendor/autoload.php';

$loop   = React\EventLoop\Factory::create();
$pusher = new MyApp\Pusher;

// Listen for push requests sent via HTTP
$httpServer = new \React\Http\StreamingServer(array(
    new \React\Http\Middleware\RequestBodyBufferMiddleware(16 * 1024 * 1024), // 16 MiB
    function (\Psr\Http\Message\ServerRequestInterface $request) use ($pusher) {
        // Maybe check the request uri here
        if ($request->getHeader('Content-Type') !== ['application/json']) {
            return new \React\Http\Response(400, array(), 'Server only accepts json');
        }

        $pusher->onBlogEntry($request->getBody()->getContents());

        return new \React\Http\Response(200, array('Content-Type' => 'text/plain'), "OK\n");
    }
));

$httpSocket = new React\Socket\Server(8081, $loop);
$httpServer->listen($httpSocket);

// Set up our WebSocket server for clients wanting real-time updates
$webSock = new React\Socket\Server('0.0.0.0:8080', $loop); // Binding to 0.0.0.0 means remotes can connect
$webServer = new Ratchet\Server\IoServer(
    new Ratchet\Http\HttpServer(
        new Ratchet\WebSocket\WsServer(
            new Ratchet\Wamp\WampServer(
                $pusher
            )
        )
    ),
    $webSock
);

$loop->run();
