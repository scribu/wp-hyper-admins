<?php
/*
Plugin Name: Hyper Admins
Description: Show all sites in admin bar; show all themes in theme page. Only for super-admins.
Network: true
Author: scribu
Plugin URI: http://wordpress.org/extend/plugins/hyper-admins/
Version: 1.1
*/

if ( ! is_multisite() ) {
	return;
}

add_filter( 'option_allowedthemes', 'all_the_themes' );
add_action( 'admin_bar_init', 'all_the_sites' );

function all_the_sites() {
	if ( ! is_super_admin() ) {
		return;
	}

	// Get all blog ids
	global $wpdb;

	$blog_ids = $wpdb->get_col( $wpdb->prepare( "
		SELECT blog_id
		FROM {$wpdb->blogs}
		WHERE site_id = %d
		AND spam = '0'
		AND deleted = '0'
		AND archived = '0'
		ORDER BY registered DESC
	", $wpdb->siteid ) );

	// Populate blogs array
	$blogs = array();

	foreach ( $blog_ids as $blog_id ) {
		$blog_id = (int) $blog_id;

		$blog = get_blog_details( $blog_id );
		$blogs[ $blog_id ] = (object) array(
			'userblog_id' => $blog_id,
			'blogname'    => $blog->blogname,
			'domain'      => $blog->domain,
			'path'        => $blog->path,
			'site_id'     => $blog->site_id,
			'siteurl'     => $blog->siteurl,
		);
	}

	// Send to admin bar object
	$GLOBALS['wp_admin_bar']->user->blogs = $blogs;
}


// All sites have access to all themes
function all_the_themes( $themes ) {
	if ( ! is_super_admin() ) {
		return $themes;
	}

	$allowed = array();
	foreach ( wp_get_themes() as $theme ) {
		$allowed[ $theme->get_stylesheet() ] = true;
	}

	return $allowed;
}

