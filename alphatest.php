<?php
/**
 * Trigger this file on Plugin uninstall
 *
 * @package ALPHATESTINGPHP
 */

/*
* Plugin Name: ALPHATESTINGPHP
* Plugin URI: http://www.jeffreyberglund.com
* Description: this is a purly testing based thing
* Version: none
* Author: derp
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
    $this->create_child_type();
    $this->create_parent_type();
    // flush rewrite rules
    flush_rewrite_rules();
}

function deactivate(){
    // flush rewrite rules
    flush_rewrite_rules();
}


function create_child_type() {
    register_post_type( 'testing_post',
        array(
            'labels' => array(
                'name' => __( 'Test Children' ),
                'singular_name' => __( 'Test Post' )
            ),
            'public' => true,
            'has_archive' => true,
            'supports' => array( 'title', 'editor', 'custom-fields' )
        )
    );
}
add_action( 'init', 'create_child_type' );

function create_parent_type() {
    register_post_type( 'testing_post_parent',
        array(
            'labels' => array(
                'name' => __( 'Test Parents' ),
                'singular_name' => __( 'Test Post Parent' )
            ),
            'public' => true,
            'has_archive' => true,
            'supports' => array( 'title', 'editor', 'custom-fields' )
        )
    );
}
add_action( 'init', 'create_parent_type' );

/**
 * Adds a meta box to children editing screen and one to parents
 */

function test_meta_box() {
    add_meta_box( 'prfx_meta', 'Parents', 'connected_parents_box_gen', 'testing_post', 'side', 'high' );
    add_meta_box( 'prfx_meta', 'These are my kids', 'display_children_box_gen', 'testing_post_parent', 'side', 'high' );
}
add_action( 'add_meta_boxes', 'test_meta_box' );


// this dumps the contents of this post type
// avar_dump((wp_count_posts( 'testing_post_prent')))
/** This was the code to create a single checkbox with name parents
 * <label for="parents">
 *             <input type="checkbox" name="parents" id="parents" value="yes" <?php if ( isset ( $prfx_stored_meta['parents'] ) ) checked( $prfx_stored_meta['parents'][0], 'yes' ); ?> />
 *             <?php _e( 'Parents', 'prfx-textdomain' )?>
 *             </label>
 */

function console_log($output, $with_script_tags = true) {
    $js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) . 
');';
    if ($with_script_tags) {
        $js_code = '<script>' . $js_code . '</script>';
    }
    echo $js_code;
}

/**
 * Add checkboxes and a unique meta field for each possible parent to be connected to the children
 */

function connected_parents_box_gen( $post ) {
    wp_nonce_field( basename( __FILE__ ), 'prfx_nonce' );
    $prfx_stored_meta = get_post_meta( $post->ID );
    $currentPost = $post->ID;

    if ( ( wp_count_posts( 'testing_post_parent')->publish > 0 ) || ( wp_count_posts( 'testing_post_parent')->draft > 0 ) || 
    ( wp_count_posts( 'testing_post_parent')->pending > 0 ) ) {
        // arguments
        $args = array( 'post_type' => 'testing_post_parent' );
        // the query
        $query = new WP_Query( $args );

        // The Loop
        ?>
            <p>
                <span class="prfx-row-title"><?php _e(  'This is a list of all parents', 'prfx-textdomain' )?></span>
            </p>
       <?php
            // Itterating over all parent type posts
            while ( $query->have_posts() ) {
                $query->the_post();
                $parent_id = "parent_" . get_the_ID();
        ?>
                <div class="parent-option-div">
                    <label for= <?= "{$parent_id}" ?>>
                        <input type="checkbox" name=<?= "{$parent_id}" ?> id=<?= "{$parent_id}" ?>   
                            <?php
                                if ( isset ( $prfx_stored_meta[$parent_id] ) ){
                                    checked( $prfx_stored_meta[$parent_id][0], 'yes' );
                                }
                            ?> 
                        />
                        <?php 
                            _e( get_the_title() . " (" . $parent_id . ")", 'prfx-textdomain' );
                            echo "  " . $prfx_stored_meta[$parent_id][0];
                        ?>
                    </label>
                </div>
            <?php
            }
        /* Restore original Post Data */
        wp_reset_postdata();
    }
    else {  
    ?>
        <p>
            <span class="prfx-row-title"><?php _e( 'Create a parent to assign this child to', 'prfx-textdomain' )?></span>
        </p>   
    <?php
    }
}

/**
 * Add a list of all children of a parent on its page
 */

