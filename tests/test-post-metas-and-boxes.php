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
	 
	 /** 
	  * this test tests if post metas are added to our custom post types upon generation. IE, a child post has an available parent
	  */
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
		parent::tearDown();
	}
	
	/**
	 * Nothing seems to be able to force add_meta_box() wordpress function to fire. Have tried go_to() and set_current_screen()... At least we can see that the create and edit post pages are indeed admin required... /4/27/21
	 * well, we can call the connect_parents_box_gen function directly, and it *seems* to give what we expect. Not sure how to assert a metabox exists though. /4/27/21
	 * requires a rework to properly test!
	 */
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
		//console_log(get_the_title($test_module_id));
		$this->go_to('/wp-admin/post.php?post={$test_module_id}&action=edit');
		set_current_screen('/wp-admin/post.php?post={$test_module_id}&action=edit');
		//console_log(get_current_screen());
		CC_add_post_meta_boxes(get_post($test_module_id));
		CC_connect_parents_box_gen(get_post($test_module_id));
		//CC_add_post_meta_boxes();
		//$this->assertFalse(is_home());
		$this->assertTrue(is_admin());
		//$this->assertNotEmpty( $wp_meta_boxes[ 'cc_level_0_6' ] );
		parent::tearDown();
	}
	
	/**
	 * test that making a post our parent conversly makes this post a child for that parent
	 * THIS SHOULD BE SPLIT INTO 3 DIFFERENT TESTS: Test that we have the right available parent added to the child. Test that we can change the child value to yes. And test that making that change adds it to the parent.
	 */
	public function test_that_form_to_add_a_parent_adds_metas_to_child_and_parent_upon_saving(){
	parent::setUp();
	// creating the testing posts, a course parent and a module child
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
	// setting the current screen to being the edit page of the child post
	$this->go_to('/wp-admin/post.php?post={$test_module_id}&action=edit');
	set_current_screen('/wp-admin/post.php?post={$test_module_id}&action=edit');
	// executing the save metas functions
	CC_save_metas($test_course_id);
	CC_save_metas($test_module_id);
	// executing the add meta boxes function and the connect parents box function
	CC_add_post_meta_boxes(get_post($test_module_id));
	CC_connect_parents_box_gen(get_post($test_module_id));
	
	// creating a variable to match the key of our parent meta
	$key = 'cc_level_0_' . $test_course_id;
	// our initial assertions. At this point, our module should have a single meta field, matching our key, which contains the value 'no' and our course should have no metas.
	$this->assertCount(1, get_post_meta($test_module_id));
	$this->assertContains($key, array_keys(get_post_meta($test_module_id)));
	$this->assertContains('no', array_keys(get_post_meta($test_module_id)[$key]));
	$this->assertEmpty(get_post_meta($test_course_id));
	$_post[$key] = 'yes';
	?>
	<form>
	<input type="checkbox" name=<?= "{$key}" ?> id=<?= "{$key}" ?> value='yes'
	</form>
	<?php
	$_POST[$key] = 'yes';
	// in order for the function to execute properly, $_POST must have our key be set.
	$this->assertTrue(isset($_POST[$key]));
	// runing saving functions again to ensure that everything is triggered
	CC_save_metas($test_module_id);
	CC_save_metas($test_course_id);
    wp_reset_postdata();
    // Asserting that everything ran right. At this point we should have 1 meta field in module, with the value 'yes' and 1 metafield in course with a value matching a key for our module.
	$this->assertCount(1, get_post_meta($test_module_id));
	$this->assertContains($key, array_keys(get_post_meta($test_module_id)));
	$this->assertContains('yes', array_keys(get_post_meta($test_module_id)[$key]));
	
	$Module_key = 'cc_level_1_' . $test_module_id;
	$this->assertContains($Module_key, array_keys(get_post_meta($test_course_id)));
	parent::tearDown();
	}
	
	/**
	 * This test should execute the display children function when no children exist. Expected result is a return informing us to create a child and add it.
	 */
	public function test_that_display_children_returns_empty_when_no_child_is_set(){
	parent::setUp();
	
	// creating the testing posts, a course parent and a module child
	CC_create_post_types();
	$test_course_post_parent = array(
		'post_title' => 'Test Course Title',
		'post_type' => 'cc_level_0'
		);
	$test_course_id = wp_insert_post( $test_course_post_parent );
	
			
	CC_display_children_box_gen(get_post($test_course_id));
	$saved = CC_display_children_box_gen(get_post($test_course_id));
	$this->assertEquals($saved, 'No child exist');
	parent::tearDown();
	}
	
	/**
	 * This test when executed should test what happens when a child does exist but is not assigned
	 */
	public function test_that_display_children_returns_information_when_child_exists_unset(){
	parent::setUp();
	
	// creating the testing posts, a course parent and a module child
	CC_create_post_types();
	$test_course_post_parent = array(
		'post_title' => 'Test Course Title',
		'post_type' => 'cc_level_0'
		);
	$test_course_id = wp_insert_post( $test_course_post_parent );
	$test_module_post_child = array(
		'post_title' => 'Test Module Title',
		'post_type' => 'cc_level_1'
		);
	$test_module_id = wp_insert_post( $test_module_post_child );
	
			
	CC_display_children_box_gen(get_post($test_course_id));
	$saved = CC_display_children_box_gen(get_post($test_course_id));
	$this->assertEquals($saved, 'No assigned child');
	parent::tearDown();
	}
	
	/**
	 * This test should see what happens when we have a child post attached to the parent
	 */
	public function test_that_display_children_returns_information_when_child_exists_is_set(){
	parent::setUp();
	
	// creating the testing posts, a course parent and a module child
	CC_create_post_types();
	$test_course_post_parent = array(
		'post_status' => 'draft',
		'post_title' => 'Test Course Title',
		'post_type' => 'cc_level_0'
		);
	$test_course_id = wp_insert_post( $test_course_post_parent );
	$test_module_post_child = array(
		'post_status' => 'draft',
		'post_title' => 'Test Module Title',
		'post_type' => 'cc_level_1'
		);
	$test_module_id = wp_insert_post( $test_module_post_child );
	
	
	$this->go_to('/wp-admin/post.php?post={$test_module_id}&action=edit');
	set_current_screen('/wp-admin/post.php?post={$test_module_id}&action=edit');
	// executing the add meta boxes function and the connect parents box function
	CC_add_post_meta_boxes(get_post($test_module_id));
	CC_connect_parents_box_gen(get_post($test_module_id));
	// creating a variable to match the key of our parent meta
	$key = 'cc_level_0_' . $test_course_id;
	$_post[$key] = 'yes';
	?>
	<form>
	<input type="checkbox" name=<?= "{$key}" ?> id=<?= "{$key}" ?> value='yes'
	</form>
	<?php
	$_POST[$key] = 'yes';
	// runing saving functions again to ensure that everything is triggered
	CC_save_metas($test_module_id);
	CC_save_metas($test_course_id);
    wp_reset_postdata();
	
	
	CC_display_children_box_gen(get_post($test_course_id));
	$saved = CC_display_children_box_gen(get_post($test_course_id));
	$this->assertEquals($saved, 'Has child');
	
	parent::tearDown();
	}
	
}
