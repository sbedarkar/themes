<?php
/**
 * Name: Picatic Lib
 *
 * Description: Event/widget related public functions
 */

if ( ! defined( 'ABSPATH' ) )
  die( "Can't load this file directly" );


class PicaticLib {

  public static function currencySymbol($code) {
    $symbol = '$';
    if ( $code === 'GBP' ) {
      $symbol = '£';
    } else if ( $code === 'EUR' ) {
      $symbol = '€';
    } else if ( $code === 'SEK' ) {
      $symbol = 'kr';
    } else if ( $code === 'CHF' ) {
      $symbol = 'CHF';
    }
    return $symbol;
  }

  public static function truncateString($string, $limit) {
    if ( strlen($string) <= $limit ) return $string;
    if ( false !== ($breakpoint = strpos($string, ' ', $limit)) ) {
      if ( $breakpoint < strlen($string) - 1 ) {
        $string = substr($string, 0, $breakpoint) . '&hellip;';
      }
    }
    return $string;
  }

  public static function eventUpcomingDate($startDate, $endDate) {
    $startDate = strtotime($startDate);
    $endDate = strtotime($endDate);
    $startMonth = date('F', $startDate);
    $endMonth = date('F', $endDate);
    $date = '';

    if ( !empty($startDate) ) {
      if ( $startDate == $endDate ) {
        $date = date('l, F j', $startDate);
      } else if ( $startMonth == $endMonth ) {
        $date = date('F j', $startDate);
        $date .= date(' - j', $endDate);
      } else {
        $date = date('F j', $startDate);
        $date .= date(' - F j', $endDate);
      }
    }
    return $date;
  }

  public static function eventDateTime($startDate, $startTime, $endDate, $endTime){
    $startDateSec = strtotime($startDate);
    $endDateSec = strtotime($endDate);
    $startTimeSec = strtotime($startTime);
    $endTimeSec = strtotime($endTime);

    $startMonth = date('m', $startDateSec);
    $startYear = date('Y', $startDateSec);
    $endMonth = date('m', $endDateSec);
    $endYear = date('Y', $endDateSec);

    $startHour = date('g:iA', $startTimeSec);
    $endHour = date('g:iA', $endTimeSec);

    if ( $startDate === $endDate ) {
      $convertedDate = date('D, M j Y', $startDateSec);
      if ( !empty($startHour) ) {
        $convertedDate .= ' '.$startHour;
        if ( !empty($endHour) ) {
          $convertedDate .= ' - '.$endHour;
        }
      }
    } else {
      if ( $startMonth === $endMonth && $startYear === $endYear ) {
        $convertedDate = date('M j',$startDateSec ) . '-' . date('j Y', $endDateSec);
      } else if ( $startYear === $endYear && $startMonth !== $endMonth ) {
        $convertedDate = date('M j',$startDateSec) . '-' . date('M j Y', $endDateSec);
      } else {
        $convertedDate = date('M j Y',$startDateSec) . '-' . date('M j Y', $endDateSec);
      }
    }

    return $convertedDate;
  }

  public static function compiledVenueLocation($event) {
    $venueLocation = '';
    $venueLocality = true;
    $venueRegion = true;
    $venueCountry = true;
    if ( empty($event['venue_locality']) ) {
      $venueLocality = false;
    }
    if ( empty($event['venue_region_id']) ) {
      $venueRegion = false;
    }
    if ( empty($event['venue_country_id']) ) {
      $venueCountry = false;
    }
    if ( $venueLocality === true ) {
      $venueLocation = $event['venue_locality'];
      if ( $venueRegion === true or $venueCountry === true ) {
        $venueLocation .= ', ';
      }
    }
    if ( $venueRegion === true ) {
      $venueLocation .= $event['_venue_region']['iso'];
      if ( $venueCountry === true ) {
        $venueLocation .= ', ';
      }
    }
    if ( $venueCountry === true ) {
      $venueLocation .= $event['_venue_country']['country'];
    }
    return $venueLocation;
  }

  /* Fetch Data */

  /**
   * Get the PicaticAPI factory configured with API key
   * @return [type] [description]
   */
  public static function getFactory() {
    $getOptions = get_option( 'picatic_settings' );
    // call events Api
    $picaticInstance = PicaticAPI::instance();
    $picaticInstance->setApiKey( $getOptions['access_key'] );
    return $picaticInstance->factory();
  }

  /**
   * Get a short list of events for a user
   * @return [type] [description]
   */
  public static function getEventsForUserShort() {
    $events = PicaticLib::cacheRead('events_short');
    if ($events !== false) {
      return $events;
    }
    $getOptions = get_option( 'picatic_settings' );
    $events = PicaticLib::getFactory()->modelCreate('Event')->findAll(array(
      'user_id' => $getOptions['user_id'] ,
      'status' => 'active' ,
      'fields' => 'id,title,status',
      'limit' => 80
      )
    );
    $normalizedEvents = array();
    foreach($events as $event) {
      $normalizedEvents[] = $event->getValues();
    }
    usort($normalizedEvents, 'PicaticLib::eventSort');
    PicaticLib::cacheWrite('events_short', $normalizedEvents);
    return $normalizedEvents;
  }

