<?php
 //host
$host = 'localhost';
//port
$port = '8403'; 
//socketpath. Este parâmetro opcional especifica o nome do soquete de domínio UNIX a ser usado para se conectar ao servidor.
$socketpath = 'cws/src/server.php'; 
//magickey. Adiciona algum nível de confiança para a integridade do protocolo. (RFC 6455 - The WebSocket Protocol).
$magickey = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11'; 
//null var
$null = NULL; 

//cria um socket com socket_create(Domain, Type, Protocol) do PHP
//Create TCP/IP sream socket
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

//reuseable port
socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);

//socket_bind() recebe como parâmetro o resource do socket que já foi criado com o socket_create(), o endereço e opcionalmente a porta. Isto tem que ser feito antes que uma conexão seja estabelecida, usando socket_connect() ou socket_listen().
//bind socket to specified host
socket_bind($socket, 0, $port);

//após o socket ter sido criado usando socket_create e associado para um nome com socket_bind() , ele deve aguardar conexões que irão entrar com o socket_listen().
//listen to port
socket_listen($socket);

//create & add listning socket to the list
$clients = array($socket);

//start endless loop, so that our script doesn't stop
while (true) {
	//manage multipal connections
	$changed = $clients;
	//returns the socket resources in $changed array
	socket_select($changed, $null, $null, 0, 10);
	
	//check for new socket
	if (in_array($socket, $changed)) {
		//accpet new socket
		$socket_new = socket_accept($socket); 
		//add socket to client array
		$clients[] = $socket_new; 
		
		//read data sent by the socket
		$header = socket_read($socket_new, 1024); 
		//perform websocket handshake
		new_client($header, $socket_new, $host, $port, $socketpath, $magickey); 
		
		//get ip address of connected socket
		socket_getpeername($socket_new, $ip); 
		//prepare json data
		$response = message_encode(json_encode(array('type'=>'system', 'message'=>$ip.' conectado')));
		//notify all users about new connection 
		send_message($response); 
		
		//make room for new socket
		$found_socket = array_search($socket, $changed);
		unset($changed[$found_socket]);
	}
	
	//loop through all connected sockets
	foreach ($changed as $changed_socket) {	
		
		//check for any incomming data
		while(socket_recv($changed_socket, $buf, 1024, 0) >= 1)
		{
			//unmessage data
			$received_text = unmessage_encode($buf); 
			//json decode 
			$tst_msg = json_decode($received_text);
			//sender name 
			$user_mode = (empty($tst_msg->mode) ? null : $tst_msg->mode); 
			//sender name
			$user_name = (empty($tst_msg->name) ? null : $tst_msg->name); 
			//message text
			$user_message = (empty($tst_msg->message) ? null : $tst_msg->message); 
			//message date
			$user_date = (empty($tst_msg->udate) ? null : $tst_msg->udate); 
			//color
			$user_color = (empty($tst_msg->color) ? null : $tst_msg->color); 
			
			//prepare data to be sent to client
			$response_text = message_encode(json_encode(array('type'=>$user_mode, 'name'=>$user_name, 'message'=>$user_message, 'date'=>$user_date, 'color'=>$user_color)));
			//send data
			send_message($response_text); 
			//exist this loop
			break 2; 
		}
		
		$buf = @socket_read($changed_socket, 1024, PHP_NORMAL_READ);
		//check disconnected client
		if ($buf === false) { 
			//remove client for $clients array
			$found_socket = array_search($changed_socket, $clients);
			socket_getpeername($changed_socket, $ip);
			unset($clients[$found_socket]);
			
			//notify all users about disconnected connection
			$response = message_encode(json_encode(array('type'=>'system', 'message'=>$ip.' disconnected')));
			send_message($response);
		}
	}
}
//close the listening socket
socket_close($socket);

function send_message($msg)
{
	global $clients;
	foreach($clients as $changed_socket)
	{
		@socket_write($changed_socket,$msg,strlen($msg));
	}
	return true;
}


//Unmessage incoming framed message
function unmessage_encode($text) {
	$length = ord($text[1]) & 127;
	if($length == 126) {
		$messages = substr($text, 4, 4);
		$data = substr($text, 8);
	}
	elseif($length == 127) {
		$messages = substr($text, 10, 4);
		$data = substr($text, 14);
	}
	else {
		$messages = substr($text, 2, 4);
		$data = substr($text, 6);
	}
	$text = "";
	for ($i = 0; $i < strlen($data); ++$i) {
		$text .= $data[$i] ^ $messages[$i%4];
	}
	return $text;
}

//encode message for transfer to client.
function message_encode($text)
{
	$b1 = 0x80 | (0x1 & 0x0f);
	$length = strlen($text);
	
	if($length <= 125)
		$header = pack('CC', $b1, $length);
	elseif($length > 125 && $length < 65536)
		$header = pack('CCn', $b1, 126, $length);
	elseif($length >= 65536)
		$header = pack('CCNN', $b1, 127, $length);
	return $header.$text;
}

//cada cliente tem que se apresentar, enviando uma solicitação WebSocket handshake para estabelecer uma conexão bem sucedida com o servidor, o pedido contém um Sec-WebSocket-Key que é uma chave em base64 de 16 bytes.
//handshake new client.
function new_client($receved_header,$client_conn, $host, $port, $socketpath, $magickey)
{
	$headers = array();
	$lines = preg_split("/\r\n/", $receved_header);
	foreach($lines as $line)
	{
		$line = chop($line);
		if(preg_match('/\A(\S+): (.*)\z/', $line, $matches))
		{
			$headers[$matches[1]] = $matches[2];
		}
	}

	$secKey = $headers['Sec-WebSocket-Key'];
	$secAccept = base64_encode(pack('H*', sha1($secKey . $magickey)));
	$upgrade  = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
	"Upgrade: websocket\r\n" .
	"Connection: Upgrade\r\n" .
	"WebSocket-Origin: $host\r\n" .
	"WebSocket-Location: ws://$host:$port/$socketpath\r\n".
	"Sec-WebSocket-Accept:$secAccept\r\n\r\n";
	socket_write($client_conn,$upgrade,strlen($upgrade));
}
