<?php
/**
 * This file contains the code that displays the course list.
 * NEW
 */

  global $post; $post_id = $post->ID;

?>


<div class="ld_course_grid remodal-bg">
  <article id="post-<?php the_ID(); ?>" <?php //post_class('thumbnail course'); ?>> 
    <?php $post_image_id = get_post_thumbnail_id($post_to_use->ID);
    if ($post_image_id) {
      $thumbnail = wp_get_attachment_image_src( $post_image_id, 'post-thumbnail', false);
      if ($thumbnail) (string)$thumbnail = $thumbnail[0];
    }?>
    <a class="CourseGridImage_wrapper" href="#" rel="bookmark">
      <div class="CourseGridImage" style="background-image:url('<?php echo $thumbnail?>')"></div>
    </a>
    <div class="infoSection">
      <div class="CourseGridTitle"> 
        <a href="#" rel="bookmark">
        <h3><?php the_title(); ?></h3>
        </a>
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

    <div class="remodal" data-remodal-id="modal-<?php echo $post_id; ?>"
      data-remodal-options="hashTracking: false, closeOnOutsideClick: false">

      <button data-remodal-action="close" class="remodal-close"></button>
      <h1>Course In Progress</h1>
      <p>
        A course is currently in progress. Please refresh the page.
      </p>
      <br>
      <button data-remodal-action="cancel" class="remodal-cancel">Cancel</button>
      <button data-remodal-action="confirm" class="remodal-confirm">OK</button>
    </div>

    <div class="btnWrapper"> 
      <div class="caption">
      <?php ////////////////cert button
      if ($progress["percentage"] == 100 ){
        if( get_field('embed_code', $post_id)){
          $embedCode = get_field('embed_code', $post_id);
          $embedThis = " embed_code='".$embedCode . "' course='" . $post_id . "'";
          echo do_shortcode('[propel-certificate ' . $embedThis . ']');
        }; 
      };
      /////////////////////////// ?>
       </div><!-- end .caption -->
    </div><!-- end .btnWrapper -->
  </article>
</div><!--end .ld_course_grid -->

<hr class="myCoursesHR"/>

