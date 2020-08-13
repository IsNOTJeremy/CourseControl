<?php
/**
 * Trigger this file on Plugin uninstall
 *
 * @package WebCourseController
 */

/*
* Plugin Name: Webcourse
* Plugin URI: http://www.jeffreyberglund.com
* Description: This plugin does some stuff with WordPress
* Version: 1.0.3
* Author: Jeffrey Berglund
* Author URI: http://www.jeffreyberglund.com
* License: GPL2
*/

// Make sure plugin cannot be called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'What you egg!';
	exit;
}
defined( 'ABSPATH' ) or die( 'Go away.' );

//////////////////////////////////////////////////////////////////////////////////////
/////////////
//////////////////////////////////////////////////////////////////////////////////////

//hook into the init action and call create_courses_hierarchical_taxonomy when it fires
add_action( 'init', 'create_courses_hierarchical_taxonomy', 0 );
 
/**
 * Creating new taxonomies. Courses and Modules for applying to modules and lessons.
 *  */ 
function create_courses_hierarchical_taxonomy() {
 
	// Add new taxonomy, make it hierarchical like categories
	//first do the translations part for GUI
	  $labels = array(
		'name' => _x( 'Courses', 'taxonomy general name' ),
		'singular_name' => _x( 'Assigned Course', 'taxonomy singular name' ),
		'search_items' =>  __( 'Search Courses' ),
		'all_items' => __( 'All Courses' ),
		'parent_item' => __( 'Parent Courses' ),
		'parent_item_colon' => __( 'Parent Courses:' ),
		'edit_item' => __( 'Edit Assigned Courses' ), 
		'update_item' => __( 'Update Assigned Course' ),
		'add_new_item' => __( 'Add New Courses' ),
		'new_item_name' => __( 'New Course Name' ),
		'menu_name' => __( 'Courses' ),
	  );    
	 
	// Now register the taxonomy
	 
	  register_taxonomy('Courses',array('lesson', 'module'), array(
		'hierarchical' => true,
		'labels' => $labels,
		'show_ui' => true,
		'show_admin_column' => true,
		'query_var' => true,
		'rewrite' => array( 'slug' => 'Courses' ),
	  ));
	}

	function create_modules_hierarchical_taxonomy() {
 
		// Add new taxonomy, make it hierarchical like categories
		//first do the translations part for GUI
		 
		  $labels = array(
			'name' => _x( 'Modules', 'taxonomy general name' ),
			'singular_name' => _x( 'Assigned Module', 'taxonomy singular name' ),
			'search_items' =>  __( 'Search Modules' ),
			'all_items' => __( 'All Modules' ),
			'parent_item' => __( 'Parent Modules' ),
			'parent_item_colon' => __( 'Parent Modules:' ),
			'edit_item' => __( 'Edit Assigned Modules' ), 
			'update_item' => __( 'Update Assigned Module' ),
			'add_new_item' => __( 'Add New Modules' ),
			'new_item_name' => __( 'New Module Name' ),
			'menu_name' => __( 'Modules' ),
		  );    
		 
		// Now register the taxonomy
		 
		  register_taxonomy('Modules', array('lesson'), array(
			'hierarchical' => true,
			'labels' => $labels,
			'show_ui' => true,
			'show_admin_column' => true,
			'query_var' => true,
			'rewrite' => array( 'slug' => 'Module' ),
		  ));
		}

//////////////////////////////////////////////////////////////////////////////////////
/////////////
//////////////////////////////////////////////////////////////////////////////////////

class TesterUniquePost
{
	function __construct(){
		add_action( 'init', array( $this, 'custom_post_type' ) );
	}

	function activate(){
		// generated a CPT
		$this->custom_post_type();
		// flush rewrite rules
		flush_rewrite_rules();
	}

	function deactivate(){
		// flush rewrite rules
		flush_rewrite_rules();
	}

