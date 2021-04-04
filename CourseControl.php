<?php
/**
 * Trigger this file on Plugin uninstall
 *
 * @package ALPHATESTINGPHP
 */

/*
* Plugin Name: Course Control
* Plugin URI: http://www.jeffreyberglund.com
* Description: Add course page management
* Version: 1.5.5
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

function activate(){
    // generate
    $this->CC_admin_menu();
    $this->CC_create_post_types();
    // flush rewrite rules
    flush_rewrite_rules();
}
function deactivate(){
    // flush rewrite rules
    flush_rewrite_rules();
}
// this is to ensure that the permalinks work on activation. There is probably a better way to implement this later
register_activation_hook( __FILE__, 'my_rewrite_flush' );
function my_rewrite_flush(){
    // this is supposed to ensure that the new post types are added and navagible I think
    // flush rewrite rules
    CC_create_post_types();
    flush_rewrite_rules();
}

// this is code that can output messages to the console for debuging purposes
function console_log($output, $with_script_tags = true) {
    $js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) . 
    ');';
    if ($with_script_tags) {
        $js_code = '<script>' . $js_code . '</script>';
    }
    echo $js_code;
}

/**
 * Create a menu page
 */
function CC_admin_menu() {
    //create top level menu item
    add_menu_page( 'CourseControl', 'CourseControl', 'manage_options', 'CC-main-menu', 'CC_complex_main', plugins_url( 'CoursesIcon.png', __FILE__ ) );	
}
add_action( 'admin_menu', 'CC_admin_menu' );
/**
 * Populate the admin landing page
 */
function CC_complex_main(){
}

/**
 * Create the custom post types
 * You can add more levels if you want, by adding more register_post_types. Be sure to follow the same naming convention.
 */
function CC_create_post_types(){
    // highest level, was testing_post_parent, now known as cc_level_0, plays part of Course
    // the custom post type name
    register_post_type( 'cc_level_0',
        [
            'label' => 'Courses', 
            'public' => true, 
            'show_in_menu' => 'CC-main-menu', 
            'show_in_nav_menus' => true, 
            'show_in_admin_bar' => true, 
            // the supports. These are all required.
            'supports' => array( 'title', 'editor', 'custom-fields' ), 
            'has_archive' => true, 
            // this ajusts the permalinks
            'rewrite'     => array( 'slug' => 'courses' )
        ]
        /* This was added because I thought i had an error but there wasn't actually an error :/
        [
            'labels' => [
                // the displayed name on the menu
                'name' => __( 'Courses' ),
                'singular_name' => __( 'Course' )
            ],
            'public' => true, 
            'show_in_menu' => 'CC-main-menu', 
            'show_in_nav_menus' => true, 
            'show_in_admin_bar' => true,
            'has_archive' => true,
            // this ajusts the permalinks
            'rewrite'     => array( 'slug' => 'courses' ),
            // the supports. These are all required.
            'supports' => array( 'title', 'editor', 'custom-fields' )
        ]
            */
    );
    // Mid level,
    // was testing_post, now cc_level_1, plays part of Module
    register_post_type( 'cc_level_1',
        [
            'label' => 'Modules', 
            'public' => true, 
            'show_in_menu' => 'CC-main-menu', 
            'show_in_nav_menus' => true, 
            'show_in_admin_bar' => true, 
            // the supports. These are all required.
            'supports' => array( 'title', 'editor', 'custom-fields' ), 
            'has_archive' => true, 
            // this ajusts the permalinks
            'rewrite'     => array( 'slug' => 'modules' )
        ]
    );
    // Bottom level,
    // cc_level_2, plays part of Lesson
    register_post_type( 'cc_level_2',
        [
            'label' => 'Lessons', 
            'public' => true, 
            'show_in_menu' => 'CC-main-menu', 
            'show_in_nav_menus' => true, 
            'show_in_admin_bar' => true, 
            // the supports. These are all required.
            'supports' => array( 'title', 'editor', 'custom-fields' ), 
            'has_archive' => true, 
            // this ajusts the permalinks
            'rewrite'     => array( 'slug' => 'lessons' )
        ]
    );
}
add_action( 'init', 'CC_create_post_types' );

/**
 * Automatically add parent and child meta boxes to all CC post levels
 */
