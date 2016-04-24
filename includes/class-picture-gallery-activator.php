<?php

/**
 * Fired during plugin activation
 *
 * @link       not available
 * @since      1.0.0
 *
 * @package    Picture_Gallery
 * @subpackage Picture_Gallery/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Picture_Gallery
 * @subpackage Picture_Gallery/includes
 * @author     Thomas Pascal <ashtom@mymts.net>
 */
class Picture_Gallery_Activator {

	/**
	 * Activates the Plugin and writes picture category database
	 *
	 * 
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		global $wpdb;

   		$table_name = $wpdb->prefix . "picture_category";

   		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		  name varchar(35) NOT NULL,
		  UNIQUE KEY id (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

}