function display_children_box_gen( $post ) {
    wp_nonce_field( basename( __FILE__ ), 'prfx_nonce' );
    $prfx_stored_meta = get_post_meta( $post->ID );
    $currentPost = $post->ID;

    if ( ( wp_count_posts( 'testing_post')->publish > 0 ) || ( wp_count_posts( 'testing_post')->draft > 0 ) || 
    ( wp_count_posts( 'testing_post')->pending > 0 ) ) {
        echo "There are kids to have!";
        ?>
            <p>
            </p>
        <?php
        $postmetas = get_post_meta(get_the_ID());
        foreach($postmetas as $meta_key=>$meta_value){
            $startString = "child_";
            if(/*true || */startsWith($meta_key, $startString)){
                echo $meta_key . ' : ' . $meta_value[0] . '<br/>';
            }
        }
    }
    else {  
        ?>
            <p>
                <span class="prfx-row-title"><?php _e( 'Create a child and assign it to me', 'prfx-textdomain' )?></span>
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
 * Add a column to children that allows sorting by the related parents
 */
add_filter( 'manage_testing_post_posts_columns', 'parents_column' );
function parents_column( $columns ) {
    $columns['parentsList'] = __( 'Parents', 'textFill' );
    $custom_col_order = array(
        'cb' => $columns['cb'],
        'title' => $columns['title'],
        'parentsList' => $columns['parentsList'],
        'date' => $columns['date']
    );
    return $custom_col_order;
}
add_action( 'manage_testing_post_posts_custom_column', 'parents_column_pop', 10, 2);
function parents_column_pop( $column, $post_id ) {
    if ( 'parentsList' === $column ) {
        
        $postmetas = get_post_meta(get_the_ID());
        $counter = 0;
        foreach($postmetas as $meta_key=>$meta_value){
            $startString = "parent_";
            if(startsWith($meta_key, $startString) && $meta_value[0] == 'yes'){
                if($counter > 0){
                    echo ', ';
                }
                $parent_id = ltrim($meta_key, $startString);
                $parent_title = get_the_title($parent_id);
                echo $parent_title;
                $counter = $counter + 1;
            }
        }
      }
}

/**
 * Make the columns able to be filtered by a parent
 */
function add_custom_tags($tags) {

    /** you can do a query to get these tags from database */
    $extra_tags = array(
        'tag1',
        'tag2',
        'tag3'
    );

    $tags = array_merge($extra_tags, $tags);
    return $tags;
}
add_filter('dropdown_tags', 'add_custom_tags');

/**
 * Saves the custom meta input
 */
function prfx_meta_save( $post_id ) {
    if(get_post_type($post_id == 'testing_post' )){
        // Checks save status - overcome autosave, etc.
        $is_autosave = wp_is_post_autosave( $post_id );
        $is_revision = wp_is_post_revision( $post_id );
        $is_valid_nonce = ( isset( $_POST[ 'prfx_nonce' ] ) && wp_verify_nonce( $_POST[ 'prfx_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';

        // Exits script depending on save status
        if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
            return;
        }

        // arguments
        $args = array( 'post_type' => 'testing_post_parent' );
        // the query
        $query = new WP_Query( $args );
        $child_id = $post_id;
        $child_key = "child_" . $post_id;
        $child_meta = get_post_meta( $child_id->ID );

        // The Loop over parents
        while ( $query->have_posts() ) {
            $query->the_post();
            $parent_id = get_the_ID();
            $parent_key = "parent_" . get_the_ID();
            // Checks for input and saves - save checked as yes and unchecked at no
            if( isset( $_POST[ $parent_key ] ) ) {
                update_post_meta( $child_id, $parent_key, 'yes' );
                update_post_meta( $parent_id, $child_key, get_permalink($child_id) );
            } else {
                update_post_meta( $child_id, $parent_key, 'no' );
                delete_metadata('post', $parent_id, $child_key, '', false );
            }
        }
        /* Restore original Post Data */
        wp_reset_postdata();
    }
}
add_action( 'save_post', 'prfx_meta_save' );

/**
 * Function to remove metadata upon deletion of a parent
 */

add_action('wp_trash_post', 'remove_deleted_parent_meta');
function remove_deleted_parent_meta( $post_id) {
    // arguments
    $args = array( 'post_type' => 'testing_post' );
    // the query
    $query = new WP_Query( $args );
    $del_parent = $post_id;

    // The Loop
    while ( $query->have_posts() ) {
        $query->the_post();
        delete_metadata('post', get_the_ID(), "parent_" . $del_parent, '', true );
    }
    /* Restore original Post Data */
    wp_reset_postdata();
}

add_action('init', 'CCremove');
function CCremove() {
    //delete_metadata('post', 111, "parent_111", '', true );
}