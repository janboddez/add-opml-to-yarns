<?php
/**
 * Handles WP Admin settings pages and the like.
 *
 * @package Yarns_Opml
 */

namespace Yarns_OPML;

use Yarns_Microsub_Channels;

/**
 * Options handler class.
 */
class Import_Handler {
	/**
	 * Interacts with WordPress's Plugin API.
	 *
	 * @since 0.5.0
	 */
	public function register() {
		add_action( 'admin_menu', array( $this, 'create_menu' ) );
		add_action( 'admin_post_add_opml_to_yarns_import', array( $this, 'admin_post' ) );
	}

	/**
	 * Registers the import page.
	 *
	 * @since 0.1.0
	 */
	public function create_menu() {
		add_management_page(
			__( 'Yarns: Import OPML', 'add-opml-to-yarns' ),
			__( 'Yarns: Import OPML', 'add-opml-to-yarns' ),
			'import',
			'add-opml-to-yarns',
			array( $this, 'settings_page' )
		);
	}

	/**
	 * Allow `text/xml` uploads.
	 *
	 * @param  array $mimes List of allowed mime types.
	 * @return array        Updated list.
	 */
	public function upload_mimes( $mimes ) {
		return array_merge( $mimes, array( 'xml' => 'text/xml' ) );
	}

	/**
	 * Echoes the upload form.
	 *
	 * @since 0.1.0
	 */
	public function settings_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Yarns: Import OPML', 'add-opml-to-yarns' ); ?></h1>

			<form action="admin-post.php" method="post" enctype="multipart/form-data">
				<?php wp_nonce_field( 'add-opml-to-yarns-import' ); ?>
				<input type="hidden" name="action" value="add_opml_to_yarns_import">

				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="opml-file"><?php esc_html_e( 'OPML File', 'add-opml-to-yarns' ); ?></label></th>
						<td>
							<input type="file" name="opml_file" id="opml-file" accept="text/xml">
							<p class="description"><?php esc_html_e( 'OPML file to be imported.', 'add-opml-to-yarns' ); ?></p>
							<p class="submit"><?php submit_button( __( 'Import OPML', 'add-opml-to-yarns' ), 'primary', 'submit', false ); ?></p>
						</td>
					</tr>
				</table>
			</form>

			<table class="form-table">
				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Export', 'add-opml-to-yarns' ); ?></th>
					<td>
						<p><a class="button" style="margin-top: -6px;" href="<?php echo esc_url( get_rest_url( null, 'yarns-opml/v1/export' ) ); ?>"><?php esc_html_e( 'Export OPML', 'add-opml-to-yarns' ); ?></a></p>
						<p class="description"><?php esc_html_e( 'Export your existing channels and feeds as OPML.', 'add-opml-to-yarns' ); ?></p>
					</td>
				</tr>
			</table>
		</div>

