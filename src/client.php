<?php
	class Client {
		public $ip, $hostname, $host, $socket, $nick = false, $user = false, $realname = false, $registered = false, $channels = Array();
		
		public static function find($socket) {
			global $clients;
			foreach($clients as &$client) {
				if($client->socket == $socket)
					return $client;
			}
			return false;
		}
		
		public static function find_by_nick($nick) {
			global $clients;
			foreach($clients as &$client) {
				if($client->nick == $nick && $client->registered)
					return $client;
			}
			return false;
		}
		
		public static function valid_nick($nick) {
			// TODO: Make this configurable or at least RIGHT
			return preg_match('/^[a-z][a-z0-9\^\[\]]+$/i', $nick);
		}
		
		public static function whois($client, $who) {
			/*
				>> :moon.n0v4.com 311 savetheinternet femanon femanon boxxy.babee * :femanon
				>> :moon.n0v4.com 379 savetheinternet femanon :is using modes +iwrxt 
				>> :moon.n0v4.com 307 savetheinternet femanon :is a registered nick
				>> :moon.n0v4.com 319 savetheinternet femanon :@#lobby 
				>> :moon.n0v4.com 312 savetheinternet femanon pluto.n0v4.com :Still a planet!
				>> :moon.n0v4.com 318 savetheinternet femanon :End of /WHOIS list.
			*/
			
			// Username and real name
			$client->write(IRC::sprintf(IRC::Whois, 311, $client, "{$who->user} {$who->host} * :{$who->realname}"));
			
			$client->write(IRC::sprintf(IRC::EndOfWhois, $client, $who->nick));
		}
		
		// New client/connection
		public function __construct($socket) {
			$this->socket = $socket;
			
			// Find IP address
			socket_getpeername($this->socket, $this->ip);
			$this->lookup();
			
			Log::write(Log::Debug, "New connection: {$this->ip}.");
		}
		
		// Try and use a hostname
		private function lookup() {
			Log::write(Log::Debug, "Resolving hostname for {$this->ip}.");
			$this->write(IRC::sprintf(IRC::LookingUpHostname));
			
			$this->host = $this->hostname = @gethostbyaddr($this->ip);
			
			// Check if it actually resolved
			if($this->hostname == $this->ip) {
				// It didn't
				Log::write(Log::Debug, "Couldn't find a hostname for {$this->ip}. Using IP address instead.");
				$this->write(IRC::sprintf(IRC::FoundHostname));
			} else {
				Log::write(Log::Debug, "Found hostname for {$this->ip}: {$this->hostname}");
				$this->write(IRC::sprintf(IRC::CouldntFindHostname));
			}
		}
		
		// Handle a command (line) from a client
		public function command($line) {
			Log::write(Log::Debug, "{$this->ip}: {$line}");
			
			if(preg_match('/^((\S+ )*):(.+)$/', $line, $match)) {
				// Extended command with a colon'd message
				$argv = array_merge(
					explode(' ', substr($match[1], 0, -1)),
					isset($match[3]) ? Array($match[3]) : Array()
				);
			} elseif(!$argv = @explode(' ', trim($line))) { // Simple command
				// TODO: Throw some sort of error (invalid command)
				return false;
			}
			
			$argc = count($argv) -1;

			// Ignore case of first argument of command
			$argv[0] = strtoupper($argv[0]);
			
			// Look for the command
			if(($command = Command::find($argv[0])) === false) {
				// Unknown command
				$this->write(IRC::sprintf(IRC::UnknownCommand, $this, $argv[0]));
			} else {
				
				if($command->need_to_be_registered && !$this->registered) {
					// Not registered, and needs to be
					$this->write(IRC::sprintf(IRC::NotRegistered, $this, $argv[0]));
				} else if(	(!is_array($command->count) && $argc != $command->count) ||
							(is_array($command->count) && !in_array($argc, $command->count))) { // Check argument count
					// Wrong parameter count
					$this->write(IRC::sprintf(IRC::NotEnoughParameters, $this, $argv[0]));
				} else {
					// We don't need to worry about the first argument (command name) anymore
					$argv = array_slice($argv, 1);
					
					// Command callback
					$command->call(&$this, $argv);
				}
			}
		}
		
		// Send the client a command (automatically appends CLRF)
		public function write($line) {
			// TODO: Broken pipe quit messages
			socket_write($this->socket, "{$line}\r\n");
		}
		
		// Change nick
		public function nick($nick) {
			// TODO: Announce it.
			$this->nick = $nick;
		}
		
		public function mask($realhost = false) {
			return $this->nick . '!' . $this->user . '@' . ($realhost ? $this->hostname : $this->host);
		}
		
		// When registered
		public function init() {
			if($this->nick === false || $this->user === false)
				return false;
			
			$this->registered = true;
			
			$this->write(IRC::sprintf(IRC::Connect(), &$this));
			
			return true;
		}
		
	};
?>