// function name
function CC_add_post_meta_boxs( $post ) {
    // iterate through all CC post types. If more are added, they will be iterated through as well.
    $post_level_number = 0;
    // Our loop for adding.
    do {
        // creating variables. Getting the numbers of what the parent and child of the current post number. And getting the post type of our current level.
        $post_level_parent = $post_level_number - 1;
        $post_level_child = $post_level_number + 1;
        $post_type = 'cc_level_' . $post_level_number;
        // Add parent checkbox metas. Only run if a parent type post exists
        if( post_type_exists( 'cc_level_' . $post_level_parent ) ){
            // making a variable for what will be the parent post type, and its label
            $parent_post_type = 'cc_level_' . $post_level_parent;            
            $parent_object_label = get_post_type_object($parent_post_type)->label;
            // Name of the box, the title in the box, the called function to populate the box, the post type the box appears on, the position on the screen, priority placement on the screen
            add_meta_box( 'CC_checklist_of_' . $parent_object_label, $parent_object_label, 'CC_connect_parents_box_gen', $post_type, 'side', 'high' );
        }
        // Add list of children metas. Only run if a child type post exists
        if( post_type_exists( 'cc_level_' . $post_level_child ) ){
            $child_post_type = 'cc_level_' . $post_level_child;            
            $child_object_label = get_post_type_object($child_post_type)->label;
            add_meta_box( 'CC_checklist_of_' . $child_object_label, $child_object_label, 'CC_display_children_box_gen', $post_type, 'side', 'high' );
        }
        $post_level_number += 1;
    } while( post_type_exists( 'cc_level_' . $post_level_number ) );
}
add_action( 'add_meta_boxes', 'CC_add_post_meta_boxs' );

/**
 * Add checkboxes and a unique meta field for every parent post type to each child post.
 */
// function name
function CC_connect_parents_box_gen( $post ) {    
    // create a nonce for valication purposes
    wp_nonce_field( basename( __FILE__ ), 'CC_nonce' );
    // getting the id of the current post
    $current_post_id = $post->ID;
    // getting the meta data of the current post
    $cc_stored_meta = get_post_meta( $post->ID );
    // getting the post type of the current post
    $current_post_type = get_post_type($current_post_id);
    // get the level of the current post
    $current_post_level = ltrim($current_post_type, "cc_level_");
    // get the level of the parent
    $parent_post_level = $current_post_level - 1;
    // get the type of the parent
    $parent_post_type = 'cc_level_' . $parent_post_level;
    // had to add this to make sure that the permalink and name of the post doesn't get changed weird
    if(get_the_title() != null){
        // checking if there are any existing parent type posts that are published, drafts, or pending
        if ( ( wp_count_posts( $parent_post_type )->publish > 0 ) || ( wp_count_posts( $parent_post_type )->draft > 0 ) || 
        ( wp_count_posts( $parent_post_type )->pending > 0 ) ) {
            // arguments for the query, specifically the post type that we will be querying, that of the parents.
            $args = array( 'post_type' => $parent_post_type );
            // We are declaring the new query using the arguments
            $query_parents = new WP_Query( $args );
            // this is a message before the list of parents and their checkboxes
            ?>
                <p>
                    <span class="CC_Parents"><?php _e(  'This is a list of all parents', 'cc-textdomain' )?></span>
                </p>
        <?php

            // While loop to iterate over all parent type posts
            while ( $query_parents->have_posts() ) {
                // aquireing a parent type post
                $query_parents->the_post();
                // creating a key for it using the parent posts unique id
                $parent_key = $parent_post_type . '_' . get_the_ID();
                $parent_id = get_the_ID();
                // html code to create the checkboxes. The input type is a checkbox, the name of the field, and the id of the field. We then have an if condition to check if it has been set or been checked, and how to update accordingly and save.
                ?>
                    <div class="parent-option-div">
                        <label for= <?= "{$parent_key}" ?>>
                            <input type="checkbox" name=<?= "{$parent_key}" ?> id=<?= "{$parent_key}" ?>   
                                <?php
                                    if ( isset ( $cc_stored_meta[$parent_key] ) ){
                                        checked( $cc_stored_meta[$parent_key][0], 'yes' );
                                    }
                                ?> 
                            />
                            <?php 
                                // outputting and attaching a title/name to a checkbox item
                                _e( get_the_title() . "\t" );            
                                // getting the edit and view links for the related parent
                                $parent_link = get_edit_post_link( $parent_id );
                                $parent_view = get_permalink( $parent_id );
                                // display link and edit
                                ?><a href= <?= $parent_link ?> >Edit</a><?php
                                echo "\t";
                                ?><a href= <?= $parent_view ?> >view</a><?php
                                // an echo that can output the current check condition of that item
                                //echo "  " . $prfx_stored_meta[$parent_id][0];
                            ?>
                        </label>
                    </div>
                <?php
            }
            // Restore original Post Data for the query loop
            wp_reset_postdata();

        }
        // else condition for if there are no existing parents. Output message to create a parent in order to begin assigning items.
        else {  
        ?>
            <p>
                <span class="CC_Parents_row"><?php _e( 'Create a parent to assign this child to', 'cc-textdomain' )?></span>
            </p>   
        <?php
        }
    }
    else {
        ?>
            <p>
                <span class="CC_Parents_row"><?php _e( 'Please add a title and save', 'cc-textdomain' )?></span>
            </p>   
        <?php
    }
    // this is a stupid fix, and i hate it. but the permalinks arent changing anymore
    // this stops the whileloop from changing the permalink to the last parent in the list
    $query = new WP_Query( $post );
    $query->the_post();
    wp_reset_postdata();
}
/**
 * Display all the children of a post in a metabox
 */
