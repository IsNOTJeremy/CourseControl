<?php

/**
 * Plugin Name: TestPlug
 * Plugin URI: insert website
 * Description: Testing a test site capabilities
 * Version: yes
 * Author: Me
 * Author URI: None
 * Liscense: GPL2
 */

class AdminStuff {
	function test_sample() {
		add_menu_page('TestingMenu', 'TestingMenu', 'manage_options', 'T-menu', 'T_complex_main');
	}
}
