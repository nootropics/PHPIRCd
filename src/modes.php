<?php
	
	// Channel modes class
	class Modes {
		const	OnlyAdminsMayJoin			= 'A',
				Admin						= 'a',
				Ban							= 'b',
				NoANSIColors				= 'c',
				NoCTCP						= 'C',
				BanException				= 'e',
				FloodProtection				= 'f',
				GRated						= 'G',
				HalfOp						= 'h',
				InviteOnly					= 'i',
				InviteException				= 'I',
				JoinThrottle				= 'j',
				NoKnock						= 'k',
				Key							= 'k',
				Limit						= 'l',
				LimitRedirect				= 'L',
				RegisteredNickToTalk		= 'M',
				Moderated					= 'm',
				NoNicknameChanges			= 'N',
				NoExternalMessages			= 'n',
				OnlyIRCopsMayJoin			= 'O',
				Operator					= 'o',
				IsPrivate					= 'p',
				Owner						= 'q',
				ULinedKickOnly				= 'Q', // Not sure about this one
				Registered					= 'r',
				OnlyRegisteredCanJoin		= 'R',
				StripColors					= 'S',
				Secret						= 's',
				OnlyChanopsCanSetTopic		= 't',
				NoNotices					= 'T',
				Auditorium					= 'u',
				NoInvite					= 'V',
				Voice						= 'v',
				OnlySSLClientsMayJoin		= 'z';
		
		public $array = Array();
		
		public function find($mode, $create_if_not_exists = false) {
			foreach($this->array as $key => $m) {
				if($m[0] == $mode)
					return $key;
			}
			
			if($create_if_not_exists) {
				$this->array[] = Array($mode, false);
				return $this->find($mode);
			}
			
			return false;
		}
		
		public function has($mode) {
			return $this->find($mode) !== false;
		}
		
		public function mode($mode) {
			$args = explode(' ', $mode);
			
			$chars = str_split($args[0]);
			
			foreach($char as &$char) {
				// TODO: Check if it is a known char mode
				
				// TODO: Multiple of the same mode FOR SPECIFIC MODES ONLY (+b, +v, etc)
				// Find the mode or create it
				if($key = $this->find($char, true)) {
					// The value will be stored at $this->array[$key][1]
					$this->array[$key][1] = true;
				}
			}
			
		}
	};

?>
