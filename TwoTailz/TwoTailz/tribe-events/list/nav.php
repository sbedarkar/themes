<?php
/**
 * List View Nav Template
 * This file loads the list view navigation.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/list/nav.php
 *
 * @package TribeEventsCalendar
 * @version 4.2
 *
 */
global $wp_query;

$events_label_plural = tribe_get_event_label_plural();?>

<h3 class="screen-reader-text" tabindex="0"><?php echo esc_html( sprintf( esc_html__( '%s List Navigation', 'the-events-calendar' ), $events_label_plural ) ); ?></h3>
<ul>
	<!-- Left Navigation -->

	<?php if ( tribe_has_previous_event() ) : ?>
		<li>
			<a href="http://twotailzrescue.org/eventz/list/?tribe_event_display=past&tribe_paged=1" rel="prev"><span>&laquo;</span> Previous Events</a>

		</li><!-- .tribe-events-nav-left -->
	<?php endif; ?>

	<!-- Right Navigation -->
	<?php if ( tribe_has_next_event() ) : ?>
		<li>
			<a href="http://twotailzrescue.org/eventz/list/?tribe_event_display=list&tribe_paged=1" rel="next">Next Events <span>&raquo;</span></a>
		</li><!-- .tribe-events-nav-right -->
	<?php endif; ?>
</ul>