	function custom_post_type(){

		add_action( 'admin_menu', 'WCC_admin_menu' );
		function WCC_admin_menu() {
			//create top level menu item
			add_menu_page( 'Webcourse Menu', 'Webcourse', 'manage_options', 
			'WCC-main-menu', 'WCC_complex_main', plugins_url( 'CoursesIcon.png', __FILE__ ) );	
		}

		function WCC_complex_main(){}

		
		add_action( 'init', 'WCC_add_category_taxonomy_to_post_types' );
		// register the post type for Courses
		register_post_type( 
			'course', 
			[
				'public' => true, 
				'label' => 'Courses', 
				'taxonomies' => array( 0 ), 
				'show_in_nav_menus' => true,
				'show_in_admin_bar' => true, 
				'show_in_menu' => 'WCC-main-menu'
			]
		);
		create_courses_hierarchical_taxonomy();
		// register the post type for Modules
		register_post_type( 
			'module', 
			[
				'public' => true, 
				'label' => 'Modules', 
				'taxonomies' => array( 'Courses' ), 
				'show_in_nav_menus' => true,
				'show_in_admin_bar' => true, 
				'show_in_menu' => 'WCC-main-menu',
				'supports' => array( 'title', 'editor', 'custom-fields' )
			]
		);
		create_modules_hierarchical_taxonomy();
		// register the post type for lessons
		register_post_type( 
			'lesson', 
			[
				'public' => true, 
				'label' => 'Lessons', 
				'taxonomies' => array( 'Courses', 'Module' ), 
				'show_in_nav_menus' => true,
				'show_in_admin_bar' => true, 
				'show_in_menu' => 'WCC-main-menu'
			]
		);

		/**
		 * Remove the category box from edit post
		 */
		add_action( 'admin_menu', 'CC_remove_category_box' );
		function CC_remove_category_box()
		{
			remove_meta_box( 'categorydiv', 'course', 'side' );
			remove_meta_box( 'categorydiv', 'module', 'side' );
			remove_meta_box( 'categorydiv', 'lesson', 'side' );
		}

//////////////////////// adding options to add lessons to modules and modules to courses, etc.
/**
 * Possibly redundant code. May be better to instead try building stuff from ground up. Currently code from elsewhere to study and adapt. Must review.
 */
		/**
		 * Add option to Bulk Actions to add to Modules
		 */
		add_filter( 'bulk_actions-edit-lesson', 'CC_register_bulk_actions' );
		function CC_register_bulk_actions($bulk_actions) {
			$bulk_actions['Add to Modules'] = __( 'Add to Modules', 'add_to_modules');
			return $bulk_actions;
		}

		/**
		 * Adding what happens when you hit "apply"
		 */

		// add to bulk options
		add_filter( 'bulk_actions-edit-post', 'register_my_bulk_actions' );
		function register_my_bulk_actions($bulk_actions) {
		$bulk_actions['email_to_eric'] = __( 'Email to Eric', 'email_to_eric');
		return $bulk_actions;
		}
		// doing think upon activation
		add_filter( 'handle_bulk_actions-edit-post', 'my_bulk_action_handler', 10, 3 );
		function my_bulk_action_handler( $redirect_to, $doaction, $post_ids ) {
		if ( $doaction !== 'email_to_eric' ) {
			return $redirect_to;
		}
		foreach ( $post_ids as $post_id ) {
			wp_set_post_categories( $post_id, array( 6 ), true );
		}
		$redirect_to = add_query_arg( 'bulk_emailed_posts', count( $post_ids ), $redirect_to );
		return $redirect_to;
		}
		// notice of doing thing
		add_action( 'admin_notices', 'my_bulk_action_admin_notice' );
		function my_bulk_action_admin_notice() {
		  if ( ! empty( $_REQUEST['bulk_emailed_posts'] ) ) {
			$emailed_count = intval( $_REQUEST['bulk_emailed_posts'] );
			printf( '<div id="message " class="updated fade ">' .
			  _n( 'Emailed %s post to Eric.',
				'Emailed %s posts to Eric.',
				$emailed_count,
				'email_to_eric'
			  ) . '</div>', $emailed_count );
		  }
		}

		//////////////////////////////////////////
		// populating taxonomies with existing courses and modules.
		//////////////////////////////////////////
		function update_custom_terms( $post_id ) { _e( 'Some text to translate and display.', 'textdomain' );}
		add_action('save_post', 'notification_1');
		// display custom admin notice
		function shapeSpace_custom_admin_notice() { ?>
			
			<div class="notice notice-success is-dismissible">
				<p><?php _e('Congratulations, you did it!', 'shapeSpace'); ?></p>
			</div>
			
		<?php }
		add_action('admin_notices', 'shapeSpace_custom_admin_notice');


	}
/*
	function WCC_register_settings() {
		add_option( 'myplugin_option_name', 'This is my option value.');
		register_setting( 'myplugin_options_group', 'myplugin_option_name', 'myplugin_callback' );
	 }
	 add_action( 'admin_init', 'myplugin_register_settings' );
*/
}

if( class_exists( 'TesterUniquePost' ) ) {
	$testerUniquePost = new TesterUniquePost();
}


// activation
register_activation_hook( __File__, array( $testerUniquePost, 'activate' ) );

// deactivation
register_deactivation_hook( __File__, array( $testerUniquePost, 'deactivate' ) );

?>