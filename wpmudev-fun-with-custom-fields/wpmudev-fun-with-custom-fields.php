<?php 
/* Plugin Name: WPMU DEV Advanced Queries with Custom Fields
Plugin URI: 
Description: This plugin registers a post type called 'favorite' with three custom fields and then outputs all of these using a function.
Version: 1.0
Author: Rachel McCollin
Author URI: http://rachelmccollin.com
License: GPLv2
*/


/****************************************************************************************
wpmu_create_post_type -  register the post type
****************************************************************************************/
function wpmu_create_post_type() {
	$labels = array( 
		'name' => __( 'Favorite Things' ),
		'singular_name' => __( 'Favorite Thing' ),
		'add_new' => __( 'New Favorite Thing' ),
		'add_new_item' => __( 'Add New Favorite Thing' ),
		'edit_item' => __( 'Edit Favorite Thing' ),
		'new_item' => __( 'New Favorite Thing' ),
		'view_item' => __( 'View Favorite Things' ),
		'search_items' => __( 'Search Favorite Things' ),
		'not_found' =>  __( 'No Favorite Things Found' ),
		'not_found_in_trash' => __( 'No Favorite Things found in Trash' ),
	);
	$args = array(
		'labels' => $labels,
		'has_archive' => true,
		'public' => true,
		'hierarchical' => false,
		'supports' => array(
			'title', 
			'thumbnail',
			'page-attributes'
		),
	);
	register_post_type( 'favorite', $args );
} 
add_action( 'init', 'wpmu_create_post_type' );


/****************************************************************************************
wpmu_add_favorite_metabox -  create the metabox
****************************************************************************************/
function wpmu_add_favorite_metabox() {
	add_meta_box( 'wpmu_metabox_id', 'Why I Love It', 'wpmu_metabox_callback', 'favorite', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'wpmu_add_favorite_metabox' );

/****************************************************************************************
wpmu_metabox_callback -the callback function for the metabox contents 
****************************************************************************************/
function wpmu_metabox_callback( $post ) {
	
	echo '<form action="" method="post">';
		
		// add nonce for security
		wp_nonce_field( 'wpmu_metabox_nonce', 'wpmu_nonce' );
		
		//best field - fetch value if it exists
		$bestValue = get_post_meta( $post->ID, 'Best Thing', true );
		echo '<p>';
			echo '<label for "part1">Best thing about it: </label>';				
			echo '<p><textarea rows="3" cols="80" name="best" value=' . $bestValue . '>' . $bestValue . '</textarea></p>';
		echo '</p>';

		//worst field - fetch value if it exists
		$worstValue = get_post_meta( $post->ID, 'Downside', true );
		echo '<p>';
			echo '<label for "part2">Worst thing about it: </label>';				
			echo '<p><textarea rows="3" cols="80" name="worst" value=' . $worstValue . '>' . $worstValue . '</textarea></p>';
		echo '</p>';
		
	echo '</form>';
	
}

/****************************************************************************************
wpmu_save_my_meta -  save data from the metabox
****************************************************************************************/
function wpmu_save_my_meta( $post_id ) {

	//check for nonce
	if( !isset( $_POST['wpmu_nonce'] ) ||
	!wp_verify_nonce( $_POST['wpmu_nonce'], 'wpmu_metabox_nonce' ) ) {
	  return;
	}
	
	// Check if the current user has permission to edit the post.
	if ( !current_user_can( 'edit_post', $post->ID ) ) {
	  return;
	 }
	
	// best field - save data
	if ( isset( $_POST['best'] ) ) {		
		$new_value = ( $_POST['best'] );
		update_post_meta( $post_id, 'Best Thing', $new_value );		
	}
	
	// part 2 field - save data
	if ( isset( $_POST['worst'] ) ) {		
		$new_value = ( $_POST['worst'] );
		update_post_meta( $post_id, 'Downside', $new_value );		
	}

	
}
add_action( 'save_post', 'wpmu_save_my_meta' );

/****************************************************************************************
wpmu_output_favorite -  run a query and output the favorite things
****************************************************************************************/
function wpmu_output_favorite() {

	if ( is_page( '22' ) ) {
	
		// run the query and fetch the data - store it in an array of variables
		$args = array(
			'post_type' => 'favorite',
			'posts_per_page' => 3,
			'orderby' => 'rand'
		);
		$query = new WP_query ( $args );
		
		if ( $query->have_posts() ) {
		
			$currentpost = 0;
					
			while ( $query->have_posts() ) : $query->the_post();
			
			$favorite[$currentpost] = get_the_title();
			$best[$currentpost] = get_post_meta( get_the_ID(), 'Best Thing', true );
			$worst[$currentpost] = get_post_meta( get_the_ID(), 'Downside', true );
			
			$currentpost++;
			
			endwhile;
					
			wp_reset_postdata();
			
		}
		
		// output	
		echo '<section class="container favorite">';
		
			echo '<h3>My Favorite Things:</h3>';
			echo '<p>Today&apos;s Favorite Things are <b>' . $favorite[0] . '</b> plus <b>' . $favorite[1] . '</b> and a bit of <b>' . $favorite[2] . '</b> . The upsides are ' . $best[0] . ' and ' . $best[1] . ', but the downside is ' . $worst[2] . '.</p>';
		
		echo '</section>';
		
	}

}
add_action( 'blog_way_before_primary', 'wpmu_output_favorite' );