		<?php
		if ( ! empty( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'import-opml-success' ) ) :
			?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'Import successful!', 'add-opml-to-yarns' ); ?></p>
			</div>
			<?php
		endif;
	}

	/**
	 * `admin-post.php` callback.
	 *
	 * @since 0.3.1
	 */
	public function admin_post() {
		if ( ! current_user_can( 'import' ) ) {
			wp_die( esc_html__( 'You have insufficient permissions to access this page.', 'add-opml-to-yarns' ) );
		}

		if ( ! class_exists( 'Yarns_Microsub_Channels' ) ) {
			wp_die( esc_html__( 'The Yarns plugin is not installed.', 'add-opml-to-yarns' ) );
		}

		if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'add-opml-to-yarns-import' ) ) {
			wp_die( esc_html__( 'This page should not be accessed directly.', 'import-bookmarks' ) );
		}

		if ( empty( $_FILES['opml_file'] ) ) {
			wp_die( esc_html__( 'Something went wrong uploading the file.', 'import-bookmarks' ) );
		}

		add_filter( 'upload_mimes', array( $this, 'upload_mimes' ) );

		// Let WordPress handle the uploaded file.
		$uploaded_file = wp_handle_upload(
			$_FILES['opml_file'], // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			array(
				'test_form' => false,
			)
		);

		if ( ! empty( $uploaded_file['error'] ) && is_string( $uploaded_file['error'] ) ) {
			// `wp_handle_upload()` returned an error.
			wp_die( esc_html( $uploaded_file['error'] ) );
		} elseif ( empty( $uploaded_file['file'] ) || ! is_string( $uploaded_file['file'] ) ) {
			wp_die( esc_html__( 'Something went wrong uploading the file.', 'import-bookmarks' ) );
		}

		global $wp_filesystem;

		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		if ( ! $wp_filesystem->exists( $uploaded_file['file'] ) ) {
			wp_die( esc_html__( 'Something went wrong uploading the file.', 'import-bookmarks' ) );
		}

		$opml = $wp_filesystem->get_contents( $uploaded_file['file'] );

		// Run the actual importer.
		$parser = new OPML_Parser();
		$feeds  = $parser->parse( $opml, true );

		// `$feeds` should now represent a multidimensional array.
		if ( empty( $feeds ) || ! is_array( $feeds ) ) {
			wp_die( esc_html__( 'No feeds found.', 'add-opml-to-yarns' ) );
		}

		$data = Yarns_Microsub_Channels::get( true );

		if ( empty( $data['channels'] ) || ! is_array( $data['channels'] ) ) {
			// Oops.
			$data['channels'] = array();
		}

		// Current Yarns feeds.
		$current_feeds = array();

		foreach ( $data['channels'] as $channel ) {
			foreach ( $channel['items'] as $item ) {
				$current_feeds[] = $item['url'];
			}
		}

		$current_feeds = array_unique( $current_feeds );

		foreach ( $feeds as $feed ) {
			if ( false === filter_var( $feed['feed'], FILTER_VALIDATE_URL ) ) {
				// Invalid feed URL.
				continue;
			}

			$current_channel = Yarns_Microsub_Channels::add( ! empty( $feed['category'] ) ? $feed['category'] : 'OPML' ); // Returns channel, also if it already exists.

			// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
			// Yarns_Microsub_Channels::follow( $current_channel->uid, $feed['feed'] ); // This would poll every single feed and time out on many servers.
			$this->follow( $current_channel->uid, $feed['feed'] );
		}

		// On sites running the IndieWeb plugin, the Yarns settings page has a
		// different URL.
		// phpcs:disable
		// wp_safe_redirect(
		// 	esc_url_raw(
		// 		add_query_arg(
		// 			array(
		// 				'page' => 'yarns_microsub_options',
		// 			),
		// 			admin_url( 'options-general.php' )
		// 		)
		// 	)
		// );
		// exit;
		// phpcs:enable

		wp_safe_redirect(
			esc_url_raw(
				add_query_arg(
					array(
						'page'     => 'add-opml-to-yarns',
						'_wpnonce' => wp_create_nonce( 'import-opml-success' ),
					),
					admin_url( 'tools.php' )
				)
			)
		);
		exit;
	}

	/**
	 * Adds a new source to a channel.
	 *
	 * Using this rather than Yarns' built-in function to avoid polling too many
	 * sources at once.
	 *
	 * @param  string $query_channel Channel UID.
	 * @param  string $url           Source URL.
	 * @return void
	 */
	private function follow( $query_channel, $url ) {
		$channels = get_option( 'yarns_channels' ); // Returns `false` if the option doesn't exist.

		if ( empty( $channels ) ) {
			return;
		}

		$channels = @json_decode( $channels, true ); // WordPress automatically unserializes arrays; Yarns used JSON encoding, though.

		if ( ! is_array( $channels ) ) {
			return;
		}

		foreach ( $channels as $key => $channel ) {
			if ( $channel['uid'] !== $query_channel ) {
				// Not the channel we're looking for. Skip.
				continue;
			}

			if ( empty( $channel['items'] ) ) {
				// Channel is empty.
				$channels[ $key ]['items'] = array();
			}

			// Check if the subscription exists in this channel.
			foreach ( $channels[ $key ]['items'] as $item ) {
				if ( $item['url'] === $url ) {
					// Already following this feed. Nothing to do.
					return;
				}
			}

			// Add the new follow to the selected channel.
			$channels[ $key ]['items'][] = array(
				'type' => 'feed',
				'url'  => $url,
			);

			// To do: sort items alphabetically?

			update_option( 'yarns_channels', wp_json_encode( $channels ) );
			return; // Done!
		}
	}
}
