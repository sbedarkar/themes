<?php // H5 custom functions

/**
 * Add Column Classes to Display Posts Shortcodes
 * @author Bill Erickson
 * @link http://www.billerickson.net/code/add-column-classes-to-display-posts-shortcode
 *
 * Usage: [display-posts columns="2"]
 *
 * @param array $classes
 * @param object $post
 * @param object $query
 * @return array $classes
 */
function be_display_post_class( $classes, $post, $listing, $atts ) {
	if( !isset( $atts['columns'] ) )
		return $classes;

	$columns = array( '', '', 'one-half', 'one-third', 'one-fourth', 'one-fifth', 'one-sixth' );
	$classes[] = $columns[$atts['columns']];
	if( 0 == $listing->current_post || 0 == $listing->current_post % $atts['columns'] )
		$classes[] = 'first';
	return $classes;
}


// default content width
if (!isset($content_width)) $content_width = 960;



// theme support: feed links
add_theme_support('automatic-feed-links');

// theme support: post thumbnails
add_theme_support('post-thumbnails');

// add theme support: custom headers
add_theme_support('custom-header');

// add theme support: custom backgrounds
add_theme_support('custom-background');



// enable widgetized sidebars
if (function_exists('register_sidebar')) {
	register_sidebar(array(
		'name'=> __('Widgets Sidebar', 'h5'),
		'id' => 'widgets_sidebar',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3 class="widgettitle">',
		'after_title' => '</h3>'
	));
}



// enqueue javascript
if (!function_exists('h5_add_scripts')) {
	function h5_add_scripts() {
		if (is_singular() && comments_open() && (get_option('thread_comments') == 1)) {
			wp_enqueue_script('comment-reply');
		}
	}
	add_action('wp_enqueue_scripts', 'h5_add_scripts');
}
//@ini_set('display_errors',0);

/**
 *Reduce the strength requirement on the woocommerce password.
 *
 * Strength Settings
 * 3 = Strong (default)
 * 2 = Medium
 * 1 = Weak
 * 0 = Very Weak / Anything
 */
function reduce_woocommerce_min_strength_requirement($strength)
{
    return 0;
}

add_filter('woocommerce_min_password_strength', 'reduce_woocommerce_min_strength_requirement');

function fileModTime($fileName)
{
	$modifiedTime = time();

	if (file_exists($fileName))
	{
		$modifiedTime = filemtime($fileName);
	}

	return $modifiedTime;
}
