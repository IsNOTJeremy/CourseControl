<?php
/**
 * Class SampleTest
 *
 * @package TestPlugin
 */

/**
 * Sample test case.
 */
class AdminTest extends WP_UnitTestCase {

	public function setUp() {

			parent::setUp();

			wp_set_current_user( self::factory()->user->create( [
				'role' => 'administrator',
			] ) );
		}

		public function test_sample() {
			//$adminpages = new \AdminStuff;
			//$adminpages->test_sample();
			$this->assertEmpty( menu_page_url( 'T-menu' ) );
			add_menu_page('TestingMenu', 'TestingMenu', 'manage_options', 'T-menu', 'T_complex_main');
			
			$this->assertNotEmpty( menu_page_url( 'T-menu' ) );
		}
}
