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
		// $AllowParameter: Allow a parameter for this mode (don't bother putting anything from $AllowMUltiple in here)
		public static $AllowMultiple, $AllowParameter;
		public $modes;
		
		public function __construct() {
			$this->AllowMultiple =		self::Admin .
										self::Ban .
										self::BanException .
										self::HalfOp .
										self::InviteException .
										self::Operator .
										self::Owner .
										self::Voice;
			
			$this->AllowParameter =		self::FloodProtection .
										self::JoinThrottle .
										self::Key .
										self::Limit .
										self::LimitRedirect;
			
			
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
				$this->modes[] = Array($mode, $argument);
				return $this->find_with_arg($mode, $argument);
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
			
			$argIndex = 1;			
			foreach($chars as &$char) {
				// TODO: Check if it is a known char mode
				
				if($char == '-')
					$plus = false;
				elseif($char == '+')
					$plus = true;
				else
				
				
				
				if(strstr($this->AllowParameter, $char) !== false) {
					// Only one node allowed, wiht a parameter
					
					// Find the argument TODO: null handling
					$arg = &$args[$argIndex++];
					
					if($char == self::Limit && (floor($arg) != $arg || round($arg) < 1)) {
						// Argument is supposed to be a non-zero integer, but it wasn't.
						continue;
					}
					
					// Find or create the mode
					if($key = $this->find($char, true)) {
						// Set the argument
						$this->modes[$key][1] = $arg;
					}
				} elseif(strstr($this->AllowMultiple, $char) !== false) {
					// Multiple of the same mode FOR SPECIFIC MODES ONLY (+b, +v, etc)
					// Allow multiples of the same mode name
					
					// Find the argument TODO: null handling
					$arg = &$args[$argIndex++];
					
					if($char == self::Admin || $char == self::Halfop || $char == self::Operator || $char == self::Owner || $char == self::Voice) {
						// Channel-user mode
						// Make sure the user is on the channel first
						// TODO: Remove these modes on part/etc
						
						if(($parent->find_by_nick($arg)) == false) {
							// Client is not on channel
							// TODO
							continue;
						}
					}
					
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
				if($mode[1] !== false) {
					// Add argument too
					$args[] = $mode[1];
				}
			}
			
			return implode(' ', $args);
		}
		
	};

?>
