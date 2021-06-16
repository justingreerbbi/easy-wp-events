<?php
/**
 * Core Plugin Functions
 */

add_action( 'wp_enqueue_scripts', 'ewp_events_frontend_script' );
function ewp_events_frontend_script() {
	wp_enqueue_script( 'ewp-events-stripe', 'https://js.stripe.com/v3/', array( 'jquery' ), null, true );
	wp_enqueue_script( 'ewp-events-script', EWPET_URI . '/includes/assets/js/frontend-scripts.js', array( 'jquery' ), null, true );
	$variables = array(
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
	);
	wp_localize_script( 'ewp-events-script', 'ewp_events', $variables );
}

add_filter( 'template_include', 'use_our_page_template', 99 );
function use_our_page_template( $template ) {
	if ( is_singular( 'ewp_events' ) ) {
		$new_template = EWPET_ABSPATH . '/includes/templates/ewp-events-single.php';

		return $new_template;
	}

	return $template;
}


add_action( 'wp_ajax_ewp_events_create_checkout_session', 'ewp_events_update_cart_ajax' );
add_action( 'wp_ajax_nopriv_ewp_events_create_checkout_session', 'ewp_events_update_cart_ajax' );
function ewp_events_update_cart_ajax() {
	parse_str( $_POST['formData'], $request );
	$event_id = intval( $request['event_id'] );

	// verifiy the nonce for security
	if ( ! wp_verify_nonce( $request['ewp_events_ticket_nonce_check'], 'ewp_events_ticket_nonce_check_' . $event_id ) ) {
		//exit( -1 );
	}

	// Get all of the possible tickets avalaible
	$event_tickets = get_post_meta( $event_id, 'event_tickets', true );

	// We look for the main tickets and calulate everything up into the total checkout
	$cart = array();
	foreach ( $event_tickets as $event ) {
		if ( empty( $request["ticket_type_{$event['id']}"] ) ) {
			continue;
		}

		if ( ! empty( $event['id'] ) ) {
			$tickets_sold       = $request[ 'ticket_type_' . $event['id'] ];
			$ticket_total       = $request[ 'price_' . $event['id'] ] * $tickets_sold;
			$cart['contents'][] = array(
				'id'           => $event['id'],
				'name'         => $event['label'],
				'tickets_sold' => $tickets_sold,
				'total'        => $ticket_total,
			);

			//$cart['total'] = number_format( $cart['total'] + $ticket_total, 2 );
		}

		$cart['event'] = $event_id;
	}

	$cart_total = 0.00;
	foreach ( $cart['contents'] as $item ) {
		//print $item['total']. '\n';
		$cart_total = $cart_total + $item['total'];
	}

	$cart['total'] = $cart_total;

	if ( empty( $cart ) ) {
		print json_encode(
			array(
				'error'             => true,
				'error_description' => 'Please Choose at Least One Ticket Type',
				'cart'              => $cart,
			)
		);
		exit;
	}

	$cart_key = wp_generate_password( 32, false );
	set_transient( 'wp_easy_event_checkout_' . $cart_key, $cart, 3600 );

	// Build the redirect to the cart with the stored cart key
	// @todo Add nonce to this redirect for verification
	$wp_easy_event_checkout_redirect = apply_filters(
		'wp_easy_events_redirect_url',
		add_query_arg(
			array(
				'ewpevents' => 'checkout',
				'cart'      => $cart_key,
			),
			site_url()
		)
	);

	// Take the values of the form and create a checkout in the DB.
	// Then redirect the user to the check out with the checkout ID/Session
	print json_encode(
		array(
			'checkout_url' => $wp_easy_event_checkout_redirect,
		)
	);
	wp_die();
}

function ewp_events_sanatize_event_title( $title ) {
	$wp_sanatize = sanitize_title( $title );

	return str_replace( '-', '_', $wp_sanatize );
}

function ewp_events_currency_convert( $value ) {
	return (int) (string) ( (float) preg_replace( "/[^0-9.]/", "", $value ) * 100 );
}

/**
 * Only orks on the cart page
 */
function ewp_event_cart_total() {
	$cart = get_transient( 'wp_easy_event_checkout_' . $_GET['cart'] );

	$cart_total = 0.00;
	foreach ( $cart['contents'] as $item ) {
		$cart_total = $cart_total + $item['total'];
	}

	return number_format( $cart_total, 2 );
}

function ewp_event_price( $price ) {
	return '$' . number_format( $price, 2 );
}

function ewp_field( $field, $type = "post" ) {

	if ( $type == 'post' ) {
		if ( ! isset( $_POST[ $field ] ) ) {
			print '';
		}
		$field = sanitize_text_field( $_POST[ $field ] );
	} else {
		if ( ! isset( $_GET[ $field ] ) ) {
			print '';
		}
		$field = sanitize_text_field( $_GET[ $field ] );
	}

	print $field;
}
