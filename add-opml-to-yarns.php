<?php
/**
 * Plugin Name:       Add OPML to Yarns
 * Description:       Add (limited) OPML support to the Yarns Microsub server
 * GitHub Plugin URI: https://github.com/janboddez/add-opml-to-yarns
 * Author:            Jan Boddez
 * Author URI:        https://jan.boddez.net/
 * License:           GNU General Public License v3
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Textdomain:        yarns-opml
 * Version:           0.1
 *
 * @author  Jan Boddez <jan@janboddez.be>
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 * @package Yarns_Opml
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require dirname( __FILE__ ) . '/includes/class-yarns-opml.php';

$yarns_opml = Yarns_Opml::get_instance();
$yarns_opml->register();
