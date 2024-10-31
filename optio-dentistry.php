<?php

/*
Plugin Name: Optio Dentistry
Plugin URI: http://www.optiopublishing.com
Description: Add Optio Dentistry patient education videos to your site.
Version: 2.1
Author: Optio Publishing Inc.
Author URI: http://www.optiopublishing.com
*/

define('OPTIO_SERVER_URL', 'https://www.optiopublishing.com');

/**
 * Define shortcodes
 */

add_shortcode( 'optio-library', 'optio_dentistry_video_library' );
add_shortcode( 'optio-video', 'optio_dentistry_video_player' );
add_shortcode( 'optio-thumbnail', 'optio_dentistry_thumbnail_link' );
add_shortcode( 'optio-lightbox', 'optio_dentistry_lightbox' );

/**
 * Configure JavaScript API
 */

function optio_dentistry_add_async( $tag, $handle ) {
	if ( 'optio-api' !== $handle ) return $tag;
  return str_replace( ' src', ' async defer src', $tag );
}

function optio_dentistry_scripts() {
	wp_register_script( 'optio-api', OPTIO_SERVER_URL . '/api/js', array(), '4.1' );
	wp_enqueue_script( 'optio-api' );
}

add_filter( 'script_loader_tag', 'optio_dentistry_add_async', 10, 2 );
add_action( 'init', 'optio_dentistry_scripts' );

/**
 * Embed video library
 */

function optio_dentistry_video_library( $attributes ) {
	$attributes = shortcode_atts(
		array(
			'id' => null,
			'language' => 'en',
			'filter' => null
		),
		$attributes
	);
	return optio_dentistry_render_control( 'video_library', $attributes );
}

/**
 * Embed stand-alone video player
 */

function optio_dentistry_video_player( $attributes ) {
	$attributes = shortcode_atts(
		array(
			'id' => null,
			'language' => 'en',
			'filter' => null
		),
		$attributes
	);
	return optio_dentistry_render_control( 'video_player', $attributes );
}

/**
 * Embed thumbnail link to video in a lightbox
 */

function optio_dentistry_thumbnail_link( $attributes ) {
	$attributes = shortcode_atts(
		array(
			'id' => null,
			'language' => 'en',
			'filter' => null
		),
		$attributes
	);
	return optio_dentistry_render_control( 'thumbnail_link', $attributes );
}

/**
 * Create link to video in a lightbox
 */

function optio_dentistry_lightbox( $attributes, $content ) {
	$attributes = shortcode_atts(
		array(
			'id' => null,
			'language' => 'en',
			'filter' => null
		),
		$attributes
	);
	return "<a href=\"javascript:optio.openLightbox('{$attributes['id']}', '{$attributes['language']}');\">$content</a>";
}

/**
 * Render control
 */

function optio_dentistry_render_control( $control = 'video_library', $attributes = array() ) {

	// Attempt to render server side
	if ( ini_get('allow_url_fopen') ) {
		return file_get_contents(
			OPTIO_SERVER_URL . '/embed/?format=html' .
			'&v=3' .
			'&url=' . rawurlencode( $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ) .
			'&q=' . rawurlencode( $_SERVER['QUERY_STRING'] ) .
			'&video=' . rawurlencode( $attributes['id'] ) .
			'&language=' . rawurlencode( $attributes['language'] ) .
			'&filter=' . rawurlencode( $attributes['filter'] ) .
			'&control=' . $control
		);
	}

	// Include JavaScript API
	wp_enqueue_script( 'optio-api' );

	// Map attributes to data-* attributes
	$attributes_map = array(
		'id' => 'data-video-id',
		'language' => 'data-language',
		'filter' => 'data-filter'
	);

	// Map control to class name
	$control_map = array(
		'video_library' => 'optio-library',
		'video_player' => 'optio-video',
		'thumbnail_link' => 'optio-thumbnail'
	);
	if ( !isset( $control_map[$control] ) ) {
		return '';
	}

	// Build and return HTML placeholder tag
	$data_attributes = '';
	foreach ( $attributes as $key => $value ) {
		if ( isset( $attributes_map[$key] ) ) {
			$value = htmlspecialchars( $value, ENT_QUOTES );
			$data_attributes .= " {$attributes_map[$key]}=\"{$value}\"";
		}
	}
	return "<div class=\"{$control_map[$control]}\"$data_attributes></div>";
}

/**
 * Add editor assets
 */

function optio_dentistry_block_editor_assets() {
 	wp_register_script(
 		'optio-dentistry',
 		plugins_url( 'dist/index.js', __FILE__ ),
 		array( 'wp-blocks', 'wp-components', 'wp-element', 'wp-editor', 'optio-api' ),
		'2.0'
 	);

	wp_register_style(
		'optio-dentistry',
		plugins_url( 'dist/style.css', __FILE__ ),
		array(),
		'2.0'
	);
}

add_action( 'enqueue_block_editor_assets', 'optio_dentistry_block_editor_assets' );

/**
 * Add block category
 */

function optio_dentistry_block_categories( $categories, $post ) {
	$categories[] = array(
		'slug' => 'optio-dentistry',
		'title' => __( 'Optio Dentistry', 'optio-dentistry' ),
		'icon' => NULL
	);
	return $categories;
}

add_filter( 'block_categories', 'optio_dentistry_block_categories', 10, 2 );

/**
 * Add blocks
 */

function optio_dentistry_register_blocks() {
	register_block_type( 'optio-dentistry/optio-video', array(
    'editor_script' => 'optio-dentistry',
		'editor_style' => 'optio-dentistry',
	) );

	register_block_type( 'optio-dentistry/optio-library', array(
		'editor_script' => 'optio-dentistry',
	) );
}

add_action( 'init', 'optio_dentistry_register_blocks' );

?>
