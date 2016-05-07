<?php
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }

    global $wpdb;
    $table_name = $wpdb->prefix . "picture_category";

    //Grabs categories data from the database for purpose of array comparison
    $query_categories = $wpdb->get_results( 'SELECT id, name FROM ' . $table_name);

    if(isset($_POST['delete_category']))
    {
        $wpdb->query( $wpdb->prepare( 
            "
                DELETE FROM $table_name
                WHERE id = %d
            ",
            $_POST['delete_category'] 
        ) );
    }

    $error_text = '';
    if(isset($_POST['category']))
    {
        if(!empty($_POST['category']))
        {   
            $category_name = strtoupper($_POST['category']);
            $category_name = sanitize_text_field($category_name);

            if(!empty($_FILES["fileToUpload"]["name"]))
            {
                $file = $_FILES["fileToUpload"];
                $user_input_file_name = $_POST['fileName'];

                $image_editor = wp_get_image_editor($file['tmp_name']);

                if (!is_wp_error($image_editor)) {
                    // Generate a new filename with suffix aftered by user
                    $saved = $image_editor->save($user_input_file_name,$file['type']);

                    // Try to alter the original $file and inject the new name and path for our new image
                    $file['name'] = sanitize_file_name($saved['file']);
                }


                $uploaddir = wp_upload_dir();
                $uploadfile = $uploaddir['path'] . '/' . basename( $file['name'] );

                require_once( ABSPATH . 'wp-admin/includes/file.php' );
                require_once( ABSPATH . 'wp-admin/includes/image.php' );
                require_once( ABSPATH . 'wp-admin/includes/media.php' );

                $allowed_image_types = array('jpeg' =>'image/jpeg', 'jpg' =>'image/jpg', 'gif' => 'image/gif', 'png' => 'image/png');

                $upload_overrides = array( 'test_form' => false, 'mimes' => $allowed_image_types );

                $movefile = wp_handle_upload( $file, $upload_overrides );

                

                if ( $movefile && ! isset( $movefile['error'] ) ) {

                    $description = '';

                    if(!empty($_POST['description']))
                    {
                        $description = $_POST['description'];
                        $description = sanitize_text_field($description);
                    }

                    $filename = basename( $_FILES['fileToUpload']['name'] );

                    $wp_filetype = wp_check_filetype(basename($filename), null );

                    $attachment = array(
                        'post_mime_type' => $wp_filetype['type'],
                        'post_title' => preg_replace('/\.[^.]+$/', '', $file['name']),
                        'post_content' => $description,
                        'post_status' => 'inherit',
                        'menu_order' => $_i + 1000
                    );

                    
                    $attach_id = wp_insert_attachment( $attachment, $movefile['file'] );


                    // Generate the metadata for the attachment, and update the database record.
                    $attach_data = wp_generate_attachment_metadata( $attach_id, $movefile['url'] );
                    wp_update_attachment_metadata( $attach_id, $attach_data );
                    update_post_meta( $attach_id, 'Category', $category_name );

                    $error_text = '<h2 style="color:green">File is valid, and was successfully uploaded.</h2>';
                } else {
                    /**
                     * Error generated by _wp_handle_upload()
                     * @see _wp_handle_upload() in wp-admin/includes/file.php
                     */
                    $error_text = '<h2 style="color:red">There was an error: ' . $movefile['error'] . '</h2>';
                }
            }

            $name_array = array();

            foreach ( $query_categories as $key=>$category )
            {
                array_push($name_array, $category->name);
            }


            if(!in_array($category_name, $name_array))
            {
                $wpdb->query( $wpdb->prepare( 
                    "
                        INSERT INTO $table_name
                        ( time, name )
                        VALUES ( %s, %s )
                    ",
                    current_time( 'mysql' ), 
                    $category_name 
                ) );
            }
        }
        else
        {
            $error_text = '<h2 style="color:red">You did not include a category: Image upload stopped</h2>';
        }
    }

    //Grabs any updated category data from the database for display on the page
    $query_categories = $wpdb->get_results( 'SELECT id, name FROM ' . $table_name);
?>

<style>
.form {
    background-color: white;
    padding: 10px;
    border-radius: 5px;
    width:60%;
}

.form, .form-input {
    border: 1px solid #aaa !important;
}

.form, .form select, .form input, .form textarea {
    margin-bottom: 5px;
    border-radius: 3px;
}

.form input, .form select, .form textarea {
    width: 95%;
}

fieldset {
    margin-bottom: 20px;
    border-radius: 5px;
    border: 1px solid #aaa;
    padding: 5px;
}

@media screen and (max-width: 580px) {
    .form {
        width: 95%;
        margin: 0px auto;
    }
}
</style>

<?= $error_text; ?>

<div class="wrap">
    <form class="form" method="post">
        <h3>Delete a category</h3>
        <select class="form-input" name="delete_category">
            <option value=''>Select Image Category</option>
            <?php foreach ( $query_categories as $key=>$category ) : ?>
            <option value='<?= $category->id ?>'><?= $category->name?></option>
            <? endforeach ?>
        </select>
        <input type="submit" class="button-primary" value="Delete Category">
    </form>
    <form id="featured_upload" class="form" method="post" enctype="multipart/form-data">
        <h3>Upload a new image and/or add category</h3>
        <fieldset>
            <legend>File Upload (Not required to add category)</legend>
            <input type="file" name="fileToUpload" id="fileToUpload" accept="image/*" /></br>
            <label for="fileName">Edit file name below if desired</label></br>
            <input id="fileName" name="fileName" type="text" class="form-input" /></br>
            <label for="description">Write a description (Optional)</label></br>
            <textarea rows="3" cols="30" name="description" id="description" class="form-input"></textarea></br>
            <select id="categorySelect" class="form-input">
            <option value=''>Select Image Category</option>
            <?php foreach ( $query_categories as $key=>$category ) : ?>
            <option value='<?= $category->name ?>'><?= $category->name?></option>
            <? endforeach ?>
        </select></br>
        </fieldset>
        <label for="category">Select Category From Above or Enter New Category</label></br>
        <input type="text" name="category" id="category" class="form-input" /></br>
        <input type="submit" class="button-primary" value="Upload Image/Add Category">
    </form>
</div>