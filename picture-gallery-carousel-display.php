<?php
    global $wpdb;
    $table_name = $wpdb->prefix . "picture_category";
    $query_categories = $wpdb->get_results( 'SELECT name FROM ' . $table_name . ' ORDER BY time DESC');

    $category_names_array = array();
    $category_array = array();

    foreach ( $query_categories as $key=>$category )
    {
      array_push($category_names_array, $category->name );
    }

    $images = new WP_Query( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'post_mime_type' => 'image' , 'posts_per_page' => -1 ) );

    foreach($images->posts as $image)
    {
      $img_src = wp_get_attachment_url($image->ID);
      $image_category = get_post_field('Category', $image->ID);
      $content = get_post_field('post_content', $image->ID);

      if(in_array($image_category, $category_names_array))
      {

        $category_array[$image_category][] = array($img_src, $content);
      }
    }
?>

<style type="text/css">
.carousel {
    margin-top: 20px;
}
.item .thumb {
  width: 25%;
  cursor: pointer;
  height: 100%;
  float: left; 
}
.item .thumb img {
  width: 100%;
  margin: 2px;
  height:100%;
}
.item img {
  width: 100%;  
}

.fullWidth {
  width:100%;
}

.description-box {
  background-color: rgba(0,0,0,.5);
  padding: 5px;
  border-radius: 5px;
}

.description-box p {
  margin-bottom: 0px;
}

.thumnail-carousel{
  position: relative;
}
.thumnail-carousel:before{
  content: "";
  display: block;
  padding-top: 22%;
}
.thumnail-carousel .item{
  position:  absolute;
  top: 0;
  left: 0;
  bottom: 0;
  right: 0;
}
.carousel-control {
  width: 6% !important;
}

.displayed-image {
  max-height: 370px !important;
}
</style>

<div class="container">
  <div class="row">
    <?php if(empty($category_array)) : ?>
      <p>No images assigned to any categories</p>
    <?php else : ?>
    <div class="col-sm-8">
        <?php $current_category_index = 0; ?>
        <?php foreach($category_array as $key => $value) : ?>
            <?php $current_image_index = 0; ?>
        <div id="carousel<?= preg_replace("/[\s]/", "-", $key) ?>" class="carousel slide" data-ride="carousel" data-interval="0">
            <div class="carousel-inner">
                <?php foreach($value as $image_source_value) : ?>
                    <div class="item <?php if ($current_image_index == 0) { echo 'active';} ?>">
                      
                        <img class="displayed-image" src="<?= $image_source_value[0] ?>" />
                      
                        <div class="carousel-caption description-box">
                          <p><?= $image_source_value[1] ?></p>
                        </div>
                    <?php $current_image_index ++; ?>
                    </div>
                <?php endforeach ?>
            </div>
        </div> 

        <?php
            $current_category_index ++;
        ?>

        <?php endforeach ?>

        <?php $current_category_index = 0; ?>
        <?php foreach($category_array as $key => $value) : ?>
            <?php $current_image_index = 0; ?>
            <div class="clearfix">
        <div id="thumbcarousel<?= preg_replace("/[\s]/", "-", $key) ?>" class="carousel" data-interval="false">
            <div class="carousel-inner thumnail-carousel">

                <?php foreach($value as $image_source_value) : ?>
                    <?php if ($current_image_index == 0) : ?>
                      <div class="item active">
                    <?php endif ?>
                        <div data-target="#carousel<?= preg_replace("/[\s]/", "-", $key) ?>" data-slide-to="<?= $current_image_index ?>" class="thumb">
                            <img class="img-responsive" src="<?= $image_source_value[0] ?>">
                        </div>
                    <?php if (((($current_image_index+1) % 4) == 0 && $current_image_index != 0) || $current_image_index == (count($value)-1)) : ?>
                      </div>
                    <?php endif ?>
                    <?php if ((($current_image_index+1) % 4) == 0 && $current_image_index != (count($value)-1)) : ?>
                      <div class="item">
                    <?php endif ?>
                    <?php $current_image_index ++; ?>
                <?php endforeach ?>
                
            </div><!-- /carousel-inner -->
            <a class="left carousel-control" href="#thumbcarousel<?= preg_replace("/[\s]/", "-", $key) ?>" role="button" data-slide="prev">
                <span class="glyphicon glyphicon-chevron-left"></span>
            </a>
            <a class="right carousel-control" href="#thumbcarousel<?= preg_replace("/[\s]/", "-", $key) ?>" role="button" data-slide="next">
                <span class="glyphicon glyphicon-chevron-right"></span>
            </a>
        </div> <!-- /thumbcarousel -->

        <?php
            $current_category_index ++;
        ?>
        </div><!-- /clearfix -->

        <?php endforeach ?>
    </div> <!-- /col-sm-6 -->
    <div class="col-sm-4">
        <h2>Categories</h2>
        <nav class="navbar navbar-default sidebar" role="navigation">
          <div class="container-fluid">
            <ul class="nav navbar-nav">
              <?php $is_first = true; ?>
              <?php foreach ( $query_categories as $key=>$category ) : ?>
                <?php if(array_key_exists($category->name, $category_array)) : ?>
                <?php $active_text = ($is_first ? ' active':'') ?>
                <li class="fullWidth"><button id="button<?= $key ?>" type="button" class="btn btn-default selector-button navbar-btn fullWidth<?= $active_text ?>"><?= $category->name?></button></li>
                <?php endif ?>
                <?php $is_first = false; ?>
              <?php endforeach ?>
            </ul>
        </div>
      </nav>
    </div> <!-- /col-sm-6 -->
  </div> <!-- /row -->
  <?php endif ?>
  </div>
</div> <!-- /container -->

<script type="text/javascript">
jQuery(function($) {
    $(".carousel").hide();

    var first_category = $(".selector-button:first").html();
    $("#carousel" + first_category.replace(/\s/g, '-')).show();
    $("#thumbcarousel" + first_category.replace(/\s/g, '-')).show();

    $(".selector-button").click(function(){
        
        $("button").removeClass("active");
        $(this).addClass("active");

        $(".carousel").hide();
        var this_category = $(this).html();
        $("#carousel" + this_category.replace(/\s/g, '-')).show();
        $("#thumbcarousel" + this_category.replace(/\s/g, '-')).show();
    }); 
});
</script>