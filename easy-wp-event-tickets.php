<?php
/**
 * Plugin Name: Easy WP Event Tickets
 * Plugin URI: http://justin-greer.cm
 * Version: 1.0.0
 * Description: Create Events and Sell Customizable Tickets
 * Author: Justin Greer
 * Author URI: http://justin-greer.com
 * License: GPL2
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 *
 * @author  Justin Greer <justin@justin-greer.com>
 * @package Easy WP Event Tickets
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class EWPET {

	/** @var string current plugin version */
	public static $version = '1.0.0';

	/** @var object container instance for plugin */
	public static $instance = null;

	/** @var array array of notices for the plugin to display */
	public $notices = array();

	/** @var array plugin default settings */
	public $defualt_settings = array(
		'enabled' => true,
	);

	public $session;

	/** construct method */
	public function __construct() {
		if ( ! defined( 'EWPET_ABSPATH' ) ) {
			define( 'EWPET_ABSPATH', dirname( __FILE__ ) );
		}

		if ( ! defined( 'EWPET_URI' ) ) {
			define( 'EWPET_URI', plugins_url( '/', __FILE__ ) );
		}

		add_action( 'wp_loaded', array( $this, 'register_scripts' ) );
		add_action( 'wp_loaded', array( $this, 'register_styles' ) );

		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'init', array( $this, 'register_query_vars' ) );
		add_filter( 'template_include', array( $this, 'template_redirect_intercept' ), 100 );
	}

	/**
	 * Load the instance of the plugin
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();

			self::$instance->includes();
			self::$instance->install();
			self::$instance->session = new EWP_Events_Session();
		}

		return self::$instance;
	}

	/**
	 * Plugin Includes
	 * @return void
	 */
	public static function includes() {
		require_once( dirname( __FILE__ ) . '/includes/sessions.php' );
		require_once( dirname( __FILE__ ) . '/includes/gateway.php' );
		require_once( dirname( __FILE__ ) . '/includes/functions.php' );
		require_once( dirname( __FILE__ ) . '/includes/metaboxes.php' );
		require_once( dirname( __FILE__ ) . '/includes/gateways/stripe.php' );

		/** include the ajax class if DOING_AJAX is defined */
		if ( defined( 'DOING_AJAX' ) ) {
			require_once( dirname( __FILE__ ) . '/includes/ajax.php' );
		}

		/** admin options page */
		//require_once( dirname( __FILE__ ) . '/admin/admin-options.php' );
	}

	/** register dependant styles */
	public function register_styles() {
		//wp_register_style( 'wpvw_admin', plugins_url( '/assets/css/admin.css', __FILE__ ) );
	}

	/** register dependant scripts */
	public function register_scripts() {
		//wp_register_script( 'wpvw_admin', plugins_url( '/assets/js/admin.js', __FILE__ ) );
	}

	/**
	 * [setup description]
	 * @return void
	 */
	public function setup() {
		$options = get_option( 'ewpet_options' );
		//if ( ! isset( $options["enabled"] ) ) {
		//	update_option( "vw_options", $this->defualt_settings );
		//}
	}

	public function register_post_type() {
		register_post_type(
			'ewp_events',
			array(
				'labels'       => array(
					'name'          => __( 'Events' ),
					'singular_name' => __( 'Event' ),
				),
				'public'       => true,
				'has_archive'  => true,
				'rewrite'      => array( 'slug' => 'events' ),
				'show_in_rest' => true,
			)
		);
	}

	function register_query_vars() {
		$this->register_rewrites();

		global $wp;
		$wp->add_query_var( 'ewpevents' );
	}


	function register_rewrites() {
		add_rewrite_rule( '^ewpevents/(.+)', 'index.php?ewpevents=$matches[1]', 'top' );
	}

	function template_redirect_intercept( $template ) {
		global $wp_query;

		if ( $wp_query->get( 'ewpevents' ) ) {

			if ( $wp_query->get( 'ewpevents' ) == 'checkout' ) {
				define( 'DOING_EWPEVENT_CHECHOUT', true );
				require_once dirname( __FILE__ ) . '/includes/templates/checkout.php';
				exit;
			}

			if ( $wp_query->get( 'ewpevents' ) == 'success' ) {
				define( 'DOING_EWPEVENT_CHECHOUT', true );
				require_once dirname( __FILE__ ) . '/includes/templates/success.php';
				exit;
			}
		}

		return $template;
	}

	public function install() {

		global $wpdb;
		$charset_collate = '';

		if ( ! empty( $wpdb->charset ) ) {
			$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
		}

		if ( ! empty( $wpdb->collate ) ) {
			$charset_collate .= " COLLATE {$wpdb->collate}";
		}

		$sql1 = "
			CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ewp_event_orders (
			id 					  INT UNSIGNED NOT NULL AUTO_INCREMENT,
	        event_id              INT		   NOT NULL,
	        first_name         	  VARCHAR(255) NOT NULL,
	        last_name             VARCHAR(255) NOT NULL,
	        email           	  VARCHAR(255) NOT NULL,
			address           	  VARCHAR(255) NOT NULL,    
	        city                  VARCHAR(255) NOT NULL,
	        state                 VARCHAR(32) NOT NULL,
	        zipcode               VARCHAR(32) NOT NULL,
	        sub_total             VARCHAR(32) NOT NULL,
			cart_contents		  LONGTEXT,
			charge_id 			  VARCHAR(255) NOT NULL,
	        PRIMARY KEY (id)
	      	);
		";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql1 );
	}

}

add_action( 'admin_print_scripts-post-new.php', 'ewp_events_css_and_js', 11 );
add_action( 'admin_print_scripts-post.php', 'ewp_events_css_and_js', 11 );
add_action( 'wp_enqueue_scripts', 'ewp_events_css_and_js' );

function ewp_events_css_and_js() {
	global $post_type;
	if ( 'ewp_events' == $post_type || defined( 'DOING_EWPEVENT_CHECHOUT' ) ) {
		wp_enqueue_style( 'ewp_events_boot_css', plugins_url( 'includes/assets/css/bootstrap.css', __FILE__ ) );
		wp_enqueue_script( 'ewp_events_boot_js', plugins_url( 'includes/assets/js/bootstrap.js', __FILE__ ) );
		//wp_enqueue_style('ewp_events_font-awesome', plugins_url('includes/assets/css/font-awesome-all.min.css', __FILE__));
	}
}

require 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/user-name/repo-name/',
	__FILE__,
	'unique-plugin-or-theme-slug'
);

//Set the branch that contains the stable release.
$myUpdateChecker->setBranch('stable-branch-name');

//Optional: If you're using a private repository, specify the access token like this:
$myUpdateChecker->setAuthentication('your-token-here');

function EWPET() {
	return EWPET::instance();
}

$GLOBAL['EWPET'] = EWPET();
