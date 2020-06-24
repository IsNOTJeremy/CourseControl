<?php 

/**
 * Trigger this file on Plugin uninstall
 *
 * @package WebCourseController
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

// clear Database stored data
$topics = get_posts( array( 'post_type' => 'topic', 'numberposts' => -1 ) );
foreach( $topics as $topic ){
	wp_delete_post( $topic->ID, true );
}

$moduals = get_posts( array( 'post_type' => 'module', 'numberposts' => -1 ) );
foreach( $moduals as $module ){
	wp_delete_post( $topic->ID, true );
}

$courses = get_posts( array( 'post_type' => 'course', 'numberposts' => -1 ) );
foreach( $courses as $course ){
	wp_delete_post( $course->ID, true );
}
