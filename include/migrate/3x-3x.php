<?php
function wpcr3_migrate_3x_3x(&$this2, $current_dbversion) {
	
	
	if ($this2->pro === false) {
		
		$this2->options['templates'] = $this2->default_options['templates'];
		update_option($this2->options_name, $this2->options);
	}
	
	
	if ($current_dbversion < 301) {
		
	}

	
	if ($current_dbversion < 302) {
		
	}
	
	return true;
}
?>