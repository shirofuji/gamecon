<?php 
error_reporting(E_ERROR);

require __DIR__."/vendor/autoload.php";
require __DIR__."/event_handler.php";

$loop = React\EventLoop\Factory::create();
$evHndler = new event_handler();

$zctx = new React\ZMQ\Context($loop);
$rcvr = $zctx->getSocket(ZMQ::SOCKET_PULL);
$rcvr->bind("tcp://127.0.0.1:5555");
$rcvr->on('message',array($evHndler,'onPush'));

$app = new Ratchet\App('gamecon-shirofuji.c9.io',8081,'0.0.0.0',$loop);
$app->route('/game',$evHndler);
$app->run();
