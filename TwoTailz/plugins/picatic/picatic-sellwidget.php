<?php

if ( ! defined( 'ABSPATH' ) )
  die( "Can't load this file directly" );

// required for shortcode creation
if (!isset($description)) {
  $description = "no";
}
if (!isset($show_ticket_desc)) {
  $show_ticket_desc = "";
}
if (!isset($show_event_title)) {
  $show_event_title = "";
}
if (!isset($theme_options)) {
  $theme_options = "";
}
?>


<!--add class of  ptw-dark  for dark theme -->
<div class="ptw-std<?php echo " ".$theme_options ?>">

<?php

// get Picatic Options
$getOptions = get_option( 'picatic_settings' );
$userid =  $getOptions['user_id'];

$theEvent = PicaticLib::getEvent($event);
$getTickets = PicaticLib::getTicketsForEvent($event);

// get widget options
$widget_settings = get_option( 'widget_picatic_sell_tickets_widget' );

// render the result on theme
?>

 <div class="widget-header">
    <div class="widget-header-content">
    <?php if ($show_event_title == 1 || $title === "yes"){ ?>
      <h1 class="event-title">
        <a href="https://www.picatic.com/<?php echo $theEvent['slug']; ?>?utm_source=wordpress&utm_campaign=<?php echo $theEvent['id']; ?>&utm_medium=integrations&utm_content=ticket%20widget" target="_blank" title="Testing Event">
          <span itemprop="summary"><?php echo PicaticLib::truncateString($theEvent['title'], 54); ?></span>
        </a>
      </h1>
    <?php } ?>

      <div class="event-promoter">
        <span><?php echo PicaticLib::eventDateTime($theEvent['start_date'],$theEvent['start_time'],$theEvent['end_date'],$theEvent['end_time']); ?></span><br>

        <span><?php echo PicaticLib::compiledVenueLocation($theEvent); ?></span>
      </div><!-- /.event-promoter -->

    </div><!-- /.widget-header-content -->
    <div class="widget-cover-photo" style="background-image: url(<?php if (!empty($theEvent['cover_image_uri'] )) echo $theEvent['cover_image_uri']; ?>);">
    </div><!-- /.widget-cover-photo -->
  </div><!-- /.widget-header -->

  <div class="widget-ticket-block">
  <form action="https://www.picatic.com/<?php echo $theEvent['slug']; ?>/checkout?utm_source=wordpress&utm_campaign=<?php echo $theEvent['id']; ?>&utm_medium=integrations&utm_content=ticket%20widget" target="_parent" id="TicketPurchaseWidgetForm" method="post" accept-charset="utf-8">
      <input type="hidden" name="data[Event][id]" value="<?php echo $theEvent['id']; ?>" id="EventId"> <?php //used in shortcode generator ?>
      <table class="widget-table">
        <tbody>
          <?php foreach($getTickets as $index=>$currentTicket) {
            if ( !in_array($currentTicket['type'], array('regular', 'free')) ) {
              continue;
            }
            if($currentTicket['status'] !== 'hidden'
              && (
                (strtotime(date('Y-m-d')) >= strtotime($currentTicket['open_date']) && strtotime(date('Y-m-d')) < strtotime($currentTicket['close_date']))
                ||
                (empty($currentTicket['open_date']) && strtotime(date('Y-m-d')) < strtotime($currentTicket['close_date']))
                ||
                (strtotime(date('Y-m-d')) >= strtotime($currentTicket['open_date']) && empty($currentTicket['close_date']))
                ||
                (empty($currentTicket['open_date']) && empty($currentTicket['close_date']))
              )
            ) {
              if ($currentTicket['price'] == 0) {
                $price = __('Free', 'Picatic_Sell_Tickets_Widget_plugin');
              } else{
                  $price = "".PicaticLib::currencySymbol($theEvent['_currency']['code'])."".number_format_i18n( $currentTicket['price'], 2 );
              }
              $ticketPriceDiscountId = '';
              ?>

            <tr itemprop="tickets" itemscope itemtype="http://schema.org/Offer">
              <td>
                <div class="widget-ticket-price" itemprop="price">
                    <?php
                      if ($price === 'Free') {
                        echo $price;
                      }else{
                        $pricePieces = explode(".", $price);
                        echo $pricePieces[0].'<span>'.$pricePieces[1].'</span>';
                      }
                    ?>
                </div><!-- /.widget-ticket-price -->
              </td>

              <td class="widget-ticket-name" valign="">
                <input type="hidden" name="data[TicketPrice][<?php echo $index; ?>][id]" value="<?php echo $currentTicket['id']; ?>">
                <input type="hidden" name="data[TicketPrice][{{$index}}][quantity]" value="{{ticketPrice.quantity_selected}}">
                <input type="hidden" name="data[TicketPrice][<?php echo $index; ?>][ticket_price_discount_id]" value="<?php echo $ticketPriceDiscountId; ?>">
                <h3><?php echo $currentTicket['name']; ?></h3>

                <?php if ( $description === 'yes' ){ ?>
                  <span><?php echo $currentTicket['description']; ?></span>
                <?php } ?>
              </td>
              <td width="72">
                <div class="widget-ticket-quantity">
                  <?php if($currentTicket['status'] == 'open') { ?>
                      <select name="data[TicketPrice][<?php echo $index; ?>][quantity]" class="form-control">
                        <option value="0">0</option>
                      <?php
                      $ticketsMin = ($currentTicket['min_quantity'] == 0) ? 1 : $currentTicket['min_quantity'];
                      $ticketsMax = ($currentTicket['max_quantity'] == 0) ? 20 : $currentTicket['max_quantity'];
                      for ($j=$ticketsMin; $j <= $ticketsMax; $j++) { ?>
                        <option value="<?php echo $j; ?>"><?php echo $j; ?></option>
                      <?php } ?>
                      </select>
                    <?php } else { ?>
                      <span><?php _e('CLOSED', 'Picatic_Sell_Tickets_Widget_plugin'); ?></span>
                    <?php } ?>
                </div><!-- /.widget-ticket-quantity -->
              </td>
            </tr>
            <?php } //end if status=!hidden ?>
          <?php } //end foreach ?>
        </tbody>
      </table>
      <?php if( $theEvent['donations_enabled'] ) { ?>
            <!--Event donations-->
      <table class="widget-table">
        <tbody>
          <tr>
            <td class="widget-ticket-name widget-donation">
              <h3>Donation</h3>
                <?php if ( isset($theEvent['donation_title']) ){ ?>
                  <span><?php echo $theEvent['donation_title']; ?></span>
                <?php } ?>
            </td>
            <td class="align-right">
              <div class="promo-code-wrapper">
                <div class="input-group">
                   <span class="input-group-addon"><?php echo PicaticLib::currencySymbol($theEvent['_currency']['code']); ?></span>
                   <input class="form-control" name="data[Donation][amount]" type="text">
                </div><!-- /.input-group -->
              </div><!-- /.promo-code-wrapper -->
            </td>
          </tr>
        </tbody>
      </table><!-- /.widget-table -->
      <?php
        }
      ?>
      <?php if( $theEvent['has_promo_code']) {  ?>
        <script>
        function getPromo() {
          var $href = document.getElementById("url").value;
          var $code = document.getElementById("promoBox").value;
          window.open( $href + $code , "_blank");
        }
        </script>
      <!--PROMO CODE-->
      <table class="widget-table">
        <tbody>
          <tr>
            <td class="widget-ticket-name widget-promo-code">
              <h3>Promo Code</h3>
            </td>
            <td class="align-right">
              <div class="promo-code-wrapper">
                <input id="url" type="hidden" value="<?php echo "https://www.picatic.com/" . $theEvent['slug'] . "?code="; ?>"/>
                <div class="input-group no-margin">
                  <input type="text" id="promoBox" class="form-control">
                  <span class="input-group-btn">
                    <button class="btn btn-small btn-outline btn-outline-teal" onclick="getPromo()" type="button">
                      Apply
                    </button>
                  </span>
                </div><!-- /.input-group -->
              </div><!-- /.promo-code-wrapper -->
            </td>
          </tr>
        </tbody>
      </table><!-- /.widget-table -->
      <?php } //end if(promo) ?>

      <div class="widget-powered-by">
        <a href="https://www.picatic.com/?utm_source=wordpress&utm_campaign=<?php echo $theEvent['id']; ?>&utm_medium=integrations&utm_content=ticket%20widget" target="_blank">
        <img src="<?php if ( $theme_options === 'ptw-dark' ) { echo plugins_url( 'images/picatic-horiz-wh.svg', __FILE__ ); } else { echo plugins_url( 'images/picatic-horiz-bl.svg', __FILE__ ); } ?>" alt="Picatic" width="108" height="20"></a>
        <button type="submit" class="btn btn-small btn-teal pull-right">Purchase</button>
      </div><!-- /.widget-powered-by -->

    </form>
  </div><!-- /.ptw-ticket-block -->
</div><!-- /.ptw -->
