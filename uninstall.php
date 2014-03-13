<?php

//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
	exit();

$option_name = 'odocs_update_sortorder_meta';

/* Delete all custom terms for passed taxonomy, and the custom term meta options both on Single site and Multisite.
*/
function delete_custom_terms($taxonomy){
	global $wpdb;
	if ( is_multisite() ) { 

		$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
		foreach ( $blog_ids as $blog_id ) {

			switch_to_blog( $blog_id );

			$query = 'SELECT t.name, t.term_id
				FROM ' . $wpdb->terms . ' AS t
				INNER JOIN ' . $wpdb->term_taxonomy . ' AS tt
				ON t.term_id = tt.term_id
				WHERE tt.taxonomy = "' . $taxonomy . '"';
		
			$terms = $wpdb->get_results($query);

			foreach ($terms as $term) {
		
				$t_id = $term->term_id;
		
				wp_delete_term( $t_id, $taxonomy );
		
				delete_option( "taxonomy_$t_id" );
			}


		} // end foreach blog_id


	} else {

		// not Multisite

		$query = 'SELECT t.name, t.term_id
				FROM ' . $wpdb->terms . ' AS t
				INNER JOIN ' . $wpdb->term_taxonomy . ' AS tt
				ON t.term_id = tt.term_id
				WHERE tt.taxonomy = "' . $taxonomy . '"';
		
		$terms = $wpdb->get_results($query);

		foreach ($terms as $term) {
	
			$t_id = $term->term_id;
	
			wp_delete_term( $t_id, $taxonomy );
	
			delete_option( "taxonomy_$t_id" );
		}

	}

}

// Delete all custom terms for this taxonomy, and the custom term meta options
delete_custom_terms('isa_docs_category');

// For Single site
if ( !is_multisite() ) {

	delete_option( $option_name );

	// delete Docs posts
	$args = array(	'post_type' => 'isa_docs', 
				'posts_per_page' => -1,
	);
	$all_docs = get_posts( $args );
	foreach ($all_docs as $doc) {
		wp_delete_post($doc->ID, true);
	}
} 
// For Multisite
else {

	global $wpdb;
	$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
	$original_blog_id = get_current_blog_id();
	foreach ( $blog_ids as $blog_id ) {
		switch_to_blog( $blog_id );

		delete_option( $option_name );
	
		// delete Docs posts
		$args = array(	'post_type' => 'isa_docs', 
					'posts_per_page' => -1,
		);
		$all_docs = get_posts( $args );
		foreach ($all_docs as $doc) {
			wp_delete_post($doc->ID, true);
		}

	}
}