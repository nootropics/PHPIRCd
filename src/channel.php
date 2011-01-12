<?php
	class Channel {
		public $name, $clients = Array();
		
		public static function find($name, $create_if_not_exists = false) {
			global $channels;
			foreach($channels as &$channel) {
				if($channel->name == $name)
					return $channel;
			}
			
			// Couldn't find the channel
			if($create_if_not_exists) {
				// Create it.
				return $channels[] = new Channel($name);
			}
			return false;
		}
		
		public function find_client($_client) {
			$i = 0;
			foreach($this->clients as &$client) {
				if($client == $_client)
					return $i;
				$i++;
			}
			
			return false;
		}
		
		public static function is_valid($name) {
			// TODO
			return preg_match('/^#[a-z0-9#?!]+$/i', $name);
		}
		
		public function __construct($name) {
			$this->name = $name;
		}
		
		public function join($newclient) {
			// Recursion doesn't matter. It makes it a lot easier (and faster) to code.
			$this->clients[] = &$newclient;
			$newclient->channels[] = &$this;
			
			// Announce join
			// TODO: Mode +u stuff
			
			foreach($this->clients as &$client) {
				$client->write(IRC::sprintf(IRC::Join, $newclient, $this->name));
			}
		}
		
		public function part($oldclient, $message = false) {
			
			// Remove the client from the userlist
			unset($this->clients[$this->find_client($oldclient)]);
			
			// Announce part
			// TODO: Mode +u stuff
			
			foreach($this->clients as &$client) {
				$client->write(IRC::sprintf(IRC::Part, $oldclient, $this->name, $message === false ? '' : $message));
			}
		}
		
		
		
	};
?>
