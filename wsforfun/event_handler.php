<?php

use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;

class event_handler implements WampServerInterface{
			function __construct(){
						$this->clients = new \SplObjectStorage();
						$this->room = array();
			}
			function onOpen(ConnectionInterface $conn){
						if($this->clients->count() == 4){
							$conn->send('[8,"system","{\"error\":\"server full, unable to connect\"}"]');
							$conn->close();
						}else{
							$conn->index = 0;
							$conn->username = '';
							$this->clients->attach($conn);
						}
						echo 'new connection detected.'.PHP_EOL;
			}
			function onClose(ConnectionInterface $conn){
						foreach($this->room as $room){
							if($room['topic']->has($conn)){
								$this->onUnsubscribe($conn,$room['topic']);
							}
						}
						$this->clients->detach($conn);
						echo 'connection disconnected.'.PHP_EOL;
			}
			function onSubscribe(ConnectionInterface $conn, $topic){
						if(!isset($this->room[$topic->getId()])){
							$this->room[$topic->getId()] = array(
								'topic'=>$topic,
								'users'=>0,
								'current_turn'=>0
								);
						}
							// $this->room[$topic->getId()]['users'] += 1;
							// if($this->room[$topic->getId()]['users'] == 4){
						 	// $topic->broadcast(array('gamestart'=>true));
						    // }
						
						echo 'user joined to '.$topic->getId().PHP_EOL;
			}
			function onUnsubscribe(ConnectionInterface $conn, $topic){
				$this->room['room']['users'] -= 1;
				echo 'user left room'.PHP_EOL;
			}
			function onCall(ConnectionInterface $conn, $id, $topic, array $params){
				echo $id;
			}
			function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $elligible){
			
			}
			function onError(ConnectionInterface $conn, Exception $e){
		 			$log = fopen('error.log','a');
						fwrite($log,$e->getMessage()."\r\n");
						fclose($log);
			}
			function onPush($message){
						$data = json_decode($message,true);
						$room = $this->getRoom('room');
						echo $message.PHP_EOL;
						$response = array('user'=>$data['user']);
						switch($data['operation']){
							case 'dice':
								if($room){
									$response['dice'] = rand(1,6);
									$room->broadcast(json_encode($response));
									echo 'dice rolled'.PHP_EOL;
								}
								break;								
							case 'endturn':
								$this->room['room']['current_turn'] +=1;
								if($this->room['room']['current_turn']>4){
									$this->room['room']['current_turn']=1;
								}
								$room = $this->room['room']['topic'];
								$turn = $this->getUserByIndex($this->room['room']['current_turn'],
															$room);
								// while(!$turn){
								// 	$this->room['room']['current_turn']+=1;
								// 	$turn = $this->getUserByIndex($this->room['room']['current_turn'],$room);
								// }
								$room->broadcast(json_encode(array('game'=>true,'turn'=>$turn->username)));
								break;
							case 'join':
								// subscribe to 'room' first before send userdata
								$user = $this->getUser($data['data']);
								
								$response['newuser']=true;
								if($user && $room){
									$this->room['room']['users'] += 1;
									$user->index = $this->room['room']['users'];
									$user->username = $data['user'];
									$room->broadcast(json_encode($response));
									$this->bCurrentUsers($room);
									echo 'userdata updated'.PHP_EOL;
									echo 'room now has '.$this->room['room']['users'].' users'.PHP_EOL;
									if($this->room['room']['users'] == 4){
										$index =0;
										$turn = null;
										while(!$turn){
											$index+=1;
											if($index>4) $index = 1;
											$this->room['room']['current_turn'] = $index;
											$turn = $this->getUserByIndex($index,$room);
										}
										$room->broadcast(json_encode(array('game'=>true,'turn'=>$turn->username)));
										echo $turn->username.'\'s turn.'.PHP_EOL;
									}
								}
								break;
							default:
								break;
						}
			}
			function getRoom($topicId){
				if( !array_key_exists($topicId, $this->room) ) {
					return;
				}
			
				$room = isset($this->room[$topicId])?$this->room[$topicId]['topic']:false;
				return $room;
			}

			function getUser($wsid){
				$conn = false;
				$this->clients->rewind();
				while($this->clients->valid()){
					$conn = $this->clients->current();
					if($conn->WAMP->sessionId == $wsid){
						break;
					}
					$this->clients->next();
				}
				return $conn;
			}
			function getUserByIndex($index,$topic){
				$c=false;
				$this->clients->rewind();
				while($this->clients->valid()){
					$c = $this->clients->current();
					if($c->index == $index && $topic->has($c)){
						break;
					}
					$this->clients->next();
				}
				return $c;
			}
			function bCurrentUsers($room){
				$users=array();
				$this->clients->rewind();
				while($this->clients->valid()){
					$conn = $this->clients->current();
					if($conn->username != ''){
						$users[] = $conn->username;
					}
					$this->clients->next();
				}
				$room->broadcast(json_encode(array('current_users'=>$users)));
			}
}
