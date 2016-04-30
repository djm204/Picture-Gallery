<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              not available
 * @since             1.0.0
 * @package           Picture_Gallery
 *
 * @wordpress-plugin
 * Plugin Name:       Picture Gallery
 * Plugin URI:        http://wordpresstest-tpascal.rhcloud.com/
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Thomas Pascal
 * Author URI:        not available
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       picture-gallery
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
  die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-picture-gallery-activator.php
 */
function activate_picture_gallery() {
  require_once plugin_dir_path( __FILE__ ) . 'includes/class-picture-gallery-activator.php';
  Picture_Gallery_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-picture-gallery-deactivator.php
 */
function deactivate_picture_gallery() {
  require_once plugin_dir_path( __FILE__ ) . 'includes/class-picture-gallery-deactivator.php';
  Picture_Gallery_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_picture_gallery' );
register_deactivation_hook( __FILE__, 'deactivate_picture_gallery' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-picture-gallery.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_picture_gallery() {

  $plugin = new Picture_Gallery();
  $plugin->run();

  add_action( 'admin_menu', 'picture_gallery_custom_admin_menu' );
}

function picture_gallery_custom_admin_menu() {
    add_menu_page(
        'Picture Gallery',
        'Picture Gallery',
        'manage_options',
        'picture-gallery',
        'wporg_options_page'
    );
}

function wporg_options_page() {
    include 'admin/partials/picture-gallery-admin-display.php';
}


function be_attachment_field_credit( $form_fields, $post ) {

      $form_fields['Category'] = array(

          'label' => 'Image Category',

          'input' => 'text',

          'value' => get_post_meta( $post->ID, 'Category', true ),

          'helps' => 'Will be displayed under appropriate gallery category if selected');

      return $form_fields;
}

add_filter( 'attachment_fields_to_edit', 'be_attachment_field_credit', 10, 2 );

function be_attachment_field_credit_save( $post, $attachment ) {
      if( isset( $attachment['Category'] ) )
          update_post_meta( $post['ID'], 'Category', $attachment['Category'] );
   
      return $post;
}


add_filter( 'attachment_fields_to_save', 'be_attachment_field_credit_save', 10, 2 );


run_picture_gallery();
?>