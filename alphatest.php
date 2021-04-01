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
}
function deactivate(){
    // flush rewrite rules
    flush_rewrite_rules();
}
// this is to ensure that the permalinks work on activation. There is probably a better way to implement this later
register_activation_hook( __FILE__, 'my_rewrite_flush' );
function my_rewrite_flush(){
    // these are supposed to ensure that the new post types are added I think
    create_child_type();
    create_parent_type();
    // flush rewrite rules
    flush_rewrite_rules();
}


/**
 * Create the custom post types
 */
function create_child_type() {
    // the custom post type name
    register_post_type( 'testing_post',
        array(
            'labels' => array(
                // the displayed name on the menu
                'name' => __( 'Test Children' ),
                'singular_name' => __( 'Test Post' )
            ),
            'public' => true,
            'has_archive' => true,
            // this ajusts the permalinks
            'rewrite'     => array( 'slug' => 'child' ),
            // the supports. These are all required.
            'supports' => array( 'title', 'editor', 'custom-fields' )
        )
    );
}
// Automatically calls this function upon itit
add_action( 'init', 'create_child_type' );

function create_parent_type() {
    // the custom post type name
    register_post_type( 'testing_post_parent',
        array(
            'labels' => array(
                // the displayed name on the menu
                'name' => __( 'Test Parents' ),
                'singular_name' => __( 'Test Post Parent' )
            ),
            'public' => true,
            'has_archive' => true,
            // this ajusts the permalinks
            'rewrite'     => array( 'slug' => 'parent' ),
            // the supports. These are all required.
            'supports' => array( 'title', 'editor', 'custom-fields' )
        )
    );
}
add_action( 'init', 'create_parent_type' );

/**
 * Adds a meta box to children editing screen and one to parents
 */
// function name
function test_meta_box() {
    // a name, the title in the box, the called function to populate the box, the post type the box appears on, the position on the screen, priority placement on the screen
    add_meta_box( 'prfx_meta', 'Parents', 'connected_parents_box_gen', 'testing_post', 'side', 'high' );
    add_meta_box( 'prfx_meta', 'These are my kids', 'display_children_box_gen', 'testing_post_parent', 'side', 'high' );
}
add_action( 'add_meta_boxes', 'test_meta_box' );

// this is code that can output messages to the console
function console_log($output, $with_script_tags = true) {
    $js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) . 
');';
    if ($with_script_tags) {
        $js_code = '<script>' . $js_code . '</script>';
    }
    echo $js_code;
}

/**
 * Add checkboxes and a unique meta field for every parent post type to each child post.
 */
