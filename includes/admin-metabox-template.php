<?php global $post; ?>
<script type="text/javascript">
    jQuery(document).on('click', '#addRow', function () {
        var clonedRow = jQuery('.ewpevents-pricing tr.single-ticket-row:first').clone();
        clonedRow.find('input').val('');
        jQuery('.ewpevents-pricing tbody').append(clonedRow);
    });

    jQuery(document).on('click', '#remove_ticket_type', function () {
        jQuery(this).closest('.single-ticket-row').remove();
    });
</script>
<style type="text/css">
    #easy-wp-events-metabox-wrapper {

    }

    #easy-wp-events-metabox-wrapper label {
        font-weight: bold;
        display: block;
        margin-bottom: 15px;
        margin-top: 15px;
        font-size: 15px;
    }

    #easy-wp-events-metabox-wrapper input[type=text], input[type=number], input[type=datetime-local] {
        -webkit-transition: all 0.30s ease-in-out;
        -moz-transition: all 0.30s ease-in-out;
        -ms-transition: all 0.30s ease-in-out;
        -o-transition: all 0.30s ease-in-out;
        outline: none;
        box-sizing: border-box;
        -webkit-box-sizing: border-box;
        -moz-box-sizing: border-box;
        width: 100%;
        background: #fff;
        border: 1px solid #ccc;
        padding: 5px;
        color: #555;
    }

    #easy-wp-events-metabox-wrapper #easy-wp-events-ticket-information {
        padding: 10px;
        box-sizing: border-box;
        background: #f5f6fa;
        margin-top: 1em;
    }
</style>
<form method="post" action="">
    <div class="row" id="easy-wp-events-metabox-wrapper">
        <div class="col-lg-12">
            <div id="event-location">

                <label for="event-location">Venue</label>
                <input class="form-control" type="text" id="venue-name" name="venue_name"
                       value="<?php print get_post_meta( $post->ID, 'venue_name', true ); ?>"/>

                <label for="event-location">Event Address</label>
                <input class="form-control" type="text" id="venue-address" name="venue_address"
                       value="<?php print get_post_meta( $post->ID, 'venue_address', true ); ?>"/>

                <div class="row">
                    <div class="col-6">
                        <label>Event Start Date</label>
                        <input class="form-control" type="datetime-local"
                               value="<?php print get_post_meta( $post->ID, 'start_date', true ); ?>" name="start_date"
                               value="<?php print get_post_meta( $post->ID, 'start_date', true ); ?>"/>
                    </div>
                    <div class="col-6">
                        <label>Event End Date</label>
                        <input class="form-control" type="datetime-local"
                               value="<?php print get_post_meta( $post->ID, 'end_date', true ); ?>" name="end_date"
                               value="<?php print get_post_meta( $post->ID, 'end_date', true ); ?>"/>
                    </div>
                </div>
            </div>

            <hr/>
            <div id="easy-wp-events-ticket-information">
                <h3>Ticket Prices, Availability, and Items</h3>
                <p class="description">
                    Ticket Prices, Availability and other purchase items are listed below. Manage Tickets
                    available for this event using the editor below.
                    <a href="<?php print admin_url( 'admin-post.php' ); ?>?action=download_event_data&event=<?php print $post->ID; ?>">
                        Download Purchases
                    </a>
                <div class="inputFormRow">
                    <table class="widefat ewpevents-pricing form-group mt-3">
                        <thead>
                        <th scope="col" width="8%"></th>
                        <th scope="col" width="50%">Label</th>
                        <th scope="col">Price</th>
                        <th scope="col">Availability</th>
                        <th scope="col" width="10%">Sold</th>
                        <th scope="col" width="10%">Type</th>
                        </thead>
                        <tbody>
						<?php
						$event_tickets = get_post_meta( $post->ID, 'event_tickets', true );

						foreach ( $event_tickets as $key => $value ): ?>

                            <tr class="single-ticket-row">
                                <td>
                                    <span id="remove_ticket_type" class="dashicons dashicons-remove"></span>
                                    <span class="dashicons dashicons-menu"></span>
                                </td>
                                <td>
                                    <input type="text" name="ticket_label[]" class="form-control mt-1 mb-1"
                                           placeholder="Ticket Name"
                                           value="<?php print $event_tickets[ $key ]['label']; ?>"
                                           autocomplete="off">
                                    <small>Ticket Description</small>
                                    <textarea class="form-control"
                                              name="ticket_description[]"><?php print $event_tickets[ $key ]['ticket_description']; ?></textarea>
                                </td>
                                <td>
                                    <input type="number" name="ticket_price[]" class="form-control mt-1 mb-1"
                                           value="<?php print $event_tickets[ $key ]['price']; ?>" autocomplete="off">
                                </td>
                                <td>
                                    <input type="number" name="ticket_availability[]" class="form-control mt-1 mb-1"
                                           value="<?php print $event_tickets[ $key ]['ticket_availability']; ?>"
                                           autocomplete="off">
                                </td>
                                <td>
                                    <input type="number" name="tickets_sold[]" class="form-control mt-1 mb-1"
                                           autocomplete="off"
                                           value="<?php print intval( $event_tickets[ $key ]['tickets_sold'] ); ?>">
                                </td>
                                <td>
                                    <select name="ticket_type[]" class="form-control">
                                        <option value="ticket" <?php print $event_tickets[ $key ]['ticket_type'] == 'ticket' ? 'selected' : ''; ?>>
                                            Ticket
                                        </option>
                                        <option value="item" <?php print $event_tickets[ $key ]['ticket_type'] == 'item' ? 'selected' : ''; ?>>
                                            Item
                                        </option>
                                    </select>
                                </td>
                            </tr>
						<?php endforeach; ?>
                        <tr class="single-ticket-row">
                            <td>
                                <span class="dashicons dashicons-remove"></span>
                                <span class="dashicons dashicons-menu"></span>
                            </td>
                            <td>
                                <input type="text" name="ticket_label[]" class="form-control mt-1 mb-1"
                                       placeholder="Ticket Name" autocomplete="off">
                                <small>Ticket Description</small>
                                <textarea class="form-control" name="ticket_description[]"></textarea>
                            </td>
                            <td>
                                <input type="number" name="ticket_price[]" class="form-control mt-1 mb-1"
                                       autocomplete="off">
                            </td>
                            <td>
                                <input type="number" name="ticket_availability[]" class="form-control mt-1 mb-1"
                                       autocomplete="off">
                            </td>
                            <td>
                                <input type="number" name="ticket_sold[]" class="form-control mt-1 mb-1"
                                       autocomplete="off">
                            </td>
                            <td>
                                <select name="ticket_type[]" class="form-control">
                                    <option value="ticket">Ticket</option>
                                    <option value="item">Item</option>
                                </select>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <div id="newRow"></div>
                <div class="mt-3">
                    <button id="addRow" type="button" class="btn btn-info">Add new Ticket</button>
                </div>

                <hr class="mt-4"/>
                <h4>Purchase Success Message</h4>
                <p class="description">
                    The content below will control the success message displayed when someone purchases tickets to this
                    event. Template tags includes the following tags ( {{RECEIPT_INFO}}, {{SUB_TOTAL}},
                    {{TICKET_PURCHASE_INFO}} )
                    <textarea
                            name="event_success_message"
                            style="width: 100%; min-height: 300px; margin-top: 1em"><?php print get_post_meta( $post->ID, 'event_success_message', true ); ?></textarea>

            </div>
        </div>
    </div>
    </div>
</form>
