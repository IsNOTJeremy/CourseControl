<?php
/**
 * Trigger this file on Plugin uninstall
 *
 * @package TesterUniquePosts
 */

/*
* Plugin Name: Webcourse-Controller
* Plugin URI: http://www.jeffreyberglund.com
* Description: This plugin does some stuff with WordPress
* Version: 1.0.2
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

//hook into the init action and call create_book_taxonomies when it fires
add_action( 'init', 'create_assigned_course_hierarchical_taxonomy', 0 );
 
//create a custom taxonomy name it assigned course for your posts
 
function create_assigned_course_hierarchical_taxonomy() {
 
// Add new taxonomy, make it hierarchical like categories
//first do the translations part for GUI
 
  $labels = array(
    'name' => _x( 'Assigned Course', 'taxonomy general name' ),
    'singular_name' => _x( 'Assigned Course', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search Assigned Course' ),
    'all_items' => __( 'All Assigned Course' ),
    'parent_item' => __( 'Parent Assigned Course' ),
    'parent_item_colon' => __( 'Parent Assigned Course:' ),
    'edit_item' => __( 'Edit Assigned Course' ), 
    'update_item' => __( 'Update Assigned Course' ),
    'add_new_item' => __( 'Add New Assigned Course' ),
    'new_item_name' => __( 'New Assigned Course Name' ),
    'menu_name' => __( 'Assigned Course' ),
  );    
 
// Now register the taxonomy
 
  register_taxonomy('assigned course',array('topic', 'module'), array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => false,
    'show_admin_column' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'assigned course' ),
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
			add_menu_page( 'Webcourse Menu', 'Webcourse Control Menu', 'manage_options', 
			'WCC-main-menu', 'WCC_complex_main', plugins_url( 'myplugin.png', __FILE__ ) );	
		}

		function WCC_complex_main(){}

		function WCC_add_category_taxonomy_to_post_types() {
			register_taxonomy_for_object_type( 'category', 'topic' );
			register_taxonomy_for_object_type( 'category', 'module' );
			register_taxonomy_for_object_type( 'category', 'course' );
		}
		add_action( 'init', 'WCC_add_category_taxonomy_to_post_types' );
		// register the post type for topics
		register_post_type( 
			'topic', 
			[
				'public' => true, 
				'label' => 'topics', 
				'taxonomies' => array( 'category' ), 
				'show_in_nav_menus' => true,
				'show_in_admin_bar' => true, 
				'show_in_menu' => 'WCC-main-menu'
			]
		);
		create_assigned_course_hierarchical_taxonomy();
		// register the post type for modules
		register_post_type( 
			'module', 
			[
				'public' => true, 
				'label' => 'modules', 
				'taxonomies' => array( 'category' ), 
				'show_in_nav_menus' => true,
				'show_in_admin_bar' => true, 
				'show_in_menu' => 'WCC-main-menu'
			]
		);
		// register the post type for courses
		register_post_type( 
			'course', 
			[
				'public' => true, 
				'label' => 'courses', 
				'taxonomies' => array( 'category' ), 
				'show_in_nav_menus' => true,
				'show_in_admin_bar' => true, 
				'show_in_menu' => 'WCC-main-menu'
			]
		);

		// sub menu for arranging the courses.
		function course_menu_page() {
			add_submenu_page( 'WCC-main-menu', 'Courses Options', 'Courses Options ', 'manage_options', 
			'courses_settings', 'arrange_course_settings'  );
			add_action( 'admin_init', 'register_course_settings' );
		}
		add_action('admin_menu', 'course_menu_page');
		// function to name the options that will be available in the settings
		function register_course_settings(){
			register_setting( 'courses-settings-group', 'Course Listing Order' );
		}
		// creating the html or whatever display stuff that appears when you go to the settings page. Currently a text box that can have text inserted and saved. Must convert to orderable list, that is also able to be saved.
		function arrange_course_settings(){
			?>
			<div class="wrap">
			<h1>Your Plugin Name</h1>
			
			<form method="post" action="options.php">
				<?php settings_fields( 'courses-settings-group' ); ?>
				<?php do_settings_sections( 'courses-settings-group' ); ?>
				<table class="form-table">
					<tr valign="top">
					<th scope="row">Course Listing Order</th>
					<td><input type="text" name="course_listing_order" value="<?php echo esc_attr( get_option('course_listing_order') ); ?>" /></td>
					</tr>
				</table>
				
				<?php submit_button(); ?>
			
			</form>
			</div>
			<?php
		}

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