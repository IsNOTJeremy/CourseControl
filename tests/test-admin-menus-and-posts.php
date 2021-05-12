<?php
/**
 * Class SampleTest
 *
 * @package CourseControl
 */

/**
 * This should test out the generation of the menu and the custom post types
 */
class AdminStuffTests extends WP_UnitTestCase {
	// apparently this helps make sure that the whole testing environment doesn't explode and cause me a headache.
	public function setUp() {
		unregister_post_type('cc_level_0');
		unregister_post_type('cc_level_1');
		unregister_post_type('cc_level_2');
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
	public function test_CC_admin_menu() {
		parent::setUp();
		CC_admin_menu();
		$this->assertNotEmpty( menu_page_url( 'CC-main-menu' ));
		parent::tearDown();
	}
	
	// later add to this to test if the posts were properly added as sub items to the menu page CC-main-menu.
	public function test_CC_create_post_types() {
		parent::setUp();
		parent::tearDown();
		CC_create_post_types();
		$this->assertTrue( post_type_exists('cc_level_0'));
		$this->assertTrue( post_type_exists('cc_level_1'));
		$this->assertTrue( post_type_exists('cc_level_2'));
	}
}
