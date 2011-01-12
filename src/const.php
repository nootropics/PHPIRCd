<?php
	define('VERSION', '0.0a');
	
	class IRC {
		const
		
		LookingUpHostname				= ":%server% NOTICE AUTH :*** Looking up your hostname...",
		
		FoundHostname 					= ":%server% NOTICE AUTH :*** Found your hostname.",
		CouldntFindHostname 			= ":%server% NOTICE AUTH :*** Couldn't find your hostname. Using IP address instead.",
		UnknownCommand					= ":%server% 421 %nick% %s :Unknown command.",
		NotEnoughParameters				= ":%server% 461 %nick% %s :Not enough parameters.",
		ErroneousNickname				= ":%server% 432 %nick% %s :Erroneous Nickname.",
		NicknameAlreadyInUse			= ":%server% 433 %nick% %s :Nickname is already in use.",
		NotRegistered					= ":%server% 451 %nick% %s :You have not registered.",
		
		NoSuchNick						= ":%server% 401 %nick% %s :No such nick.",
		NoSuchServer					= ":%server% 402 %nick% %s :No such server.",
		NoSuchChannel					= ":%server% 403 %nick% %s :No such channel.",
		
		Join							= ":%mask% JOIN %s",
		Part							= ":%mast% PART %s :%s"
		
		;
		// Sent upon registration (NICK and USER combination)
		
		public static function Connect() {
		
							// Don't end with \r\n!!!
			return			":%server% 001 %nick% :Welcome to the %network% IRC Network %realmask%\r\n" .
							":%server% 002 %nick% :Your host is %server%, running PHPIRCd %version%, PHP %php%"
							;
							
		}
		
		// Escapes % with %% for use in sprintf
		public static function escape($string) {
			return str_replace('%', '%%', $string);
		}
		
		public static function sprintf() {
			global $config;
			
			// Get function arguments
			$args = func_get_args();
			if(!is_array($args) || empty($args)) {
				Log::write(Log::Error, "IRC::sprintf() was called incorrectly.");
				return false;
			}
			
			// Extra arguments (to be given to vsprintf)
			$extargs = Array();
			
			// Itterate through each argument
			foreach($args as &$arg) {
				if(!isset($format)) {
					// Initial format string to work with
					$format = $arg;
					continue;
				}
				
				// Check if it's a class
				if(is_object($arg)) {
					if(get_class($arg) == 'Client') {
						$client = $arg;
						continue;
					}
				}
				
				// Nothing special; add it to the $extargs
				$extargs[] = $arg;
			}
			
			// Add config and general stuff
			$format = str_replace('%server%',	self::escape($config['hostname']),	$format);
			$format = str_replace('%network%',	self::escape($config['network']),	$format);
			$format = str_replace('%version%',	self::escape(VERSION),				$format);
			$format = str_replace('%php%',		self::escape(phpversion()),			$format);
			
			if(isset($client)) {
				// Replace some more stuff
				
				// TODO: Replace %ip%, etc, for the hell of it.
				
				if($client->nick === false) {
					$format = str_replace('%nick%', '*', $format);
				} else {
					$format = str_replace('%nick%', self::escape($client->nick), $format);
				}
				
				if($client->registered) {
					$format = str_replace('%mask%', self::escape($client->mask()), $format);
					$format = str_replace('%realmask%', self::escape($client->mask(true)), $format);
				}
			}
			
			
			// Make the string using vsprintf
			if(!empty($extargs))
				return vsprintf($format, $extargs);
			// Nothing more to do.
			return $format;
		}
	};
	
?>
