<?php
/**
 * Plugin Name: WP Auto Post Link Title Attributes
 * Plugin URI: https://github.com/wpexplorer/wpex-auto-link-titles
 * Description: Automatically adds link title attributes to links within posts that don't have them.
 * Author: AJ Clarke
 * Author URI: http://www.wpexplorer.com
 * Version: 1.2.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Version
define( 'WPEX_PLUGIN_VERSION', '1.2.0' );
define( 'WPEX_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// This is the main plugin function - does everything.
function wpex_auto_add_link_titles( $content ) {

	// No need to do anything if there isn't any content or if DomDocument isn't supported
	if ( empty( $content ) || ! class_exists( 'DOMDocument' ) ) {
		return $content;
	}

	// Define links array
	$links = array();

	// Create new dom object
	$dom = new DOMDocument;

	// Load html into the object
	$dom->loadHTML( utf8_decode( $content ) );

	// Discard white space
	$dom->preserveWhiteSpace = false;

	// Loop through all content links
	foreach( $dom->getElementsByTagName( 'a' ) as $link ) {

		// If the title attribute is already defined no need to do anything
		if ( $link->getAttribute( 'title' ) ) {
			continue;
		}

		// Get link text
		$link_text = $link->textContent;

		// If there isn't any link text (most probably an image) lets try and get it from the first child
		if ( ! $link_text && ! empty( $link->firstChild ) && $link->firstChild->hasAttributes() ) {

			// Get alt
			$alt = $link->firstChild->getAttribute( 'alt' );

			// If no alt get image title
			$alt = $alt ? $alt : $link->firstChild->getAttribute( 'title' );

			// Clean up alt (remove dashses and underscores which are common in WP)
			$alt = str_replace( '-', ' ', $alt );
			$alt = str_replace( '_', ' ', $alt );

			// Return cleaned up alt
			$link_text = $alt;

		}

		// Save links and link text in $links array
		if ( $link_text ) {
			$links[$link_text] = $link->getAttribute( 'href' );
		}

	}

	// Loop through links array and update post content to add link titles
	if ( ! empty( $links ) ) {
		foreach ( $links as $text => $link ) {
			if ( $link && $text ) {
				$text    = ( $text ); // Sanitize
				$text    = ucwords( $text );  // Captilize words (looks better imo)
				$replace = $link .'" title="'. $text .'"'; // Add title to link
				$content = str_replace( $link .'"', $replace, $content ); // Replace post content
			}

		}
	}

	// Return post content
	return $content;

}

// Edit content on the front end
add_filter( 'the_content', 'wpex_auto_add_link_titles' );
