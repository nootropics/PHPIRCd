<?php
	class Command {
		public $command, $count, $callback;
		public static function find($name) {
			global $commands;
			
			// Look for the command
			foreach($commands as &$command) {
				if($command->command == $name) return $command;
			}
			
			// Command not found
			return false;
		}
		
		public function call($client, $argv) {
			$func = $this->callback;
			$func(&$client, $argv);
		}
		
		// Create command
		public function __construct($command, $count, $callback, $need_to_be_registered=true) {
			global $commands;
			$this->command = strtoupper($command);
			$this->count = $count;
			$this->callback = $callback;
			$this->need_to_be_registered = $need_to_be_registered;
			
			// Add command to list
			$commands[] = &$this;
		}
	};
	
	$commands = Array();
	
	//			command		params		function	need to be registered (=true)
	// Initial commands
	new Command('nick',		1,			'cmd_nick', false);
	new Command('user',		4,			'cmd_user', false);
	
	// General commands
	new Command('join',		Array(1,2),	'cmd_join');
	new Command('part',		Array(1,2),	'cmd_part');
	new Command('privmsg',	2,			'cmd_privmsg');
	new Command('whois',	1,			'cmd_whois');
	
	// Add any commands here. They must follow this format for arguments: ($client, $argv)
	
	function cmd_nick($client, $argv) {
		$nick = $argv[0];
		
		if(!Client::valid_nick($nick)) {
			$client->write(IRC::sprintf(IRC::ErroneousNickname, $client, $nick));
			return;
		}
		
		if(Client::find_by_nick($nick) === false) {
			// Nick doesn't exist; we can use it.
			$client->nick($nick);
			
			// Check if user is now registered
			if(!$client->registered)
				$client->init();
		} else {
			// Nickname already in use
			$client->write(IRC::sprintf(IRC::NicknameAlreadyInUse, $client, $nick));
		}
	}
	
	function cmd_user($client, $argv) {
		global $clients;
		$user = $argv[0];
		// arg 2 and 3 are ignored
		$realname = $argv[3];
		
		$client->user = $user;
		$client->realname = $realname;
		
		// Check if user is now registered
		if(!$client->registered)
			$client->init();
	}
	
	function cmd_join($client, $argv) {
		$name = $argv[0];
		
		// TODO: Follow the specification, stating the users can use commands
		// such as "JOIN #channel,#anotherchannel somekey", etc.
		
		if(!Channel::is_valid($name)) {
			// Invalid channel name
			$client->write(IRC::sprintf(IRC::NoSuchChannel, $name));
			return;
		}
		
		// Find the channel or create it.
		$channel = Channel::find($name, true);
		
		if($channel->find_client($client) !== false) {
			// User is already on the channel
			// Don't respond with anything
			return;
		}
		
		// TODO: Make sure the user CAN join it (+iklb, etc)
		
		$channel->join(&$client);
		
		// TODO
	}
	
	function cmd_part($client, $argv) {
		$name = $argv[0];
		$message = isset($argv[1]) ? $argv[1] : false;
		
		// TODO: Follow the specification, stating the users can use commands
		// such as "PART #channel,#anotherchannel", etc.
		
		// Find the channel
		if(($channel = Channel::find($name)) === false) {
			// Channel doesn't exist.
			$client->write(IRC::sprintf(IRC::NoSuchChannel, $name));
			return;
		}
		
		if($channel->find_client($client) === false) {
			// User is not on the channel
			$client->write(IRC::sprintf(IRC::NotOnChannel, $name));
			return;
		}
		
		$channel->part(&$client, $message);
	}
	
	function cmd_privmsg($client, $argv) {
		$name = $argv[0];
		$message = $argv[1];
		
		if(Channel::is_valid($name)) {
			// Valid channel name
			// meaning it starts with a #
			// Treat as a channel message
			if(($channel = Channel::find($name)) === false) {
				// Channel doesn't exist.
				break;
			}
			
			$channel->message(&$client, $message);
			return;
		}
		
		// Treat as a PM
		if(($user = Client::find($name)) === false) {
			// Couldn't find a channel or user under this name
			$client->write(IRC::sprintf(IRC::NoSuchNickChannel, $name));
		} else {
			// Found a user
			// This is a PM
			// TODO
		}
	}
	
	function cmd_whois($client, $argv) {
		$name = $argv[0];
		
		if(($user = Client::find($name)) === false) {
			// Couldn't find a user under this name
			$client->write(IRC::sprintf(IRC::NoSuchNick, $name));
		} else {
			// Found him.
			// Create a WHOIS request
			Client::WHOIS(&$client, &$user);
		}
	}
	
?>
