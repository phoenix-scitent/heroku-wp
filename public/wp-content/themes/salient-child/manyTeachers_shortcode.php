<?php
/*
*  ACF repeater shortcode
*/            
function manyTeachers_fn() {
if( function_exists('have_rows') ) {
    ob_start();
    if( have_rows('instructors') ): ?>
        <?php while( have_rows('instructors') ): the_row(); ?>
          <div class="eachInstructor">
            <img class="instructor_image" src=" <?php the_sub_field('instructor_image'); ?> " alt=""/>
            <p><strong>Instructor: <?php the_sub_field('instructor_name'); ?></strong></p>
            <p><?php the_sub_field('instructor_bio'); ?> </p>
          </div> 
        <?php endwhile; ?>      
    <?php endif;    
    $content = ob_get_contents();
    ob_end_clean();
    return $content; 
    }  
}
add_shortcode( 'manyTeachers', 'manyTeachers_fn' );
?>