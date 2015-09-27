<?php
	header("Access-Control-Allow-Origin: *");
	if(!isset($_POST["op"]) && !isset($_POST["user"])){
		die("Invalid data.");
	}else{
		$entryData = array(
			"operation"=>$_POST['op'],
			"user"=>$_POST['user'],
			"data"=>isset($_POST['data'])?$_POST['data']:""
			);
	}
	$context = new ZMQContext();
	$socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'snder');
	$socket->connect("tcp://127.0.0.1:5555");

	$socket->send(json_encode($entryData));
	echo json_encode(array("response"=>true));
