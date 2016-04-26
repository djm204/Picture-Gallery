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
    add_options_page(
        'Picture Gallery',
        'Picture Gallery',
        'manage_options',
        'wporg-plugin',
        'wporg_options_page'
    );
}

/*
  Option page - admin backend to allow users to upload images and either make new categories or 
  assign current ones to upoaded images.
*/
function wporg_options_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . "picture_category";

    $query_categories = $wpdb->get_results( 'SELECT name FROM ' . $table_name);

    $images = array();
    foreach ( $query_categories->posts as $image ) {
        $images[] = $image;
    }

    //If the category is set and it is a new category, it will write to the database
    if(isset($_POST['category']))
    {
        //Escapes for testing purposes
        ?>
        <div>category is set and is <?= $_POST['category'] ?></div>
        <?php

        //File upload testing
        $uploaddir = wp_upload_dir();
        $file = $_FILES["fileToUpload"]["name"];
        $uploadfile = $uploaddir['path'] . '/' . basename( $file );
        
        wp_handle_upload($_FILES["fileToUpload"]);
        $filename = basename( $uploadfile );

        $wp_filetype = wp_check_filetype(basename($filename), null );

        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
            'post_content' => '',
            'post_status' => 'inherit',
            'menu_order' => $_i + 1000
        );
        $attach_id = wp_insert_attachment( $attachment, $uploadfile );

        // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
        require_once( ABSPATH . 'wp-admin/includes/image.php' );

        // Generate the metadata for the attachment, and update the database record.
        $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
        wp_update_attachment_metadata( $attach_id, $attach_data );
        
        set_post_thumbnail( 0, $attach_id );


        $wpdb->insert( 
            $table_name, 
                array( 
                    'time' => current_time( 'mysql' ), 
                    'name' => strtoupper($_POST['category']),
                ) 
        );
    }

?>

    <div class="wrap">
        <?= print_r($query_categories); ?>
        <h2>My Plugin Options</h2>
        <form id="featured_upload" method="post">
            <input type="button" id="upload_image_button" value="Upload Image" />
            <input type="text" name="category" />
            <input type="submit" value="Submit">
        </form>
    </div>
    
    <?php
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