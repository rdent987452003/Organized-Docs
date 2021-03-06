<?php
/**
 * Functions to be used for templating tasks.
 */

/**
 * Return the HTML for the last Updated date on single Docs.
 * @param string $loc location of the tag, whether above or below the article
 */
function odocs_updated_on( $loc ) {
	if ( get_option('od_show_updated_date') == $loc ) {
		$time_string = '<meta itemprop="dateModified" content="%1$s" />%2$s';
		$time_string = sprintf( $time_string,
			esc_attr( get_the_modified_date( 'c' ) ),
			get_the_modified_date()
		);
		return sprintf( '<p class="updated-on">%1$s %2$s</p>',
			__( 'Updated on', 'organized-docs' ),
			$time_string
		);
	}
}

/**
 * Get structured data markup
 * @return array $schema
 * @since 2.6
 */
function odocs_schema_markup() {
	$schema = array( 'type' => '', 'name' => '', 'body' => '', 'properties' => '' );
	if ( ! get_option('od_disable_microdata') ) {
		$schema['type'] = ' itemscope itemtype="http://schema.org/' . apply_filters( 'od_single_schema_type', 'TechArticle' ) . '"';
		$schema['name'] = ' itemprop="headline"';
		$schema['body'] = apply_filters( 'od_single_schema_itemprop_body', ' itemprop="articleBody"' );
		// datePublished
		$schema['properties'] .= apply_filters( 'od_single_schema_date', '<meta itemprop="datePublished" content="' . get_the_time('c') . '">' );
		// image
		if ( $img_id = get_post_thumbnail_id() ) {
			$image = wp_get_attachment_image_src( $img_id );
			$img_url = $image[0];
			$width = $image[1];
			$height = $image[2];
		} else {
			$img_url = apply_filters( 'od_schema_img', plugins_url( '/organized-docs.png', dirname( __FILE__ ) ) );
			$width = apply_filters( 'od_schema_img_width', '128' );
			$height = apply_filters( 'od_schema_img_height', '128' );
		}
		$schema['properties'] .= '<span itemprop="image" itemscope itemtype="https://schema.org/ImageObject"><meta itemprop="url" content="' . esc_attr( $img_url ) . '"><meta itemprop="width" content="' . esc_attr( $width ) . '"><meta itemprop="height" content="' . esc_attr( $height ) . '"></span>';
	} 
	return $schema;
}

/**
 * Display the schema item properties. Hooked from the single.php bottom.
 * @since 2.6
 */
add_action( 'organized_docs_single_after_content', 'odocs_add_schema_properties' );
function odocs_add_schema_properties() {
	echo odocs_schema_markup()['properties'];
}
/**
 * Allow the author to be displayed on single Docs with a filter.
 * @since 2.6
 */
add_action( 'organized_docs_single_after_content', 'odocs_maybe_add_author' );
function odocs_maybe_add_author() {
	global $post;
	if ( empty( $post ) ) {
		return;
	}
	if ( apply_filters( 'od_display_author', false ) ) {
		echo '<span class="od-author">By ' .
		esc_html( apply_filters( 'od_author_name', get_the_author_meta( 'display_name', $post->post_author ) ) ) .
		'</span>';
	}
}

/**
 * Get the custom template from theme, if set
 * @since 2.6
 */
function odocs_get_template_hierarchy( $template ) {
	$template = $template . '.php';

	// Check if a custom template exists in the theme folder, if not, load the plugin template file
	if ( $theme_file = locate_template( 'organized-docs/' . $template ) ) {
		$file = $theme_file;
	} else {
		$file = dirname( __FILE__ ) . '/templates/' . $template;
	}
	return $file;
}

add_action( 'organized_docs_single_top', 'organized_docs_load_single_top', 10 );
function organized_docs_load_single_top() {
	wp_enqueue_style('organized-docs');
	if ( ! get_option('od_hide_print_link') ) { ?>
		<p id="odd-print-button">
		<?php if ( ! get_option('od_hide_printer_icon') ) { ?>
				<span>&#9113; </span>
		<?php } ?>
		<a href="javascript:window.print()" class="button"><?php _e( 'Print', 'organized-docs' ); ?></a>
		</p>
	<?php }
}
/**
 * Insert "Updated" date before content, if enabled in settings.
 */
add_filter( 'the_content', 'odocs_insert_date_above_content' );
function odocs_insert_date_above_content( $content ) {
	// Check if we're inside the main query in a single Docs post.
	if ( is_singular( 'isa_docs' ) && is_main_query() ) {
		$content = odocs_updated_on( 'above' ) . $content;
	}
	return $content;
}

/**
 * Wrapper for get_posts to fetch Docs by terms
 */
function odocs_query_docs( $terms, $top = '' ) {
	// orderby custom option
	$single_sort_by = get_option('od_single_sort_by');
	$orderby_order = get_option('od_single_sort_order');
					
	if ( 'date' == $single_sort_by ) {
		$orderby = 'date';
	} elseif ( 'title - alphabetical' == $single_sort_by ) {
		$orderby = 'title';
	} else {
		$orderby = 'meta_value_num';
	}

	$args = array(	'post_type' => 'isa_docs', 
					'posts_per_page' => -1,
					'tax_query' => array(
							array(
								'taxonomy' => 'isa_docs_category',
								'field' => 'id',
								'terms' => $terms,
								'include_children' => ( ! $top )
							)
						),
					'orderby' => $orderby,
					'order' => $orderby_order
	);
	if ( 'meta_value_num' == $orderby ) {
		$args['meta_key'] = '_odocs_meta_sortorder_key';
	}

	return get_posts( $args );
}
