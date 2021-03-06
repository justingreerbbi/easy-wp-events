<?php
if ( ! empty( $_POST['ewp_event_checkout'] ) ) {
	$stripe = new EWP_Event_Stripe_Gateway();
	$stripe->createCharge( $_POST );
}
get_header();
global $post;
$event_options = get_option( 'ewp_events_options' );
?>
<style>
    .easy_wp_event_table {
        text-align: left;
    }

    input[type="text"], input[type="number"] {
        font-size: inherit;
    }

    <?php print $event_options['custom_css_single_event_page']; ?>
</style>
<div id="page" class="single">
    <div class="ewp-events-content">
        <div class="container">
            <div class="row">
                <div class="col mb-5">
                    <h2><?php echo get_the_title(); ?></h2>
                </div>
            </div>
            <div class="row">
                <div class="col-md-9">
                    <h5 class="mb-3">About This Event</h5>
					<?php print apply_filters( 'the_content', $post->post_content ); ?>

                    <div class="ewp_events_tickets">
                        <h5 class="mb-3 mt-3">Purchase Tickets</h5>
						<?php
						$event_tickets = get_post_meta( $post->ID, 'event_tickets', true );
						?>
                        <form name="event-tickets" id="frontend-ticket-form">
							<?php wp_nonce_field( 'ewp_events_ticket_nonce_check_' . $post->ID, 'ewp_events_ticket_nonce_check' ); ?>
                            <input type="hidden" name="event_id" value="<?php print $post->ID; ?>"/>
                            <table class="easy_wp_event_table">
                                <thead>
                                <th width=80%">Ticket Type</th>
                                <th width="20%">Qty</th>
                                </thead>
								<?php foreach ( $event_tickets as $event ) : ?>
                                    <tr>
                                        <td>
                                            <strong><?php print $event['label']; ?> -
                                                $<?php print $event['price']; ?></strong><br/>
                                            <small><?php print $event['ticket_description']; ?></small>
                                        </td>
                                        <td style="vertical-align: middle;">
                                            <input type="number" class="form-control"
                                                   name="ticket_type_<?php print ewp_events_sanatize_event_title( $event['label'] ); ?>"
                                                   min="" max="<?php print $event['ticket_availability']; ?>">
                                            <input type="hidden"
                                                   name="price_<?php print ewp_events_sanatize_event_title( $event['label'] ); ?>"
                                                   value="<?php print $event['price']; ?>"/>
                                            <input type="hidden"
                                                   name="type_<?php print ewp_events_sanatize_event_title( $event['label'] ); ?>"
                                                   value="<?php print $event['ticket_type']; ?>"/>
                                        </td>
                                    </tr>
								<?php endforeach; ?>
                            </table>
                            <button class="btn btn-primary btn-lg float-end mt-3 clearboth">Checkout</button>
                        </form>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="ewp-events-module mb-5 btn-block form-group">
                        <!--<button class="btn btn-success btn-lg input-block-level form-control btn-block">
							View Tickets
						</button>-->
                        <h4>Event Info</h4>
                    </div>
                    <div class="ewp-events-module mb-5">
                        <h5>Date and Time</h5>
						<?php
						$start_datetime = get_post_meta( $post->ID, 'start_date', true );
						$end_datetime   = get_post_meta( $post->ID, 'end_date', true );

						print '<p>Start Time:<br/>' . date( 'M j, Y - g:ia', strtotime( $start_datetime ) ) . '</p>';
						print '<p>End Time: <br/>' . date( 'M j, Y - g:ia', strtotime( $end_datetime ) ) . '</p>';
						//print '<a href="">Add to Calendar</a>';
						?>
                    </div>

                    <div class="ewp-events-module mb-5">
                        <h5>Location</h5>
						<?php
						$venue_name    = get_post_meta( $post->ID, 'venue_name', true );
						$venue_address = get_post_meta( $post->ID, 'venue_address', true );
						print '<p>' . $venue_name . '</p>';
						print '<p>' . $venue_address . '</p>';
						//print '<a href="">View Map</a>';
						?>
                    </div>

                    <!--<div class="ewp-events-module">
						<strong>Refund Policy</strong>
						 <p>No Refunds</p>
					</div>-->
                </div>
            </div>
        </div>
    </div>
</div>
<?php get_footer(); ?>
