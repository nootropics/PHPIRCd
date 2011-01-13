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
		
		// $AllowMultiple: Allow multiple modes for some things (with unique values)
		public static $AllowMultiple;
		public $modes;
		
		public function __construct() {
			$this->AllowMultiple =		self::Ban .
										self::BanException .
										self::HalfOp .
										self::InviteException .
										self::Operator .
										self::Owner .
										self::Voice;
			$this->modes = Array();
		}
		
		public function find($mode, $create_if_not_exists = false) {
			foreach($this->modes as $key => $m) {
				if($m[0] == $mode)
					return $key;
			}
			
			if($create_if_not_exists) {
				$this->modes[] = Array($mode, false);
				return $this->find($mode);
			}
			
			return false;
		}
		
		public function find_with_arg($mode, $argument, $create_if_not_exists = false) {
			foreach($this->modes as $key => $m) {
				if($m[0] == $mode && $m[1] == $argument)
					return $key;
			}
			
			if($create_if_not_exists) {
				$this->modes[] = Array($mode, $agument);
				return $this->find_with_arg($mode, $agument);
			}
			
			return false;
		}
		
		public function has($mode) {
			return $this->find($mode) !== false;
		}
		
		public function mode($mode) {
			$args = explode(' ', $mode);
			
			$chars = str_split($args[0]);
			
			// Whether we're currently at a + or - index
			$plus = false;
			
			$argIndex = -1;			
			foreach($char as &$char) {
				// TODO: Check if it is a known char mode
				
				if($char == '-')
					$plus = false;
				elseif($char == '+')
					$plus = true;
				else
				
				
				// Multiple of the same mode FOR SPECIFIC MODES ONLY (+b, +v, etc)
				if(strstr(self::AllowMultiple, $char) !== false) {
					// Allow multiples of the same mode name
					
					// Find the argument
					$arg = &$args[$argIndex++];
					
					// Find or create the mode
					if($key = $this->find_with_arg($char, $arg, true)) {
						// Done... Nothing more to do.
					}
					
				} else {
					// Only allow one of this mode
					// Find the mode or create it
					if($key = $this->find($char, true)) {
						// The value will be stored at $this->modes[$key][1]
						$this->modes[$key][1] = true;
					}
				}
			}
		}
		
		public function make_string() {
			$args = Array('');
			foreach($this->modes as &$mode) {
				$args[0] .= $mode[0];
				if($args[1] !== false) {
					// Add argument too
					$args[] = $mode[1];
				}
			}
			
			return implode(' ', $args);
		}
		
	};

?>