  /**
   * Get the detailed list of events for a user-
   * @return [type] [description]
   */
  public static function getEventsForUserLong() {
    $events = PicaticLib::cacheRead('events_long');
    if ($events !== false) {
      return $events;
    }
    $getOptions = get_option( 'picatic_settings' );
    $events  =PicaticLib::getFactory()->modelCreate('Event')->findAll(array(
      'user_id' => $getOptions['user_id'],
      'status' => 'active',
      'extend' => 'venue_region,venue_country,currency',
      'fields' => 'id,title,status,cover_image_uri,slug,start_date,start_time,end_date,end_time,venue_street,venue_locality,venue_region_id,venue_country_id,promoter_name,_venue_region,_venue_country,_currency',
      'limit' => 80
      )
    );
    $normalizedEvents = array();
    foreach($events as $event) {
      $normalizedEvents[] = $event->getValues();
    }
    usort($normalizedEvents, 'PicaticLib::eventSort');
    PicaticLib::cacheWrite('events_long', $normalizedEvents);
    return $normalizedEvents;
  }

  /**
   * Sort events by start date
   * @return [type]          [description]
   */
  public static function eventSort($a, $b) {
    return strtolower($a['start_date']) > strtolower($b['start_date']);
  }

  /**
   * Get an event
   * @param  [type] $eventId [description]
   * @return [type]          [description]
   */
  public static function getEvent($eventId) {
    $events = PicaticLib::cacheRead('events');
    if (isset($events[$eventId])) {
      return $events[$eventId];
    }
    $event  = PicaticLib::getFactory()->modelCreate('Event')->find( $eventId, array(
      'extend' => 'currency,venue_region,venue_country'
    ));
    if (!is_array($events)) {
      $events = array();
    }
    $events[$eventId] = $event->getValues();
    PicaticLib::cacheWrite('events', $events);
    return $event;
  }


  /**
   * Order array by key value
   * @param  [type]
   * @return [type]
   */
  public static function arrayOrderby() {
    $args = func_get_args();
    $data = array_shift($args);
    foreach ($args as $n => $field) {
      if (is_string($field)) {
        $tmp = array();
        foreach ($data as $key => $row)
          $tmp[$key] = $row[$field];
        $args[$n] = $tmp;
      }
    }
    $args[] = &$data;
    call_user_func_array('array_multisort', $args);
    return array_pop($args);
  }


  /**
   * Get the tickets for an event
   * @param  [type] $eventId [description]
   * @return [type]          [description]
   */
  public static function getTicketsForEvent($eventId) {
    $tickets = PicaticLib::cacheRead('tickets');
    if (isset($tickets[$eventId])) {
      return $tickets[$eventId];
    }
    $_tickets = PicaticLib::getFactory()->modelCreate('Ticket_Price')->findAll(array(
      'event_id' => $eventId,
      'extend' => 'ticket_price_discount',
      )
    );
    if(!is_array($tickets)) {
      $tickets = array();
    }
    $normalizedTickets = array();
    foreach($_tickets as $ticket) {
      $normalizedTickets[] = $ticket->getValues();
    }
    $sortedTickets = PicaticLib::arrayOrderby($normalizedTickets, 'order', SORT_ASC);
    $tickets[$eventId] = $sortedTickets;
    PicaticLib::cacheWrite('tickets', $tickets);
    return $sortedTickets;
  }

  /* Cache Related */

  public static $cache_prefix = "pt_cache_";

  public static function cacheWrite($key, $value, $timeout=3600) {
    $settings = get_option('picatic_settings_cache');
    if (isset($settings['cache']) && $settings['cache'] == "1") {
      if ( isset($settings['cache_duration']) ) {
        $timeout = $settings['cache_duration'];
      }
      $full_key = sprintf("%s%s", PicaticLib::$cache_prefix, $key);
      set_transient($full_key, $value, $timeout);
    }

  }

  public static function cacheRead($key) {
    $full_key = sprintf("%s%s", PicaticLib::$cache_prefix, $key);
    return get_transient($full_key);
  }

  /**
   * Clear the cached keys
   * @HACK static key names implies the above functions are terrible, make it not terrible
   * @return [type] [description]
   */
  public static function cacheClear() {
    $keys = array('events_short', 'events_long', 'events', 'tickets', 'pt_cache_events', 'pt_cache_tickets', 'pt_cache_events_long');
    foreach($keys as $key) {
      delete_transient($key);
    }
  }
}