function CC_display_children_box_gen( $post ) {
    console_log(get_post_meta($post->ID));
    echo 'This is a list of children';
    ?>
        <p>
        </p>
    <?php
    
    // create a nonce for valication purposes
    wp_nonce_field( basename( __FILE__ ), 'CC_nonce' );
    // getting the id of the current post
    $current_post_id = $post->ID;
    // getting the post type of the current post
    $current_post_type = get_post_type($current_post_id);
    // get the level of the current post
    $current_post_level = ltrim($current_post_type, "cc_level_");
    // get the level of the child
    $child_post_level = $current_post_level + 1;
    // get the type of the child
    $child_post_type = 'cc_level_' . $child_post_level;
    //echo $child_post_type;
    // testing if there are any children that exist as being published, drafts, or pending, if not skip to else statement.
    if ( ( wp_count_posts( $child_post_type )->publish > 0 ) || ( wp_count_posts( $child_post_type )->draft > 0 ) || 
    ( wp_count_posts( $child_post_type )->pending > 0 ) ) {
        // getting the metadata of the current post
        $postmetas = get_post_meta(get_the_ID());
        // Loop through each one of the meta datas on the current post
        foreach($postmetas as $meta_key=>$meta_value){
            // the test for if this meta data field is one of the children by using the attached key at the start of the string
            $startString = $child_post_type . '_';
            if(startsWith($meta_key, $startString)){
                // the id of the found related child
                // using str_replace instead of ltrim, because ltrim caused bugs chopping off too much
                $childID = str_replace($startString, "", $meta_key);
                // the title of the found related child
                $childTitle = get_the_title($childID);
                // getting the edit and view links for the related child
                $child_link = get_edit_post_link( $childID );
                $child_view = get_permalink( $childID );
                // displaying the name of the child, as well as an edit and view link
                echo $childTitle . "\t";
                ?><a href= <?= $child_link ?> >Edit</a><?php
                echo "\t";
                ?><a href= <?= $child_view ?> >view</a><?php
                //'<br/>'
            }
            // a paragraph seperation between child entries
            ?>
                <p>
                </p>
            <?php
        }
    }
    // run if there are no children available to run through and display a message to create a child
    else {  
        ?>
            <p>
                <span class="cc-row-title"><?php _e( 'Create a child and assign it to me', 'cc-textdomain' )?></span>
            </p>   
       <?php
    }
}

// function to test if string starts with another string
function startsWith ($string, $startString) 
{ 
    $len = strlen($startString); 
    return (substr($string, 0, $len) === $startString); 
} 


/**
 * Save the custom meta input
 */
// function name. Including the current post id.
function CC_save_metas( $post_id ) {
    // getting the id of the current post
    $current_post_id = $post->ID;
    // getting the post type of the current post
    $current_post_type = get_post_type($current_post_id);
    $start_string = 'cc_level_';
    // make sure this is one of our post types
    if(startsWith($current_post_type, $start_string)){
        // getting the meta data of the current post
        $cc_stored_meta = get_post_meta( $post->ID );
        // get the level of the current post
        $current_post_level = ltrim($current_post_type, "cc_level_");
        // get the level of the parent
        $parent_post_level = $current_post_level - 1;
        // get the type of the parent
        $parent_post_type = 'cc_level_' . $parent_post_level;
        // test that the post type has existing parents
        if( post_type_exists( $parent_post_type ) ){
            // Checks save status - overcome autosave, etc.
            $is_autosave = wp_is_post_autosave( $post_id );
            $is_revision = wp_is_post_revision( $post_id );
            $is_valid_nonce = ( isset( $_POST[ 'cc_nonce' ] ) && wp_verify_nonce( $_POST[ 'cc_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';
            // Exits script depending on save status
            if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
                return;
            }
            // arguments for a query. Arguments are post type being parent type posts
            $args = array( 'post_type' => $parent_post_type );
            // the query
            $query = new WP_Query( $args );
            // the child id is the id of the currently being saved post
            $child_id_saving = $post_id;
            // creating a key using our current child post's id
            $child_key = $current_post_type . '_' . $post_id;
            // getting the meta data of our current child post
            // I don't think this is actually doing anything. Commenting out for now.
            //$child_meta = get_post_meta( $child_id_saving->ID );

            // The Loop over parents
            while ( $query->have_posts() ) {
                $query->the_post();
                // getting the id of the parent post, and creating a key for it
                $parent_id = get_the_ID();
                $parent_key = $parent_post_type . '_' . get_the_ID();
                // Checks for input and saves - save checked as yes and unchecked at no.
                // If it is a yes, then add the child to the parent as meta data.
                // If no, ensure that the child is not related on the parent's side by deleting any entry with this child's key on the parent.
                if( isset( $_POST[ $parent_key ] ) ) {
                    update_post_meta( $child_id_saving, $parent_key, 'yes' );
                    update_post_meta( $parent_id, $child_key, get_permalink($child_id_saving) );
                } else {
                    update_post_meta( $child_id_saving, $parent_key, 'no' );
                    delete_metadata('post', $parent_id, $child_key, '', false );
                }
            }
            // Restore original Post Data after using the query
            wp_reset_postdata();
        }
    }
}
add_action( 'save_post', 'CC_save_metas' );

?>