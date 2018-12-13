<?php

if ( ! defined( 'ABSPATH' ) )
  die( "Can't load this file directly" );

// get picatic options
$getOptions = get_option( 'picatic_settings' );
$userid =  $getOptions['user_id'];

// call events Api
$allEvents = PicaticLib::getEventsForUserLong();

//widget options
$get_widget_settings = get_option( 'widget_picatic_upcoming_events_widget' );
?>

<div class="pt-upcoming-events">
  <?php
  if ( !empty($allEvents) ) {
    foreach($allEvents as $theEvent) { ?>
    <div class="pt-event-box-container">
      <div class="pt-event-box">
        <div class="pt-event-box-img">
          <div class="pt-event-box-content">
            <h3 class="pt-event-box-title">
              <a href="https://www.picatic.com/<?php echo $theEvent['slug'] ?>?utm_source=wordpress&utm_campaign=<?php echo $theEvent['id'] ?>&utm_medium=integrations&utm_content=upcoming%20events%20widget" target="_blank" title="<?php echo $theEvent['title']; ?>" itemprop="url">
                <span itemprop="name">
                  <?php echo PicaticLib::truncateString($theEvent['title'], 54); ?>
                </span>
              </a>
            </h3>
            <?php
            if ( $theEvent['promoter_name'] ) {
            ?>
            <div class="pt-event-box-by">
              <a href="https://www.picatic.com/<?php echo $theEvent['slug'] ?>?utm_source=wordpress&utm_campaign=<?php echo $theEvent['id'] ?>&utm_medium=integrations&utm_content=upcoming%20events%20widget" target="_blank">
                <span title="<?php echo $theEvent['promoter_name'] ?>"><?php echo $theEvent['promoter_name']; ?></span>
              </a>
            </div>
            <?php
            }
            ?>
          </div>

          <div class="pt-event-box-cover-photo" <?php if ($theEvent['cover_image_uri']) { ?>style="background-image: url(<?php echo $theEvent['cover_image_uri']; ?>);"<?php } ?>>
            <a href="https://www.picatic.com/<?php echo $theEvent['slug'] ?>?utm_source=wordpress&utm_campaign=<?php echo $theEvent['id'] ?>&utm_medium=integrations&utm_content=upcoming%20events%20widget" target="_blank"></a>
          </div>

        </div><!-- /.pt-event-box-img -->

        <div class="pt-event-box-date">
          <div>
            <a href="https://www.picatic.com/<?php echo $theEvent['slug'] ?>?utm_source=wordpress&utm_campaign=<?php echo $theEvent['id'] ?>&utm_medium=integrations&utm_content=upcoming%20events%20widget" target="_blank">
              <img src="<?php echo plugins_url( 'images/calendar.svg', __FILE__ ); ?>" alt="Date" width="16" height="16" />
              <span itemprop="startDate"><?php echo PicaticLib::eventUpcomingDate($theEvent['start_date'], $theEvent['end_date']); ?></span>
            </a>
          </div>
          <?php if ( PicaticLib::compiledVenueLocation($theEvent) ): ?>
            <div class="pt-event-box-venue">
              <a href="https://www.picatic.com/<?php echo $theEvent['slug'] ?>?utm_source=wordpress&utm_campaign=<?php echo $theEvent['id'] ?>&utm_medium=integrations&utm_content=upcoming%20events%20widget" target="_blank">
                <img src="<?php echo plugins_url( 'images/globe.svg', __FILE__ ); ?>" alt="Location" width="16" height="16" />
                <span><?php echo PicaticLib::compiledVenueLocation($theEvent); ?></span>
              </a>
            </div><!-- /.pt-event-box-venue -->
          <?php endif; ?>
        </div><!-- /.pt-event-box-date -->
        <div class="pt-event-box-footer">
          <span><img src="<?php echo plugins_url( 'images/share.svg', __FILE__ ); ?>" alt="share" width="14" height="14" /> <span>Share</span></span>
          <ul class="dropdown-menu social-share pt-event-box-menu">
            <li class="share-btn share-btn-twitter">
              <a href="http://twitter.com/share?url=https://www.picatic.com/<?php echo $theEvent['slug']."&text=".$theEvent['title']; ?><?php if ($theEvent['twitter_hashtag']) { echo "&hashtags=" . $theEvent['twitter_hashtag']; } ?>" target="_blank">
                <img src="<?php echo plugins_url( 'images/twitter.svg', __FILE__ ); ?>" alt="Twitter" width="16" height="14" /> <span>Share on Twitter</span>
              </a>
            </li><!-- /.share-btn share-btn-twitter -->

            <li class="share-btn share-btn-googleplus">
              <a href="https://plus.google.com/share?url=https://www.picatic.com/<?php echo $theEvent['slug']; ?>" target="_blank">
                <img src="<?php echo plugins_url( 'images/google+.svg', __FILE__ ); ?>" alt="Google+" width="16" height="14" /> <span>Share on Google+</span>
              </a>
            </li><!-- /.share-btn share-btn-googleplus -->

            <li class="share-btn share-btn-facebook">
              <a href="https://www.facebook.com/sharer/sharer.php?u=https://www.picatic.com/<?php echo $theEvent['slug']; ?>" target="_blank">
                <img src="<?php echo plugins_url( 'images/facebook.svg', __FILE__ ); ?>" alt="Facebook" width="16" height="16" /> <span>Share on Facebook</span>
              </a>
            </li><!-- /.share-btn share-btn-facebook -->

            <li class="share-btn share-btn-linkedin">
              <a href="https://www.linkedin.com/shareArticle?mini=true&amp;url=https://www.picatic.com/<?php echo $theEvent['slug'].'&title='.$theEvent['title'].'&summary='.$theEvent['summary'].'&source=Picatic'; ?>"  target="_blank">
                <img src="<?php echo plugins_url( 'images/linkedin.svg', __FILE__ ); ?>" alt="LinkedIn" width="16" height="16" /> <span>Share on LinkedIn</span>
              </a>
            </li><!-- /.share-btn share-btn-linkedin -->
          </ul><!-- /.dropdown-menu -->
        </div>

      </div><!-- /.pt-event-box -->
    </div><!-- /.pt-upcoming-events -->
    <?php
    } // end theEvent
  } else {
  ?>
    <p><?php _e('No upcoming events at this time.', 'Picatic_Sell_Tickets_Widget_plugin'); ?></p>
  <?php
  }
  ?>

</div><!-- /.pt-upcoming-events -->
