<?php
	
	require 'const.php';
	require 'client.php';
	require 'modes.php';
	require 'channel.php';
	require 'command.php';
	require 'log.php';
	
	require 'mod_mem.php';
	
	// Sockets
	$sockets = Array(
		// Listening sockets
		'listen' => Array(),
		// Client sockets
		'clients' => Array()
	);
	
	// Client classes
	$clients = Array();	
	// Channels classes
	$channels = Array();
	
	$config = Array(
		'listen' =>
			Array(
				Array(
					'ip' => 	'127.0.0.1',
					'port' =>	'6667'
				),
				Array(
					'ip' => 	'127.0.0.1',
					'port' =>	'6668'
				)
			),
		'hostname'	=> 'localhost',
		'network'	=> 'localhost'
	);
	
	if(!isset($config['listen']) || empty($config['listen'])) {
		Log::write(Log::Error, 'You must define at least one listen block.');
		exit(1);
	}
	
	// Listen on each block
	foreach($config['listen'] as &$listen) {
		Log::write(Log::Debug, "Trying to listen on {$listen['ip']}:{$listen['port']}");
		
		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if(!@socket_bind($socket, $listen['ip'], $listen['port'])) {
			Log::write(Log::Error, "Couldn't bind to {$listen['ip']}:{$listen['port']}! " . socket_strerror(socket_last_error()));
			exit(1);
		}
		
		if(!@socket_listen($socket)) {
			Log::write(Log::Error, "Couldn't listen on {$listen['ip']}:{$listen['port']}! " . socket_strerror(socket_last_error()));
			exit(1);
		}
		
		// Append to list of sockets to poll
		$sockets['listen'][] = $socket;
	}
	
	// Handle a shutdown
	function shutdown() {
		global $sockets;
		Log::write(Log::Info, 'Server going down...');
		
		// Close ALL sockets
		foreach($sockets['listen'] as &$socket) {
			socket_close($socket);
		}
		
		foreach($sockets['clients'] as &$socket) {
			// TODO: Maybe announce that the server is going down
			socket_close($socket);
		}
		
		// Exit with no error
		//exit(0);
	}
	
	// Add callback
	register_shutdown_function('shutdown');
	
	// Ignore abort
	//ignore_user_abort(true);
	
	$write = Array();
	$except = Array();
	
	while(true) {
		// Read from the listening sockets and the clients
		// We want to make a duplicate array in-case socket_select() decides to modify something (which it does)
		$read = array_merge($sockets['listen'], $sockets['clients']);
		
		if(@socket_select($read, $write, $except, 0) === false) {
			// Something failed
			Log::write(Log::Error, "Couldn't poll sockets! " . socket_strerror(socket_last_error()));
		}
		
		// Itterate through sockets with data
		foreach($read as &$socket) {
			if(in_array($socket, $sockets['listen'])) {
				// This is a listen socket
				
				// Accept the connection
				$client = socket_accept($socket);
				// Append to poll, create client and append client to the $clients array
				$clients[] = new Client($sockets['clients'][] = $client);
				//
				// = new Client(&$socket);
			} else {
				$data = @socket_read($socket, 1024, PHP_NORMAL_READ);
				if($data === false) {
					// Connection closed
					
					// TODO: Handle this.
				} else {
					// New data!
					
					$data = trim($data, "\r\n");
					
					if(empty($data))
						continue; // Useless to us.
					
					if(($client = Client::find($socket)) === false) {
						// We received data from a client that was disconnected... What?
						// If coded right, this SHOULD NEVER HAPPEN
						// TODO
					} else {
						// Send client the command
						$client->command($data);
					}
					
				}
			}
		}
		
		// Comment out this line if you want to use a lot of CPU
		usleep(100);
	}
?>

