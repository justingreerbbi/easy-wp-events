<?php
/**
 * Stripe Gateway Logic for Checkout
 */

class EWP_Event_Stripe_Gateway {

	private $api_pub_key = 'pk_test_rByFEf6MAqwryISgnzBU1AQd';
	private $api_secret_key = 'sk_test_P9tqT4FFC1XEbwOV8VB4U1cw';

	public $api_endpoint = 'https://api.stripe.com/v1/';

	public $last_error = '';
	public $last_error_message = '';

	public function createCharge( $post, $cart ) {

		$plugin_options = get_option( 'ewp_events_options' );

		$event_ticket_types = get_post_meta( $cart['event'], 'event_tickets', true );

		$exp = explode( "/", $_POST['card']['exp'], 2 );

		$card_data = array(
			'card[number]'        => $_POST['card']['num'],
			'card[exp_month]'     => $exp[0],
			'card[exp_year]'      => $exp[1],
			'card[cvc]'           => $_POST['card']['cvc'],
			'card[name]'          => $_POST['card']['name'],
			'card[address_line1]' => $_POST['address'],
			'card[address_city]'  => $_POST['city'],
			'card[address_state]' => $_POST['state'],
			'card[address_zip]'   => $_POST['zipcode']
		);

		$response = wp_remote_post( 'https://api.stripe.com/v1/tokens', array(
			'body'    => $card_data,
			'headers' => array(
				'Authorization' => 'Bearer ' . $plugin_options['ewp_events_secret_stripe_secret_key']
			),
		) );

		//print '<pre>';
		$token_response = json_decode( $response['body'] );
		/**
		 * stdClass Object
		 * (
		 * [id] => tok_1J2KEqEgjp3snQ78yMn5rXSk
		 * [object] => token
		 * [card] => stdClass Object
		 * (
		 * [id] => card_1J2KEpEgjp3snQ78mPj3zEul
		 * [object] => card
		 * [address_city] => Barberton
		 * [address_country] =>
		 * [address_line1] => 559 Jefferson Avenue
		 * [address_line1_check] => unchecked
		 * [address_line2] =>
		 * [address_state] => OH
		 * [address_zip] => 44203
		 * [address_zip_check] => unchecked
		 * [brand] => Visa
		 * [country] => DE
		 * [cvc_check] => unchecked
		 * [dynamic_last4] =>
		 * [exp_month] => 9
		 * [exp_year] => 2023
		 * [fingerprint] => PFE7kEn1K2HQTIl1
		 * [funding] => credit
		 * [last4] => 3184
		 * [metadata] => stdClass Object
		 * (
		 * )
		 *
		 * [name] => Test Card
		 * [tokenization_method] =>
		 * )
		 *
		 * [client_ip] => 98.157.96.152
		 * [created] => 1623694220
		 * [livemode] =>
		 * [type] => card
		 * [used] =>
		 * )
		 */

		// If there is no error, let's display
		if ( ! isset( $token_response->error ) ) {

			$sub_total = bcmul( $_POST['sub_total'], 100 );

			// Let's Make the Charge as required for the purchase
			$charge = $this->doCharge( array(
				'amount'      => $sub_total,
				'currency'    => 'usd',
				'source'      => $token_response->id,
				'description' => 'This needs to be changed to be descriptive of the event'
			) );

			if ( ! isset( $charge->error ) ) {
				global $wpdb;
				$insert_id = $wpdb->insert( $wpdb->prefix . 'ewp_event_orders', array(
					'event_id'       => $cart['event'],
					'first_name'     => $_POST['firstname'],
					'last_name'      => $_POST['lastname'],
					'email'          => $_POST['email'],
					'address'        => $_POST['address'],
					'city'           => $_POST['city'],
					'state'          => $_POST['state'],
					'zipcode'        => $_POST['zipcode'],
					'sub_total'      => $_POST['sub_total'],
					'cart_contents'  => maybe_serialize( $cart['contents'] ),
					'name_of_guests' => $_POST['name_of_guests'],
					'charge_id'      => $charge->id
				) );

				foreach ( $cart['contents'] as $type ) {
					$num_of_tickets = $type['tickets_sold'];
					$ticket_type    = $type['name'];
					for ( $x = 0; $x < $num_of_tickets; $x ++ ) {
						$insert_id = $wpdb->insert( $wpdb->prefix . 'ewp_event_tickets', array(
							'event_id'     => $cart['event'],
							'ticket_name'  => $ticket_type,
							'ticket_price' => '',
							'email'        => $_POST['email'],
							'charge_id'    => $charge->id
						) );
					}
				}

				/**
				 * Delete the transient
				 */
				delete_transient( 'wp_easy_event_checkout_' . $_GET['cart'] );

				// @todo Add logic to prevent the charge from happening again

				/**
				 * Deduct the tickets sold from the tickets remaining.
				 */
				foreach ( $cart['contents'] as $item ) {

					// @todo Check to ensure there is no sales of tickets that are oversold. and limit sales to only what is open for purchase.
					$event_ticket_types[ $item['id'] ]['ticket_availability'] = ( $event_ticket_types[ $item['id'] ]['ticket_availability'] - $item['tickets_sold'] );

					// Update the tickets sold
					$event_ticket_types[ $item['id'] ]['tickets_sold'] = intval( $event_ticket_types[ $item['id'] ]['tickets_sold'] ) + intval( $item['tickets_sold'] );
				}
				update_post_meta( $cart['event'], 'event_tickets', $event_ticket_types );

				// GET CUSTOM SUCCESS MESSAGE FOR THE EMAIL
				$custom_success_message = get_post_meta( $cart['event'], 'event_success_message', true );

				$receipt = '<strong>Event</strong>: ' . get_the_title( $cart['event'] ) . '<br/>';
				$receipt .= '<strong>Purchaser</strong>: ' . $_POST['firstname'] . ' ' . $_POST['lastname'] . '<br/>';
				$receipt .= '<strong>Email</strong>: ' . $_POST['email'] . '<br/>';
				$receipt .= '<strong>Charge ID</strong>: ' . $charge->id . '<br/>';


				/**
				 * Send an email to the purchaser with the info
				 */
				$email_content = file_get_contents( EWPET_ABSPATH . '/includes/templates/email/success.html' );

				// Dynamic Email Content
				$ticket_list = '';

				global $wpdb;
				$prepare_tickets = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ewp_event_tickets WHERE charge_id=%s", array( $charge->id ) );
				$tickets         = $wpdb->get_results( $prepare_tickets );
				foreach ( $tickets as $item ) {
					$ticket_list .= '<li>Ticket #' . $item->id . ' - ' . $item->ticket_name . ' (x1)</li>';
				}

				$custom_success_message = str_replace( '{{TICKET_PURCHASE_INFO}}', $ticket_list, $custom_success_message );
				$custom_success_message = str_replace( '{{SUB_TOTAL}}', '<strong>Total:</strong> $' . $_POST['sub_total'], $custom_success_message );
				$custom_success_message = str_replace( '{{RECEIPT_INFO}}', $receipt, $custom_success_message );

				// Add the content to the template template success.html
				$email_content = str_replace( '{{SUCCESS_MESSAGE}}', $custom_success_message, $email_content );

				$content_type = function () {
					return 'text/html';
				};
				add_filter( 'wp_mail_content_type', $content_type );
				//wp_mail( , 'New Ticket Sale', $email_content );
				wp_mail( $_POST['email'], 'Ticket Sale Information', $email_content );
				remove_filter( 'wp_mail_content_type', $content_type );

				wp_redirect( add_query_arg(
					array(
						'ewpevents' => 'success',
						'purchase'  => $charge->id,
					),
					site_url()
				) );
				exit;

			} else {

				$respond = array(
					'error'         => $charge->error,
					'error_message' => $charge->error->message,
				);

				return (object) $respond;
			}

		} else {
			$this->last_error_message = $token_response->error->message;

			$respond = array(
				'error'         => $token_response->error,
				'error_message' => $token_response->error->message
			);

			return (object) $respond;
		}
	}

	/**
	 * Preform the charge using the order details. Details should be passed to this method only by the createCharge
	 * method and nothing else. All validation and prep happens there.
	 *
	 * @param $order
	 *
	 * @return false|mixed
	 */
	public function doCharge( $order ) {

		$plugin_options = get_option( 'ewp_events_options' );

		$charge = wp_remote_post( 'https://api.stripe.com/v1/charges', array(
			'body'    => $order,
			'headers' => array(
				'Authorization' => 'Bearer ' . $plugin_options['ewp_events_secret_stripe_secret_key'],
			),
		) );

		return json_decode( wp_remote_retrieve_body( $charge ) );
	}
}
