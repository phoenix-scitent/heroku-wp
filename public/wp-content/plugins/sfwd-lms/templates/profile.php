<?php
/**
 * Displays a user's profile.
 * 
 * Available Variables:
 * 
 * $user_id     : Current User ID
 * $current_user  : (object) Currently logged in user object
 * $user_courses  : Array of course ID's of the current user
 * $quiz_attempts   : Array of quiz attempts of the current user
 * 
 * @since 2.1.0
 * 
 * @package LearnDash\User
 */
?>

<div id="learndash_profile">
  <div class="learndash_profile_heading no_radius clear_both"> 
    <span><?php _e( 'My Certificates', 'learndash' ); ?></span> 
  </div>
  <div id="course_list">
    <?php 
  if ( ! empty( $user_courses ) ) : 
    foreach ( $user_courses as $course_id ) : 
    $course = get_post( $course_id);
    $course_link = get_permalink( $course_id);
    $progress = learndash_course_progress( array(
      'user_id'   => $user_id,
      'course_id' => $course_id,
      'array'     => true
      ) 
    );
  $status = ( $progress['percentage'] == 100 ) ? 'completed' : 'notcompleted';
  ?>
    <div id='course-<?php echo esc_attr( $user_id ) . '-' . esc_attr( $course->ID ); ?>'>
<?php /** @todo Remove h4 container. **/ ?>
      <h4 style="padding: 30px;"> 
        <center><?php echo $course->post_title; ?></center>
        <div class="flip">
          <?php $incomplete = true; 
       if ( ! empty( $quiz_attempts[ $course_id ] ) ) : ?>
          <div class="learndash_profile_quizzes clear_both">
            <?php foreach ( array_reverse($quiz_attempts[ $course_id ]) as $k => $quiz_attempt ) : 
        $certificateLink = @$quiz_attempt['certificate']['certificateLink'];
        $status = empty( $quiz_attempt['pass'] ) ? 'failed' : 'passed';
        $quiz_title = ! empty( $quiz_attempt['post']->post_title) ? $quiz_attempt['post']->post_title : @$quiz_attempt['quiz_title'];
        $quiz_link = ! empty( $quiz_attempt['post']->ID) ? get_permalink( $quiz_attempt['post']->ID ) : '#';
      
      if ( (! empty( $quiz_title )) && (! empty( $certificateLink )) ) : ?>
            <div class='<?php echo esc_attr( $status ); ?>'>
              <div class="certificate">
                <?php if ( ! empty( $certificateLink ) ) : ?>
                <a class="btn btn-primary" style="color: #fff !important;" href='<?php echo esc_attr( $certificateLink ); ?>&time=<?php echo esc_attr( $quiz_attempt['time'] ) ?>' target="_blank"> Download the Certificate</a>
                <div style="margin: .5em 0"> Claimed On: <strong> <?php echo date_i18n( get_option( 'date_format' ), $quiz_attempt['time'] ); ?> </strong> </div>
                <?php $incomplete = false; 
          break; // breaks the loop ?
          endif; ?>
              </div>
      </div>
            <?php 
             endif; 
       endforeach; ?>
    </div>
          <?php 
           endif; 
           if ($incomplete) : ?>
          <center style="text-transform: none;"> Please complete this course to claim your Certificate.</center>
          <br />
          <?php  endif; ?>
</div>
</h4><!--en of HasCertWrapper-->
</div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>
</div>
