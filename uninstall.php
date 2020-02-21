<?php 

/**
 * Trigger this file on Plugin uninstall
 *
 * @package TesterUniquePosts
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

// clear Database stored data
$topics = get_posts( array( 'post_type' => 'topic', 'numberposts' => -1 ) );

foreach( $topics as $topic ){
	wp_delete_post( $topic->ID, true );
}
$moduals = get_posts( array( 'post_type' => 'modual', 'numberposts' => -1 ) );

foreach( $moduals as $modual ){
	wp_delete_post( $topic->ID, true );
}