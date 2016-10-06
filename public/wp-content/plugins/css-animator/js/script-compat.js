// @codekit-prepend "script.js"

jQuery(document).ready(function($) {
	if ( typeof $.fn.waypoint === 'undefined' ) {
		return;
	}
	$('.gambit-css-animation.wpb_animate_when_almost_visible').waypoint(function() {
		$(this).addClass( 'wpb_start_animation' );
	}, {
		offset: '90%',
		triggerOnce: true
	});
});