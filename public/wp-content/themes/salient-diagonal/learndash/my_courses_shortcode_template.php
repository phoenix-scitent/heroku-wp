<?php
/**
 * This file contains the code that displays the course list.
 * NEW
 */
 

 $col = empty($shortcode_atts["col"])? 3:intval($shortcode_atts["col"]);
    $smcol = $col/3;
    $col = empty($col)? 1:($col >= 12)? 12:$col;
    $smcol = empty($smcol)? 1:($smcol >= 12)? 12:$smcol;
    $col = intVal(12/$col);
    $smcol = intVal(12/$smcol);

    global $post; $post_id = $post->ID;

    $options = get_option('sfwd_cpt_options');
?>


    <div class="ld_course_grid">
      <article id="post-<?php the_ID(); ?>" <?php //post_class('thumbnail course'); ?>> 
        <?php $post_image_id = get_post_thumbnail_id($post_to_use->ID);
        if ($post_image_id) {
          $thumbnail = wp_get_attachment_image_src( $post_image_id, 'post-thumbnail', false);
          if ($thumbnail) (string)$thumbnail = $thumbnail[0];
        }?>
        <div class="course_grid_image" style="background-image:url('<?php echo $thumbnail?>')"></div>
        <div class="infoSection">
          <div class="course_grid_title"> 
            <h3><?php the_title(); ?></h3>
          </div>
          <?php 
		      $user_id = get_current_user_id();
          $user_courses = ld_get_mycourses($user_id);
          $usermeta = get_user_meta( $user_id, '_sfwd-quizzes', true );    
          $progress = learndash_course_progress(array("user_id" => $user_id, "course_id" => $post_id, "array" => true));?>
          
      <div class="overview">
        <div class= "progressWrapper">
          <dd class="course_progress" title="<?php echo sprintf("%s out of %s lessons completed",$progress["completed"], $progress["total"]); ?>">
           
            <div class="course_progress_blue" style="width: <?php echo $progress["percentage"]; ?>%;">
              <span class="percentCompleteLable"><?php echo sprintf("%s%% Complete", $progress["percentage"]); ?></span> 
            </div> 
          </dd>
        </div><!-- end .table-cell .progressWrapper -->
      </div><!-- end .overview -->

    </div> <!-- end .infoSection -->  

        <div class="btnWrapper"> 
          <div class="caption">

            <?php echo scitent_render_xapi_button($post_id, $progress["percentage"]); ?> 
         
          <?php ////////////////cert button
          if ($progress["percentage"] ==100 ){ 
            /////
            if( get_field('embed_code', $post_id)){ 
              $embedCode = get_field('embed_code', $post_id);
              $embedThis = " embed_code='".$embedCode . "' course='" . $post_id . "'";
              echo do_shortcode('[propel-certificate ' . $embedThis . ']');
            }; 
            /////
          };
          /////////////////////////// ?>
           </div><!-- end .caption -->
        </div><!-- end .btnWrapper -->
        <br class='clear' />
      </article>
    </div><!--end .ld_course_grid -->
<br class='clear' />

 