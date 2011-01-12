<?php
	new Command('mem', 0, 'cmd_mem');
	
	class mod_mem {
		const MemoryUsage = ":%server% NOTICE MEMORY :*** Using %s in memory...";
	}
	
	function format_bytes($size) {
		$units = array(' B', ' KB', ' MB', ' GB', ' TB');
		for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
		return sprintf("%0.2f", $size) . $units[$i];
	}

	function cmd_mem($client, $argv) {
		$mem = format_bytes(memory_get_usage(true));
		
		$client->write(IRC::sprintf(mod_mem::MemoryUsage, $mem));
	}
?>