// function name
function connected_parents_box_gen( $post ) {
    // creating a nonce for validation purposes
    wp_nonce_field( basename( __FILE__ ), 'prfx_nonce' );
    // getting the meta data of the current post
    $prfx_stored_meta = get_post_meta( $post->ID );
    // getting the id of the current post
    $currentPost = $post->ID;
    // checking if there are any existing parent type posts that are published, drafts, or pending
    if ( ( wp_count_posts( 'testing_post_parent')->publish > 0 ) || ( wp_count_posts( 'testing_post_parent')->draft > 0 ) || 
    ( wp_count_posts( 'testing_post_parent')->pending > 0 ) ) {
        // arguments for the query, specifically the post type that we will be querying, that of the parents.
        $args = array( 'post_type' => 'testing_post_parent' );
        // We are declaring the new query using the arguments
        $query = new WP_Query( $args );
        // this is a message before the list of parents and their checkboxes
        ?>
            <p>
                <span class="prfx-row-title"><?php _e(  'This is a list of all parents', 'prfx-textdomain' )?></span>
            </p>
       <?php
            // While loop to iterate over all parent type posts
            while ( $query->have_posts() ) {
                // aquireing a parent type post
                $query->the_post();
                // creating a key for it using the parent posts unique id
                $parent_key = "parent_" . get_the_ID();
                $parent_id = get_the_ID();
                // html code to create the checkboxes. The input type is a checkbox, the name of the field, and the id of the field. We then have an if condition to check if it has been set or been checked, and how to update accordingly and save.
        ?>
                <div class="parent-option-div">
                    <label for= <?= "{$parent_key}" ?>>
                        <input type="checkbox" name=<?= "{$parent_key}" ?> id=<?= "{$parent_key}" ?>   
                            <?php
                                if ( isset ( $prfx_stored_meta[$parent_key] ) ){
                                    checked( $prfx_stored_meta[$parent_key][0], 'yes' );
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
            <span class="prfx-row-title"><?php _e( 'Create a parent to assign this child to', 'prfx-textdomain' )?></span>
        </p>   
    <?php
    }
}

/**
 * Add a list of all children of a parent on its page
 * 
 * Features to add: 
 *      Ability to delete a child from its parent.
 *      A "copy hyper link" button to insert the hyper links into your webpage.
 */
// function name
function display_children_box_gen( $post ) {
    // create a nonce for valication purposes
    wp_nonce_field( basename( __FILE__ ), 'prfx_nonce' );
    // getting the id of the current post
    $currentPost = $post->ID;
    // testing if there are any  children that exist as being published, drafts, or pending, if not skip to else statement.
    if ( ( wp_count_posts( 'testing_post')->publish > 0 ) || ( wp_count_posts( 'testing_post')->draft > 0 ) || 
    ( wp_count_posts( 'testing_post')->pending > 0 ) ) {
        // getting the metadata of the current post
        $postmetas = get_post_meta(get_the_ID());
        // Loop through each one of the meta datas on the current post
        foreach($postmetas as $meta_key=>$meta_value){
            // the test for if this meta data field is one of the children by using the attached key at the start of the string
            $startString = "child_";
            if(startsWith($meta_key, $startString)){
                // the id of the found related child
                $childID = ltrim($meta_key, $startString);
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
 * Add a column to children that contains all of their assigned parents
 * 
 * Future features:
 *      Possibly make the list of the parents also be hyperlinks to the edit page for that parent
 */
// filter that call our custom function when manging the posts of child type posts
add_filter( 'manage_testing_post_posts_columns', 'parents_column' );
// function name. Including the columns of the post type we are on
function parents_column( $columns ) {
    // adding a column, id is parentsList, displayed name is Parents
    $columns['parentsList'] = __( 'Parents', 'textFill' );
    // changing the order of the displayed columns to make the added parents column be in the middle, before date
    $custom_col_order = array(
        'cb' => $columns['cb'],
        'title' => $columns['title'],
        'parentsList' => $columns['parentsList'],
        'date' => $columns['date']
    );
    // returning this changed order for the columns.
    return $custom_col_order;
}
// adding an action that will call our custom function when populating the columns of the child posts
add_action( 'manage_testing_post_posts_custom_column', 'parents_column_pop', 10, 2);
// function name. Including the columns of the post type we are on, and the post ID of the individual posts within the different rows.
function parents_column_pop( $column, $post_id ) {
    // testing to ensure that our custom parent column exists
    if ( 'parentsList' === $column ) {
        // getting the meta data of the post in a row
        $postmetas = get_post_meta(get_the_ID());
        // creating a counter
        $counter = 0;
        // iterating through each of the existing meta fields of the post
        foreach($postmetas as $meta_key=>$meta_value){
            // testing if the meta field is of a parent type
            $startString = "parent_";
            if(startsWith($meta_key, $startString) && $meta_value[0] == 'yes'){
                // if this is a second or later parent type post, add a comma and a space
                if($counter > 0){
                    echo ', ';
                }
                // getting the id of this parent
                $parent_id = ltrim($meta_key, $startString);
                // getting the title of the parent
                $parent_title = get_the_title($parent_id);
                // display the name of the parent
                echo $parent_title;
                // add one to the counter
                $counter = $counter + 1;
            }
        }
      }
}

/**
 * Make the custom parent column able to be filtered by a parent
 * 
 * Future changes:
 *      I think I can remove all instances of $current_parent as it does not seem to have any effect
 *      Add the ability to switch from one filter option to another without reseting the filters
 */
// function name
function filter_by_parents() {
    // setting arguments for the query. Argument is the post type being parent type posts
    $args = array( 'post_type' => 'testing_post_parent' );
    // creating a new query using the arguments
    $query = new WP_Query( $args );
    // creating an array to hold a list of the parents, it will be a 2d array, being an array of arrays
    $parent_list = array();
    // iterating through the parents and getting their information
    while ( $query->have_posts() ) {
        // getting the parent post
        $query->the_post();
        // getting the id of the parent post
        $parent_id = get_the_ID();
        // getting the title of the parent post
        $parent_name = get_the_title($parent_id);
        // creating an entry which contains the parent's name and ID in an array
        $entry = array($parent_name, $parent_id);
        // adding this entry array into the parent list array
        $parent_list[] = $entry;
    }
    // reseting the query after our use of it
    wp_reset_postdata();
    // utilizing the wordpress variables of $typenow to find the type of post we are currently viewing
    global $typenow;
    // testing if we are currently viewing the child posts
    if ( $typenow == 'testing_post' ) {
        // I think I can just remove everything related to $current_parent.
        $current_parent = '';
        if( isset( $_GET['slug'] ) ) {
            $current_parent = $_GET['slug']; // Check if option has been selected
        } 
        // this outputs and displays the parents that the child on that row has
        ?>
            <select name="slug" id="slug">
                <option value="all" <?php selected( 'all', $current_parent ); ?>><?php _e( 'All Parents', 'wisdom-plugin' ); ?></option>
                <?php foreach( $parent_list as $key=>$value ) { ?>
                <option value="<?php echo esc_attr( $value[1] ); ?>" <?php selected( $value[1], $current_parent ); ?>><?php echo esc_attr( $value[0] ); ?></option>
                <?php } ?>
            </select>
        <?php
    }
  }
// calling the function
add_action( 'restrict_manage_posts', 'filter_by_parents' );
  
/**
 * Allow to filter by a specific parent
 * 
 * Future Features:
 *      Add the ability to switch from one filter option to another without reseting the filter
 */
// function name
function sort_parents_by_slug( $query ) {
    global $pagenow;
    // setting the post type. If the current post type is set, then set the post type to current post type, if not then set to an empty string
    $post_type = isset( $_GET['post_type'] ) ? $_GET['post_type'] : '';
    // if we are an admin, we are currently on an edit.php page, the post type is child type posts, and we have set the filter slug to something other than all
    if ( is_admin() && $pagenow=='edit.php' && $post_type == 'testing_post' && isset( $_GET['slug'] ) && $_GET['slug'] !='all' ) {
        // the selected parent is the filter slug plus our parent_ key
        $selectedParent = 'parent_' . $_GET['slug'];
        // filter to only display children that have this parent metafield with a yes value
        $query->query_vars['meta_key'] = $selectedParent;
        $query->query_vars['meta_value'] = 'yes';
        $query->query_vars['meta_compare'] = '=';
    }
}
add_filter( 'parse_query', 'sort_parents_by_slug' );


/**
 * Save the custom meta input
 */
// function name. Including the current post id.
function prfx_meta_save( $post_id ) {
    // test if we are a child type post
    if(get_post_type($post_id) == 'testing_post' ){
        // Checks save status - overcome autosave, etc.
        $is_autosave = wp_is_post_autosave( $post_id );
        $is_revision = wp_is_post_revision( $post_id );
        $is_valid_nonce = ( isset( $_POST[ 'prfx_nonce' ] ) && wp_verify_nonce( $_POST[ 'prfx_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';

        // Exits script depending on save status
        if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
            return;
        }

        // arguments for a query. Arguments are post type being parent type posts
        $args = array( 'post_type' => 'testing_post_parent' );
        // the query
        $query = new WP_Query( $args );
        // the child id is the id of the currently being saved post
        $child_id = $post_id;
        // creating a key using our current child post's id
        $child_key = "child_" . $post_id;
        // getting the meta data of our current child post.
        // does this even do anything except throw an error? Commenting it out.
        //$child_meta = get_post_meta( $child_id->ID );

        // The Loop over parents
        while ( $query->have_posts() ) {
            $query->the_post();
            // getting the id of the parent post, and creating a key for it
            $parent_id = get_the_ID();
            $parent_key = "parent_" . get_the_ID();
            // Checks for input and saves - save checked as yes and unchecked at no.
            // If it is a yes, then add the child to the parent as meta data.
            // If no, ensure that the child is not related on the parent's side by deleting any entry with this child's key on the parent.
            if( isset( $_POST[ $parent_key ] ) ) {
                update_post_meta( $child_id, $parent_key, 'yes' );
                update_post_meta( $parent_id, $child_key, get_permalink($child_id) );
            } else {
                update_post_meta( $child_id, $parent_key, 'no' );
                delete_metadata('post', $parent_id, $child_key, '', false );
            }
        }
        // Restore original Post Data after using the query
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