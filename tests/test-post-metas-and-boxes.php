<?php
/**
 * Class SampleTest
 *
 * @package CourseControl
 */

/**
 * This should test out the generation of the menu and the custom post types
 */
class MetaBoxesTests extends WP_UnitTestCase {
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
	 // this test tests if post metas are added to our custom post types
	public function test_CC_can_add_post_metas_to_custom_posts() {
		parent::setUp();
		CC_create_post_types();
		$test_course_post_parent = array(
			'post_title' => 'Test Course Title',
			'post_type' => 'cc_level_0'
			);
		$test_module_post_child = array(
			'post_title' => 'Test Module Title',
			'post_type' => 'cc_level_1'
			);
		$test_course_id = wp_insert_post( $test_course_post_parent );
		$test_module_id = wp_insert_post( $test_module_post_child );
		$test_course = get_post($test_course_id);
		$test_module = get_post($test_module_id);
		set_current_screen('cc_level_1');
		CC_save_metas($test_course_id);
		CC_save_metas($test_module_id);
		$has_a_possible_parent = false;
		$test_child_post_metas = get_post_meta($test_module_id);
		foreach ($test_child_post_metas as $meta_key => $meta_value) {
			if(startsWith($meta_key, 'cc_level_')){
				$has_a_possible_parent = true;
			}
		}
		$this->assertTrue( $has_a_possible_parent );
		//$this->assertNotEmpty( $wp_meta_boxes[ 'CC_checklist_of_Modules' ] );
		parent::tearDown();
	}
	// Nothing seems to be able to force add_meta_box() wordpress function to fire. Have tried go_to() and set_current_screen()... At least we can see that the create and edit post pages are indeed admin required... /4/27/21
	// well, we can call the connect_parents_box_gen function directly, and it *seems* to give what we expect. Not sure how to assert a metabox exists though. /4/27/21
	public function test_CC_add_meta_boxes_to_custom_posts() {
		parent::setUp();
		CC_create_post_types();
		$test_course_post_parent = array(
			'post_title' => 'Test Course Title',
			'post_type' => 'cc_level_0'
			);
		$test_course_id = wp_insert_post( $test_course_post_parent );
		
		$test_module_id = $this->factory()->post->create([
			'post_status' => 'draft',
			'post_title' => 'Title',
			'post_type' => 'cc_level_1'
		]);
		console_log(get_the_title($test_module_id));
		$this->go_to('/wp-admin/post.php?post={$test_module_id}&action=edit');
		set_current_screen('/wp-admin/post.php?post={$test_module_id}&action=edit');
		console_log(get_current_screen());
		CC_add_post_meta_boxes(get_post($test_module_id));
		CC_connect_parents_box_gen(get_post($test_module_id));
		//CC_add_post_meta_boxes();
		//$this->assertFalse(is_home());
		$this->assertTrue(is_admin());
		//$this->assertNotEmpty( $wp_meta_boxes[ 'cc_level_0_6' ] );
		parent::tearDown();
	}
	// test that making a post our parent conversly makes this post a child for that parent
	public function test_CC_add_child_to_parent_by_saving_child(){
	parent::setUp();
	echo '
	hello world
	';
	
	CC_create_post_types();
	$test_course_post_parent = array(
		'post_title' => 'Test Course Title',
		'post_type' => 'cc_level_0'
		);
	$test_course_id = wp_insert_post( $test_course_post_parent );
	
	$test_module_id = $this->factory()->post->create([
		'post_status' => 'draft',
		'post_title' => 'Title',
		'post_type' => 'cc_level_1'
	]);
	$this->go_to('/wp-admin/post.php?post={$test_module_id}&action=edit');
	set_current_screen('/wp-admin/post.php?post={$test_module_id}&action=edit');
	CC_save_metas($test_course_id);
	CC_save_metas($test_module_id);
	CC_add_post_meta_boxes(get_post($test_module_id));
	CC_connect_parents_box_gen(get_post($test_module_id));
	$key = 'cc_level_0_' . $test_course_id;
	
	update_post_meta($test_module_id, 'cc_level_0_' . $test_course_id, 'yes');
	console_log(get_post_meta($test_module_id));
	//console_log($_POST[$key]);
	CC_save_metas($test_course_id);
    wp_reset_postdata();
	
	console_log(get_post_meta($test_module_id));
	console_log(get_post_meta($test_course_id));
	$this->assertTrue(false);
	parent::tearDown();
	}
}
