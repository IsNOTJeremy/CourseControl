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
		$this->assertEmpty( menu_page_url( 'CC-main-menu' ));
		CC_admin_menu();
		$this->assertNotEmpty( menu_page_url( 'CC-main-menu' ));
		parent::tearDown();
	}
	
	// later add to this to test if the posts were properly added as sub items to the menu page CC-main-menu.
	public function test_CC_create_post_types() {
		parent::setUp();
		
		$this->assertFalse( post_type_exists('cc_level_0'));
		$this->assertFalse( post_type_exists('cc_level_1'));
		$this->assertFalse( post_type_exists('cc_level_2'));
		
		CC_create_post_types();
		
		$this->assertTrue( post_type_exists('cc_level_0'));
		$this->assertTrue( post_type_exists('cc_level_1'));
		$this->assertTrue( post_type_exists('cc_level_2'));
		
		parent::tearDown();
	}
	
	/** Test that the activation function which calls the previously tested functions works
	* Reintegrate back into the other one when possible.
	* This is being caused by wordpress not properly removing menu items between tests. Pain in the ass. remove_menu_page doesn't actually work btw.
	* I don't have any solution. I tested it independent of the other at least, and it works... Hopefully I can find a solution somewhere.
	*/
	public function test_CC_activation() {
		parent::setUp();
		/*
		remove_menu_page('CC-main-menu');
		foreach($GLOBALS['menu'] as $mkey => $mval) {
			if(!in_array($mval[2], [
				'separator1',
				'separator2',
				'profile.php','edit.php?post_type=my_custom_post'
			])){
				unset($GLOBALS['menu'][$mkey]);
			}
		}
		remove_menu_page('CC-main-menu');
		*/
		//$this->assertEmpty( menu_page_url( 'CC-main-menu' ));
		$this->assertFalse( post_type_exists('cc_level_0'));
		$this->assertFalse( post_type_exists('cc_level_1'));
		$this->assertFalse( post_type_exists('cc_level_2'));
		
		activate_CourseControl();
		
		$this->assertNotEmpty( menu_page_url( 'CC-main-menu' ));
		$this->assertTrue( post_type_exists('cc_level_0'));
		$this->assertTrue( post_type_exists('cc_level_1'));
		$this->assertTrue( post_type_exists('cc_level_2'));
		
		parent::tearDown();
	}
}
