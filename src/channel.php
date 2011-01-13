<?php
	class Channel {
		public $name, $clients = Array(), $modes, $topic = Array('topic' => false, 'set' => false, 'time' => false), $created;
		
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
		
		public function find_client($client) {
			return array_search($client, $this->clients, true);
		}
		
		public static function is_valid($name) {
			// TODO
			return preg_match('/^#[a-z0-9#?!]+$/i', $name);
		}
		
		public function __construct($name) {
			$this->name = $name;
			$this->modes = new Modes();
			$this->created = time();
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
			// Announce part
			// TODO: Mode +u stuff
			
			foreach($this->clients as &$client) {
				$client->write(IRC::sprintf(IRC::Part, $oldclient, $this->name, $message === false ? '' : $message));
			}
			
			// Remove the client from the userlist
			unset($this->clients[$this->find_client($oldclient)]);
		}
		
		public function message($from, $text) {
			// TODO: No external messages flag: +n
			// TODO: Modes +mMbn
			
			// Announce the message
			foreach($this->clients as &$client) {
				if($client == $from)
					continue; // Don't send it to the person who sent it (or the IRC client will echo it twice)
				
				$client->write(IRC::sprintf(IRC::Message, $from, $this->name, $text));
			}
		}
		
	};
?>
