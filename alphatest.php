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
 * create custom taxonomies
 */
/*
function custom_taxonomy() {
 
	// Add new taxonomy, make it hierarchical like categories
	//first do the translations part for GUI
	  $labels = array(
		'name' => _x( 'Organization', 'taxonomy general name' ),
		'singular_name' => _x( 'Organization', 'taxonomy singular name' ),
		'search_items' =>  __( 'Search Organization' ),
		'all_items' => __( 'All Organization' ),
		'parent_item' => __( 'Parent Organization' ),
		'parent_item_colon' => __( 'Parent Organization:' ),
		'edit_item' => __( 'Edit Organization' ), 
		'update_item' => __( 'Update Organization' ),
		'add_new_item' => __( 'Add New Organization' ),
		'new_item_name' => __( 'New Organization Name' ),
		'menu_name' => __( 'Organization' ),
	  );    
	 
	// Now register the taxonomy
	 
	  register_taxonomy('Organization',array('testing_post', 'testing_post_parent'), array(
		'hierarchical' => true,
		'labels' => $labels,
		'show_ui' => true,
		'show_admin_column' => true,
		'query_var' => true,
		'rewrite' => array( 'slug' => 'Parents' ),
	  ));
}
*/
/**
 * This auto adds a category to posts
 */
/*
add_action( 'save_post', 'set_default_category' );
function set_default_category( $post_id ) {

    // Get the terms
    $terms = wp_get_post_terms( $post_id, 'your_custom_taxonomy');

    // Only set default if no terms are set yet
    if (!$terms) {
        // Assign the default category
        $default_term = get_term_by('slug', 'your_term_slug', 'your_custom_taxonomy');
        $taxonomy = 'your_custom_taxonomy';
        wp_set_post_terms( $post_id, $default_term, $taxonomy );
    }
}
*?

/**
 * Adds a meta box to the post editing screen
 */

function test_meta_box() {
    add_meta_box( 'prfx_meta', 'Parents', 'test_box_callback', 'testing_post', 'side', 'high' );
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

function test_box_callback( $post ) {
    wp_nonce_field( basename( __FILE__ ), 'prfx_nonce' );
    $prfx_stored_meta = get_post_meta( $post->ID );

    if ( ( wp_count_posts( 'testing_post_parent')->publish > 0 ) || ( wp_count_posts( 'testing_post_parent')->draft > 0 ) || 
    ( wp_count_posts( 'testing_post_parent')->pending > 0 ) ) {
        // arguments
        $args = array( 'post_type' => 'testing_post_parent' );
        // the query
        $query = new WP_Query( $args );

        // The Loop
        ?>
            <p>
                <span class="prfx-row-title"><?php _e(  'This should be a list of all parents', 'prfx-textdomain' )?></span>
            </p>
       <?php
            while ( $query->have_posts() ) {
                $query->the_post();
                $parent_id = "parent_" . get_the_ID();
                ?>
                    <div class="parent-option-div">
                        <label for= <?= "{$parent_id}" ?>>
                            <input type="checkbox" name=<?= "{$parent_id}" ?> id=<?= "{$parent_id}" ?> parents=<?= "{$parent_id}" ?>  <?php /*checked( true );*/ if ( isset ( $prfx_stored_meta['parents'] ) )  checked( $prfx_stored_meta['parents'][0], 'yes' ); ?> />
                            <?php _e( get_the_title() . " (" . $parent_id . ")", 'prfx-textdomain' )?>
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
 * Saves the custom meta input
 */
function prfx_meta_save( $post_id ) {

    // Checks save status - overcome autosave, etc.
    $is_autosave = wp_is_post_autosave( $post_id );
    $is_revision = wp_is_post_revision( $post_id );
    $is_valid_nonce = ( isset( $_POST[ 'prfx_nonce' ] ) && wp_verify_nonce( $_POST[ 'prfx_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';

    // Exits script depending on save status
    if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
        return;
    }

    // Checks for input and saves - save checked as yes and unchecked at no
    if( isset( $_POST[ 'parents' ] ) ) {
        update_post_meta( $post_id, 'parents', 'yes' );
    } else {
        update_post_meta( $post_id, 'parents', 'no' );
    }

}
add_action( 'save_post', 'prfx_meta_save' );