<?php
/*
Plugin Name: Minimal AutoP
Plugin URI:  https://github.com/andrewklimek/minimal-autop/
Description: very simple replacement for wpautop, won't interfere with your html.
Version:     0.1.2
Author:      Andrew J Klimek
Author URI:  https://readycat.net
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Minimal AutoP is free software: you can redistribute it and/or modify 
it under the terms of the GNU General Public License as published by the Free 
Software Foundation, either version 2 of the License, or any later version.

Minimal AutoP is distributed in the hope that it will be useful, but without 
any warranty; without even the implied warranty of merchantability or fitness for a 
particular purpose. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with 
Minimal AutoP. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/

function mnml_autop( $c ) {
	
	$c = "\n\n{$c}";
	
	// not sure if we need to standardize line breaks. wp_autop does.
	// Maybe if entries were made on a different OS than current server?
	// And would Darwin \r ever still show up??
	$c = str_replace( array( "\r\n", "\r" ), "\n", $c, $count );
	// How often does this happen?
	if ( $count ) error_log( 'replaced \r\n or \r');
	
	$have_pre = false;
	
	if ( false !== strpos( $c, "<pre") ) {// check for pre tags. could use stripos but is it slower? this plugin is just for me
	
		$have_pre = true;
	
		$c = preg_replace_callback(
			'|<pre[\s\S]+?</pre>|',
			function ($matches) {
				return str_replace( "\n", "~crazyNewLinePlaceholder~", $matches[0]);
			},
			$c
		);
	}
	
	$c = str_replace( array( "\n<br>", "<br>\n" ), "<br>", $c );// trim line breaks from any <br>s they might code

	// I tk \n out of the first group, wasn't sure why it was there.
	$c = preg_replace( "|([\n>\]])\n+([^\n<\[])|", "$1\n<p>$2", $c );// opening <p>
	
	// $c = preg_replace( "/([^\n\]>])\n+?(\n|\[|<)/", "$1</p>\n$2", $c );// closing </p> isnt technically needed
	$c = preg_replace( "|([^\n\]>])\n([^\n\[<])|", "$1\n<br>$2", $c );// <br>
	
	if ( $have_pre ) {
		$c = str_replace( "~crazyNewLinePlaceholder~", PHP_EOL, $c);
	}
	
	//Let's try to keep double spacing!
	$c = str_replace( ".  ", ".&nbsp; ", $c );
	
	return $c;
}

remove_filter( 'the_content', 'wpautop' );
remove_filter( 'the_content', 'shortcode_unautop' );
remove_filter( 'the_excerpt', 'wpautop' );
remove_filter( 'the_excerpt', 'shortcode_unautop' );
remove_filter( 'the_excerpt_embed', 'wpautop' );
remove_filter( 'the_excerpt_embed', 'shortcode_unautop' );

// Conditional version
// add_action( 'wp', function() {
// 	if ( is_page() ) remove_filter( 'the_content', 'wpautop' );
// } );

add_filter( 'the_content', 'mnml_autop' );
add_filter( 'the_excerpt', 'mnml_autop' );
add_filter( 'the_excerpt_embed', 'mnml_autop' );
