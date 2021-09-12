<?php
/**
 * Defines the main plugin class.
 *
 * @package Yarns_Opml
 */

namespace Yarns_OPML;

use WP_Error;
use Yarns_Microsub_Channels;

/**
 * Main plugin class.
 */
class Yarns_Opml {
	/**
	 * Single class instance.
	 *
	 * @since 0.1
	 *
	 * @var Yarns_Opml Single class instance.
	 */
	private static $instance;

	/**
	 * Class constructor.
	 *
	 * @since 0.1
	 */
	private function __construct() {
		// Private constructor.
		$import_handler = new Import_Handler();
		$import_handler->register();
	}

	/**
	 * Returns the single instance of this class.
	 *
	 * @since 0.1
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Registers hook callbacks.
	 *
	 * @since 0.1
	 */
	public function register() {
		// Register a new REST API route.
		add_action(
			'rest_api_init',
			function() {
				register_rest_route(
					'yarns-opml/v1',
					'/export',
					array(
						'methods'             => 'GET',
						'callback'            => array( $this, 'export' ),
						'permission_callback' => '__return_true',
					)
				);
			}
		);

		add_action( 'current_screen', array( $this, 'current_screen' ) );
	}

	/**
	 * Yarns' settings page has an empty title on sites that aren't running the
	 * IndieWeb plugin. This addresses that.
	 *
	 * @todo Remove whenever the original issue is fixed.
	 *
	 * @param  \WP_Screen $current_screen Current admin screen.
	 * @return void
	 */
	public function current_screen( $current_screen ) {
		if ( empty( $current_screen->id ) || 'settings_page_yarns_microsub_options' !== $current_screen->id ) {
			return;
		}

		add_filter(
			'admin_title',
			function( $admin_title ) {
				if ( preg_match( '~^Yarns~i', $admin_title ) ) {
					return $admin_title;
				}

				return 'Yarns Micropub Server ' . trim( $admin_title );
			}
		);
	}

	/**
	 * Outputs OPML.
	 *
	 * @since 0.1
	 */
	public function export() {
		if ( ! class_exists( 'Yarns_Microsub_Channels' ) ) {
			return new WP_Error(
				'not_supported',
				'Not supported',
				array( 'status' => 501 ) // "Not implemented." Close enough.
			);
		}

		$data = Yarns_Microsub_Channels::get( true );

		if ( empty( $data['channels'] ) || ! is_array( $data['channels'] ) ) {
			return new WP_Error(
				'not_found',
				'No channels found',
				array( 'status' => 404 )
			);
		}

		$channels = $data['channels'];

		// Yes, we're using a _JSON_ API to send OPML (which is XML).
		header( 'Content-Type: application/xml; charset=' . get_option( 'blog_charset' ) );

		if ( false !== strpos( (string) wp_get_referer(), 'page=add-opml-to-yarns' ) ) {
			// If we got here via the import page, "force download" the file.
			header( 'Content-Disposition: attachment; filename=export.xml' );
		}

		ob_start();
		include dirname( __FILE__ ) . '/../templates/opml.php';
		ob_end_flush();

		exit;
	}
}
