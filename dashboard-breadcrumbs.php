<?php
/*
Plugin Name: Dashboard Breadcrumbs
Description: This plugin adds breadcrumbs to your admin.
Author: Mike Hansen
Version: 1.0
Author URI: http://mikehansen.me
*/
function dbread_make_words( $words ) {
	$words = str_replace( array( '-', '_' ), ' ', $words );
	$words = ucwords( $words );
	return $words;
}

function dbread_make_parent( $parent ) {
	if ( false == strpos( $parent, '.' ) ) {
		$parent = 'admin.php?page=' . $parent;
	}
	return $parent;
}

function dbread_make_taxonomy( $tax, $post_type ) {
	$args = array(
		'taxonomy' => $tax,
	);
	if ( ! empty( $post_type) ) {
		$args['post_type'] = $post_type;
	}
	$args = http_build_query( $args );
	return 'edit-tags.php?' . $args;
}

function dbread_make_base( $base ) {
	$bases = explode( '-', $base );
	$base = array();
	foreach ( $bases as $b ) {
		if ( false === strpos( $b, '_' ) ) {
			$base[] = $b;
		}
	}
	$base = implode( ' ', $base );
	return dbread_make_words( $base );
}

function dbread_load_breadcrumbs() {
	$divider = apply_filters( 'dbread_divider', ' &rsaquo; ' );
	$screen = get_current_screen();
	if ( 'dashboard' != $screen->id ) {
		$breadcrumbs = array( '<a href="' . admin_url() . '">Dashboard</a>' );

		if ( '' != $screen->post_type ) {
			$breadcrumbs[] = '<a href="' . dbread_make_parent( $screen->parent_file ) . '">' . dbread_make_words( $screen->post_type ) . '</a>';
			if ( '' != $screen->action ) {
				$breadcrumbs[] = dbread_make_words( $screen->action );
			}
		}

		if ( '' != $screen->taxonomy ) {
			$breadcrumbs[] = '<a href="' . dbread_make_taxonomy( $screen->taxonomy, $screen->post_type ) . '">' . dbread_make_words( $screen->taxonomy ) . '</a>';
			if ( '' != $screen->action ) {
				$breadcrumbs[] = dbread_make_words( $screen->action );
			}
		}

		if ( '' == $screen->taxonomy && '' == $screen->post_type && basename( $_SERVER['PHP_SELF'] ) != $screen->parent_file ) {
			$breadcrumbs[] = '<a href="' . dbread_make_parent( $screen->parent_file ) . '">' . dbread_make_words( $screen->parent_base ) . '</a>';
		}

		if ( '' == $screen->action ) {
			$breadcrumbs[] = dbread_make_base( $screen->id );
		} else {
			$breadcrumbs[] = dbread_make_base( $screen->action );
		}

		$output = '';

		$breadcrumbs = array_filter( $breadcrumbs );

		foreach ( $breadcrumbs as $crumb ) {
			if ( empty( $output ) ) {
				$output = $crumb;
			} else {
				$output .= $divider . $crumb;
			}
		}
		echo apply_filters( 'dbread_before_output', '<div class="dbread-crumbs-wrap">' );
		echo apply_filters( 'dbread_output', $output );
		echo apply_filters( 'dbread_after_output', '</div>' );
	}
}
add_action( 'admin_notices', 'dbread_load_breadcrumbs', -5 );

function dbread_styles() {
	$screen = get_current_screen();

	if ( 'dashboard' != $screen->id ) {
		?>
		<style type="text/css">
		.dbread-crumbs-wrap{
			padding-top: 10px;
			display: inline-block;
		}
		</style>
		<?php
	}
}
add_action( 'admin_head', 'dbread_styles' );