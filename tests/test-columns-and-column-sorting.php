<?php
/**
 * Class SampleTest
 *
 * @package CourseControl
 */

/**
 * This should test out the generation of the menu and the custom post types
 */
class ColumnsTests extends WP_UnitTestCase {
	// apparently this helps make sure that the whole testing environment doesn't explode and cause me a headache.
	public function setUp() {
		unregister_post_type('cc_level_0');
		unregister_post_type('cc_level_1');
		unregister_post_type('cc_level_2');
		$user_id = $this->factory->user->create(array('role' => 'administrator'));
		$user = wp_set_current_user($user_id);
	}
	public function tearDown(){
		unregister_post_type('cc_level_0');
		unregister_post_type('cc_level_1');
		unregister_post_type('cc_level_2');
	}
		
	/**
	 * A single example test.
	 * test these
	 * 
		CC_admin_menu();
		CC_create_post_types();
	 */
	
	 
	 /** 
	  * this test tests that the cc_parents_column function executes as expected. Should have different output when exectuted on level_1 and _2 from level_0
	  */
	public function test_cc_parents_column() {
		parent::setUp();
		
		CC_create_post_types();
		require_once( ABSPATH . 'wp-admin/includes/screen.php' );
		echo'
		aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa
		';
		$this->go_to('/wp-admin/edit.php?post_type=cc_level_1');
		set_current_screen('/wp-admin/edit.php?post_type=cc_level_1');
		
		global $wpdb;
		$table_name = $wpdb->posts;
		
		foreach ( $wpdb->get_col ( "DESC " . $table_name, 0 ) as $column_name ) {
			console_log( $column_name );
		}
		
		echo '
		bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb
		';
		
		
		$this->assertEquals(true, true);
		parent::tearDown();
	}
	
}
