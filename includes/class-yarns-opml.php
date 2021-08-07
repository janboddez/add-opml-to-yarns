<?php
/**
 * Defines the main plugin class.
 *
 * @package Yarns_Opml
 */

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
		// Register a new REST API route (it's that easy).
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

		ob_start();
		include dirname( __FILE__ ) . '/../templates/opml.php';
		ob_end_flush();

		exit;
	}
}
