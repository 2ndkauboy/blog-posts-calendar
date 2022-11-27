<?php
/**
 * Blog Posts Calendar
 *
 * @package 2ndkauboy/blog-posts-calendar
 * @author  Bernhard Kau
 * @license GPLv3
 *
 * @wordpress-plugin
 * Plugin Name: Blog Posts Calendar
 * Plugin URI: https://github.com/2ndkauboy/blog-posts-calendar
 * Description: Generate a dynamic iCalendar with all blog posts.
 * Version: 1.0.0
 * Author: Bernhard Kau
 * Author URI: https://kau-boys.de
 * Text Domain: blog-posts-calendar
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 */

use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;

require_once 'vendor/autoload.php';

function blog_posts_calendar_register_rest_route() {
	register_rest_route(
		'blog-posts-calendar/v1',
		'/ical.ics',
		[
			'methods'             => 'GET',
			'callback'            => 'blog_posts_calendar_generate_ical',
			'permission_callback' => '__return_true',
		]
	);
}

add_action( 'rest_api_init', 'blog_posts_calendar_register_rest_route' );

/**
 * Generate the iCalendar with the bookings.
 */
function blog_posts_calendar_generate_ical() {
	// Create the calendar.
	$calendar = Calendar::create( 'example.com' );

	// Get all blog posts.
	$query_args = [
		'post_type'      => 'post',
		'post_status'    => [
			'publish',
			'future',
		],
		'posts_per_page' => - 1,
	];

	$posts = get_posts( $query_args );

	// Create an event per blog post.
	foreach ( $posts as $post ) {
		$start_date = new DateTime( $post->post_date_gmt, new DateTimeZone( 'UTC' ) );
		$end_date   = ( clone $start_date )->add( new DateInterval( 'PT15M' ) );

		$event = Event::create();
		$event->name( $post->post_title );
		$event->startsAt( $start_date );
		$event->endsAt( $end_date );
		$event->uniqueIdentifier( $post->ID );

		$calendar->event( $event );
	}

	// Print the calendar output.
	header( 'Content-Type: text/calendar; charset=utf-8' );
	echo $calendar->get();
	exit;
}
