<?php
/**
 * Event Metabox Logic
 */
add_action( 'add_meta_boxes', 'ewpet_add_events_metaboxes' );
function ewpet_add_events_metaboxes() {
	add_meta_box(
		'wpt_events_location',
		'Event Information',
		'wpt_events_location',
		'ewp_events',
		'normal',
		'high'
	);
}

function ewp_events_save_meta_box( $post_id ) {

	if ( $_POST['post_type'] != 'ewp_events' ) {
		return $post_id;
	}

	update_post_meta( $post_id, 'venue_name', $_POST['venue_name'] );
	update_post_meta( $post_id, 'venue_address', $_POST['venue_address'] );
	update_post_meta( $post_id, 'start_date', $_POST['start_date'] );
	update_post_meta( $post_id, 'end_date', $_POST['end_date'] );

	// Take the ticket prices and put it into raw and formatted format
	$event_tickets = array();
	for ( $x = 0; $x < count( array_filter( $_POST['ticket_label'] ) ); $x ++ ) {
		if ( empty( $_POST['ticket_label'][ $x ] ) ) {
			continue;
		}

		$event_tickets[ ewp_events_sanatize_event_title( $_POST['ticket_label'][ $x ] ) ] = array(
			'id'                  => ewp_events_sanatize_event_title( $_POST['ticket_label'][ $x ] ),
			'label'               => $_POST['ticket_label'][ $x ],
			'price'               => $_POST['ticket_price'][ $x ],
			'ticket_availability' => $_POST['ticket_availability'][ $x ],
			'tickets_sold'        => $_POST['tickets_sold'][ $x ],
			'ticket_description'  => $_POST['ticket_description'][ $x ],
			'ticket_type'         => $_POST['ticket_type'][ $x ]
		);
	}

	// Formatted tickets
	update_post_meta( $post_id, 'event_tickets', $event_tickets );

	// Raw tickets incas someone wants these values
	update_post_meta( $post_id, 'ticket_labels', $_POST['ticket_label'] );
	update_post_meta( $post_id, 'ticket_prices', $_POST['ticket_price'] );
	update_post_meta( $post_id, 'ticket_availability', $_POST['ticket_availability'] );
	update_post_meta( $post_id, 'ticket_description', $_POST['ticket_description'] );
	update_post_meta( $post_id, 'ticket_type', $_POST['ticket_type'] );

	update_post_meta( $post_id, 'event_success_message', $_POST['event_success_message'] );
}

add_action( 'save_post', 'ewp_events_save_meta_box' );

function wpt_events_location() {
	require_once( dirname( __FILE__ ) . '/admin-metabox-template.php' );
}

/**
 * Event Purchase Export Functionality
 * Checks for the user to be an admin and then compiles the sales of an event.
 */
add_action( 'admin_post_download_event_data', 'ewp_event_download_event_purchase_history' );
function ewp_event_download_event_purchase_history() {

	// @todo Add WP NONCE Security Check
	// @todo Add human readable formatting for tickets on the export
	if ( ! is_admin() ) {
		wp_die( 'You do not have permission to access this data' );
	}

	$event = intval( $_GET['event'] );

	global $wpdb;
	$prepare = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ewp_event_orders WHERE event_id=%d", array( $event ) );
	$results = $wpdb->get_results( $prepare, ARRAY_A );

	$filename = 'event_purchase_export';
	$date     = date( "Y-m-d H:i:s" );

	$fp = fopen( 'php://output', 'w' );
	fputcsv( $fp, array(
		'ID',
		'Event ID',
		'First Name',
		'Last Name',
		'Email',
		'Address',
		'City',
		'State',
		'Zipcode',
		'Sub Total',
		'Guest Names',
		'Cart Contents',
		'Charge ID',
		'Tickets',
		'Items'
	) );

	foreach ( $results as $key => $value ) {
		$prepare_tickets = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ewp_event_tickets WHERE charge_id=%s", array( $value['charge_id'] ) );
		$tickets         = $wpdb->get_results( $prepare_tickets );

		$prepare_items = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ewp_event_items WHERE charge_id=%s", array( $value['charge_id'] ) );
		$items         = $wpdb->get_results( $prepare_items );

		$ticket_info = '';
		foreach ( $tickets as $ticket ) {
			$ticket_info .= '#' . $ticket->id . ' ' . $ticket->ticket_name . "\n";
		}

		$item_info = '';
		foreach ( $items as $item ) {
			$item_info .= $item->item_name . "\n";
		}

		$modified_values = array(
			$value['id'],
			$value['event_id'],
			$value['first_name'],
			$value['last_name'],
			$value['email'],
			$value['address'],
			$value['city'],
			$value['state'],
			$value['zipcode'],
			'$' . number_format( $value['sub_total'], 2 ),
			$value['name_of_guests'],
			$value['cart_contents'],
			$value['charge_id'],
			$ticket_info,
			$item_info
		);
		fputcsv( $fp, $modified_values );
	}
	header( "Pragma: public" );
	header( "Expires: 0" );
	header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
	header( "Cache-Control: private", false );
	header( 'Content-Type: text/csv; charset=utf-8' );
	// header("Content-Type: application/octet-stream");
	header( "Content-Disposition: attachment; filename=\"" . $filename . " " . $date . ".csv\";" );
	// header('Content-Disposition: attachment; filename=lunchbox_orders.csv');
	header( "Content-Transfer-Encoding: binary" );
	exit;
	// Handle request then generate response using echo or leaving PHP and using HTML
}
