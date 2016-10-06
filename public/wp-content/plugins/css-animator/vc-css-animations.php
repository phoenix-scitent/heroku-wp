<?php
/*
Plugin Name: CSS Animator for VC
Description: Adds the CSS Animator element to Visual Composer that enables more CSS animations
Author: Benjamin Intal, Gambit
Version: 1.6
Author URI: http://gambit.ph
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

defined( 'VERSION_GAMBIT_VC_CSS_ANIMATIONS' ) or define( 'VERSION_GAMBIT_VC_CSS_ANIMATIONS', '1.6' );

defined( 'GAMBIT_VC_CSS_ANIMATIONS' ) or define( 'GAMBIT_VC_CSS_ANIMATIONS', 'gambit-vc-css-animations' );


if ( ! class_exists( 'GambitVCCSSAnimations' ) ) {

	/**
	 * CSS Animation Class
	 *
	 * @since	1.0
	 */
	class GambitVCCSSAnimations {

		// Used for loading stuff only once during a page load
		private static $firstLoad = 0;

		private $animations;

		const COMPATIBILITY_MODE = '_gambit_css_animator_compat_mode';

		private $ignoreElements = array(
			'vc_column',
			'vc_row',
		);

		/**
		 * Constructor, checks for Visual Composer and defines hooks
		 *
		 * @return	void
		 * @since	1.0
		 */
		function __construct() {
            add_action( 'after_setup_theme', array( $this, 'init' ), 1 );
			add_action( 'plugins_loaded', array( $this, 'loadTextDomain' ) );

			add_shortcode( 'css_animation', array( $this, 'cssAnimationShortcode' ) );

			// Gambit links
			add_filter( 'plugin_row_meta', array( $this, 'pluginLinks' ), 10, 2 );

			// Add a compatibility mode toggler
			add_filter( 'plugin_row_meta', array( $this, 'addCompatibilityModeToggle' ), 11, 2 );
			add_action( 'admin_init', array( $this, 'toggleCompatibilityMode' ) );

			// Activation instructions & CodeCanyon rating notices
			$this->createNotices();

			$this->formAnimationArray();
		}

        public function init() {
            if ( ! defined( 'WPB_VC_VERSION' ) ) {
                return;
            }
            if ( version_compare( WPB_VC_VERSION, '4.2', '<' ) ) {
        		add_action( 'after_setup_theme', array( $this, 'createAnimationElement' ) );
            } else {
        		add_action( 'vc_after_mapping', array( $this, 'createAnimationElement' ) );
            }

			add_action( 'admin_enqueue_scripts', array( $this, 'adminEnqueueScripts' ) );
        }

		public function adminEnqueueScripts() {
			wp_enqueue_style( 'css_animation', plugins_url( '/css/admin.css', __FILE__ ), false, VERSION_GAMBIT_VC_CSS_ANIMATIONS );
		}

		private function formAnimationArray() {
			$this->animations = array(
				__( '- Entrance Animations -', GAMBIT_VC_CSS_ANIMATIONS ) => '',
				__( 'Fade in', GAMBIT_VC_CSS_ANIMATIONS )                         => 'fade-in',
				__( 'Flip top to bottom 3D', GAMBIT_VC_CSS_ANIMATIONS )           => 'flip-3d-to-bottom',
				__( 'Flip bottom to top 3D', GAMBIT_VC_CSS_ANIMATIONS )           => 'flip-3d-to-top',
				__( 'Flip right to left 3D', GAMBIT_VC_CSS_ANIMATIONS )           => 'flip-3d-to-left',
				__( 'Flip left to right 3D', GAMBIT_VC_CSS_ANIMATIONS )           => 'flip-3d-to-right',
				__( 'Flip in horizontally 3D', GAMBIT_VC_CSS_ANIMATIONS )         => 'flip-3d-horizontal',
				__( 'Flip in vertically 3D', GAMBIT_VC_CSS_ANIMATIONS )           => 'flip-3d-vertical',
				__( 'Fall bottom to top 3D', GAMBIT_VC_CSS_ANIMATIONS )           => 'fall-3d-to-top',
				__( 'Fall top to bottom 3D', GAMBIT_VC_CSS_ANIMATIONS )           => 'fall-3d-to-bottom',
				__( 'Roll bottom to top 3D', GAMBIT_VC_CSS_ANIMATIONS )           => 'roll-3d-to-top',
				__( 'Roll right to left 3D', GAMBIT_VC_CSS_ANIMATIONS )           => 'roll-3d-to-left',
				__( 'Roll left to right 3D', GAMBIT_VC_CSS_ANIMATIONS )           => 'roll-3d-to-right',
				__( 'Rotate in top left 2D', GAMBIT_VC_CSS_ANIMATIONS )           => 'rotate-in-top-left',
				__( 'Rotate in top right 2D', GAMBIT_VC_CSS_ANIMATIONS )          => 'rotate-in-top-right',
				__( 'Rotate in bottom left 2D', GAMBIT_VC_CSS_ANIMATIONS )        => 'rotate-in-bottom-left',
				__( 'Rotate in bottom right 2D', GAMBIT_VC_CSS_ANIMATIONS )       => 'rotate-in-bottom-right',
				__( 'Slide top to bottom 3D', GAMBIT_VC_CSS_ANIMATIONS )          => 'slide-to-bottom',
				__( 'Slide bottom to top 3D', GAMBIT_VC_CSS_ANIMATIONS )          => 'slide-to-top',
				__( 'Slide right to left 3D', GAMBIT_VC_CSS_ANIMATIONS )          => 'slide-to-left',
				__( 'Slide left to right 3D', GAMBIT_VC_CSS_ANIMATIONS )          => 'slide-to-right',
				__( 'Slide elastic bottom to top 2D', GAMBIT_VC_CSS_ANIMATIONS )     => 'slide-elastic-to-top',
				__( 'Slide elastic top to bottom 2D', GAMBIT_VC_CSS_ANIMATIONS )     => 'slide-elastic-to-bottom',
				__( 'Slide elastic right to left 2D', GAMBIT_VC_CSS_ANIMATIONS )     => 'slide-elastic-to-left',
				__( 'Slide elastic left to right 2D', GAMBIT_VC_CSS_ANIMATIONS )     => 'slide-elastic-to-right',
				__( 'Grow 2D', GAMBIT_VC_CSS_ANIMATIONS )                         => 'size-grow-2d',
				__( 'Shrink 2D', GAMBIT_VC_CSS_ANIMATIONS )                       => 'size-shrink-2d',
				__( 'Spin 2D', GAMBIT_VC_CSS_ANIMATIONS )                         => 'spin-2d',
				__( 'Spin 2D reverse', GAMBIT_VC_CSS_ANIMATIONS )                 => 'spin-2d-reverse',
				__( 'Spin 3D', GAMBIT_VC_CSS_ANIMATIONS )                         => 'spin-3d',
				__( 'Spin 3D reverse', GAMBIT_VC_CSS_ANIMATIONS )                 => 'spin-3d-reverse',
				__( 'Twirl top left 3D', GAMBIT_VC_CSS_ANIMATIONS )               => 'twirl-3d-top-left',
				__( 'Twirl top right 3D', GAMBIT_VC_CSS_ANIMATIONS )              => 'twirl-3d-top-right',
				__( 'Twirl bottom left 3D', GAMBIT_VC_CSS_ANIMATIONS )            => 'twirl-3d-bottom-left',
				__( 'Twirl bottom right 3D', GAMBIT_VC_CSS_ANIMATIONS )           => 'twirl-3d-bottom-right',
				__( 'Twirl 3D', GAMBIT_VC_CSS_ANIMATIONS )                        => 'twirl-3d',
				__( 'Unfold top to bottom 3D', GAMBIT_VC_CSS_ANIMATIONS )                 => 'unfold-3d-to-bottom',
				__( 'Unfold bottom to top 3D', GAMBIT_VC_CSS_ANIMATIONS )                 => 'unfold-3d-to-top',
				__( 'Unfold right to left 3D', GAMBIT_VC_CSS_ANIMATIONS )                 => 'unfold-3d-to-left',
				__( 'Unfold left to right 3D', GAMBIT_VC_CSS_ANIMATIONS )                 => 'unfold-3d-to-right',
				__( 'Unfold horzitonal 3D', GAMBIT_VC_CSS_ANIMATIONS )                 => 'unfold-3d-horizontal',
				__( 'Unfold vertical 3D', GAMBIT_VC_CSS_ANIMATIONS )                 => 'unfold-3d-vertical',
				// __( 'Three unfold top to bottom 3D', GAMBIT_VC_CSS_ANIMATIONS )                 => 'three_unfold-3d-to-bottom',
				// __( 'Three unfold bottom to top 3D', GAMBIT_VC_CSS_ANIMATIONS )                 => 'three_unfold-3d-to-top',
				// __( 'Three unfold right to left 3D', GAMBIT_VC_CSS_ANIMATIONS )                 => 'three_unfold-3d-to-left',
				// __( 'Three unfold left to right 3D', GAMBIT_VC_CSS_ANIMATIONS )                 => 'three_unfold-3d-to-right',
				__( '- Looped Animations -', GAMBIT_VC_CSS_ANIMATIONS )   => '',
				__( 'Pulsate', GAMBIT_VC_CSS_ANIMATIONS )                         => 'loop-pulsate',
				__( 'Pulsate fade', GAMBIT_VC_CSS_ANIMATIONS )                    => 'loop-pulsate-fade',
				__( 'Hover', GAMBIT_VC_CSS_ANIMATIONS )                           => 'loop-hover',
				__( 'Hover floating', GAMBIT_VC_CSS_ANIMATIONS )                  => 'loop-hover-float',
				__( 'Wobble', GAMBIT_VC_CSS_ANIMATIONS )                          => 'loop-wobble',
				__( 'Wobble 3D', GAMBIT_VC_CSS_ANIMATIONS )                       => 'loop-wobble-3d',
				__( 'Dangle', GAMBIT_VC_CSS_ANIMATIONS )                          => 'loop-dangle',
			);
		}


		/**
		 * Loads the translations
		 *
		 * @return	void
		 * @since	1.0
		 */
		public function loadTextDomain() {
			load_plugin_textdomain( GAMBIT_VC_CSS_ANIMATIONS, false, basename( dirname( __FILE__ ) ) . '/languages/' );
		}


		/**
		* Adds plugin links
		*
		* @access	public
		* @param	array $plugin_meta The current array of links
		* @param	string $plugin_file The plugin file
		* @return	array The current array of links together with our additions
		* @since	1.0
		**/
		public function pluginLinks( $plugin_meta, $plugin_file ) {
			if ( $plugin_file == plugin_basename( __FILE__ ) ) {
				$pluginData = get_plugin_data( __FILE__ );

				$plugin_meta[] = sprintf( "<a href='%s' target='_blank'>%s</a>",
					"http://support.gambit.ph?utm_source=" . urlencode( $pluginData['Name'] ) . "&utm_medium=plugin_link",
					__( "Get Customer Support", GAMBIT_VC_CSS_ANIMATIONS )
				);
				$plugin_meta[] = sprintf( "<a href='%s' target='_blank'>%s</a>",
					"https://gambit.ph/plugins?utm_source=" . urlencode( $pluginData['Name'] ) . "&utm_medium=plugin_link",
					__( "Get More Plugins", GAMBIT_VC_CSS_ANIMATIONS )
				);
			}
			return $plugin_meta;
		}


		/************************************************************************
		 * Activation instructions & CodeCanyon rating notices START
		 ************************************************************************/
		/**
		 * For theme developers who want to include our plugin, they will need
		 * to disable this section. This can be done by include this line
		 * in their theme:
		 *
		 * defined( 'GAMBIT_DISABLE_RATING_NOTICE' ) or define( 'GAMBIT_DISABLE_RATING_NOTICE', true );
		 */

		/**
		 * Adds the hooks for the notices
		 *
		 * @access	protected
		 * @return	void
		 * @since	1.0
		 **/
		protected function createNotices() {
			register_activation_hook( __FILE__, array( $this, 'justActivated' ) );
			register_deactivation_hook( __FILE__, array( $this, 'justDeactivated' ) );

			if ( defined( 'GAMBIT_DISABLE_RATING_NOTICE' ) ) {
				return;
			}

			add_action( 'admin_notices', array( $this, 'remindSettingsAndSupport' ) );
			add_action( 'admin_notices', array( $this, 'remindRating' ) );
			add_action( 'wp_ajax_' . __CLASS__ . '-ask-rate', array( $this, 'ajaxRemindHandler' ) );
		}


		/**
		 * Creates the transients for triggering the notices when the plugin is activated
		 *
		 * @return	void
		 * @since	1.0
		 **/
		public function justActivated() {
			delete_transient( __CLASS__ . '-activated' );
			set_transient( __CLASS__ . '-activated', time(), MINUTE_IN_SECONDS * 2 );

			if ( defined( 'GAMBIT_DISABLE_RATING_NOTICE' ) ) {
				return;
			}

			delete_transient( __CLASS__ . '-ask-rate' );
			set_transient( __CLASS__ . '-ask-rate', time(), DAY_IN_SECONDS * 4 );

			update_option( __CLASS__ . '-ask-rate-placeholder', 1 );
		}


		/**
		 * Removes the transients & triggers when the plugin is deactivated
		 *
		 * @return	void
		 * @since	1.0
		 **/
		public function justDeactivated() {
			delete_transient( __CLASS__ . '-activated' );
			delete_transient( __CLASS__ . '-ask-rate' );
			delete_option( __CLASS__ . '-ask-rate-placeholder' );
		}


		/**
		 * Ajax handler for when a button is clicked in the 'ask rating' notice
		 *
		 * @return	void
		 * @since	1.0
		 **/
		public function ajaxRemindHandler() {
			check_ajax_referer( __CLASS__, '_nonce' );

			if ( $_POST['type'] == 'remove' ) {
				delete_option( __CLASS__ . '-ask-rate-placeholder' );
			} else { // remind
				set_transient( __CLASS__ . '-ask-rate', time(), DAY_IN_SECONDS );
			}

			die();
		}


		/**
		 * Displays the notice for reminding the user to rate our plugin
		 *
		 * @return	void
		 * @since	1.0
		 **/
		public function remindRating() {
			if ( defined( 'GAMBIT_DISABLE_RATING_NOTICE' ) ) {
				return;
			}
			if ( get_option( __CLASS__ . '-ask-rate-placeholder' ) === false ) {
				return;
			}
			if ( get_transient( __CLASS__ . '-ask-rate' ) ) {
				return;
			}

			$pluginData = get_plugin_data( __FILE__ );
			$nonce = wp_create_nonce( __CLASS__ );

			echo '<div class="updated gambit-ask-rating" style="border-left-color: #3498db">
					<p>
						<img src="' . plugins_url( 'gambit-logo.png', __FILE__ ) . '" style="display: block; margin-bottom: 10px"/>
						<strong>' . sprintf( __( 'Enjoying %s?', GAMBIT_VC_CSS_ANIMATIONS ), $pluginData['Name'] ) . '</strong><br>' .
						__( 'Help us out by rating our plugin 5 stars in CodeCanyon! This will allow us to create more awesome products and provide top notch customer support.', GAMBIT_VC_CSS_ANIMATIONS ) . '<br>' .
						'<button data-href="http://codecanyon.net/downloads?utm_source=' . urlencode( $pluginData['Name'] ) . '&utm_medium=rate_notice" class="button button-primary" style="margin: 10px 10px 10px 0;">' . __( 'Rate us 5 stars in CodeCanyon :)', GAMBIT_VC_CSS_ANIMATIONS ) . '</button>' .
						'<button class="button button-secondary remind" style="margin: 10px 10px 10px 0;">' . __( 'Remind me tomorrow', GAMBIT_VC_CSS_ANIMATIONS ) . '</button>' .
						'<button class="button button-secondary nothanks" style="margin: 10px 0;">' . __( 'I&apos;ve already rated!', GAMBIT_VC_CSS_ANIMATIONS ) . '</button>' .
						'<script>
						jQuery(document).ready(function($) {
							"use strict";

							$(".gambit-ask-rating button").click(function() {
								if ( $(this).is(".button-primary") ) {
									var $this = $(this);

									var data = {
										"_nonce": "' . $nonce . '",
										"action": "' . __CLASS__ . '-ask-rate",
										"type": "remove"
									};

									$.post(ajaxurl, data, function(response) {
										$this.parents(".updated:eq(0)").fadeOut();
										window.open($this.attr("data-href"), "_blank");
									});

								} else if ( $(this).is(".remind") ) {
									var $this = $(this);

									var data = {
										"_nonce": "' . $nonce . '",
										"action": "' . __CLASS__ . '-ask-rate",
										"type": "remind"
									};

									$.post(ajaxurl, data, function(response) {
										$this.parents(".updated:eq(0)").fadeOut();
									});

								} else if ( $(this).is(".nothanks") ) {
									var $this = $(this);

									var data = {
										"_nonce": "' . $nonce . '",
										"action": "' . __CLASS__ . '-ask-rate",
										"type": "remove"
									};

									$.post(ajaxurl, data, function(response) {
										$this.parents(".updated:eq(0)").fadeOut();
									});
								}
								return false;
							});
						});
						</script>
					</p>
				</div>';
		}


		/**
		 * Displays the notice that we have a support site and additional instructions
		 *
		 * @return	void
		 * @since	1.0
		 **/
		public function remindSettingsAndSupport() {
			if ( defined( 'GAMBIT_DISABLE_RATING_NOTICE' ) ) {
				return;
			}
			if ( ! get_transient( __CLASS__ . '-activated' ) ) {
				return;
			}

			$pluginData = get_plugin_data( __FILE__ );

			echo '<div class="updated" style="border-left-color: #3498db">
					<p>
						<img src="' . plugins_url( 'gambit-logo.png', __FILE__ ) . '" style="display: block; margin-bottom: 10px"/>
						<strong>' . sprintf( __( 'Thank you for activating %s!', GAMBIT_VC_CSS_ANIMATIONS ), $pluginData['Name'] ) . '</strong><br>' .

						__( 'Create a CSS Animator Element in Visual Composer then put other elements in it and you are good to go.', GAMBIT_VC_CSS_ANIMATIONS ) . '<br>' .

						__( 'If you need any support, you can leave us a ticket in our support site. The link to our support site is listed in the plugin details for future reference.', GAMBIT_VC_CSS_ANIMATIONS ) . '<br>' .
						'<a href="http://support.gambit.ph?utm_source=' . urlencode( $pluginData['Name'] ) . '&utm_medium=activation_notice" class="gambit_ask_rate button button-default" style="margin: 10px 0;" target="_blank">' . __( 'Visit our support site', GAMBIT_VC_CSS_ANIMATIONS ) . '</a>' .
						'<br>' .
						'<em style="color: #999">' . __( 'This notice will go away in a moment', GAMBIT_VC_CSS_ANIMATIONS ) . '</em><br>
					</p>
				</div>';
		}


		/************************************************************************
		 * Activation instructions & CodeCanyon rating notices END
		 ************************************************************************/


		public function createAnimationElement() {

			/**
			 * We need to define this so that VC will show our nesting container correctly
			 */
			include( 'class-css-animation.php' );

			if ( ! is_admin() ) {
				return;
			}

			vc_map( array(
			    "name" => __( "CSS Animator", GAMBIT_VC_CSS_ANIMATIONS ),
			    "base" => "css_animation",
			    "as_parent" => array('except' => 'css_animation'),
			    "content_element" => true,
				"icon" => plugins_url( 'vc-icon.png', __FILE__ ),
			    "js_view" => 'VcColumnView',
				"description" => __( "Add animations to your elements", GAMBIT_VC_CSS_ANIMATIONS ),
			    "params" => array(
			        // add params same as with any other content element
					array(
						"type" => "dropdown",
						"heading" => __( 'Functionality', GAMBIT_VC_CSS_ANIMATIONS ),
						"param_name" => "enable_animator",
						"value" => array(
							__( 'All devices', GAMBIT_VC_CSS_ANIMATIONS ) => 'all',
							__( 'Disabled in mobile', GAMBIT_VC_CSS_ANIMATIONS ) => 'nomobile',
						),
						'description' => __( 'Select whether to have CSS Animator work in all devices, or disable on mobile devices.', GAMBIT_VC_CSS_ANIMATIONS ),
					),
					array(
						"type" => "dropdown",
						"heading" => __( "CSS Animation", "js_composer" ),
						"param_name" => "animation",
						"value" => array_merge( array( __( "No", "js_composer" ) => '' ), $this->animations ),
						"description" => __( "Select type of animation if you want this element to be animated when it enters into the browsers viewport. Note: Works only in modern browsers.", "js_composer" ),
					),
					array(
						"type" => "textfield",
						"heading" => __( "Animation Duration", GAMBIT_VC_CSS_ANIMATIONS ),
						"param_name" => "duration",
						"value" => '',
						"description" => __( "Duration in seconds. You can use decimal points in the value. Use this field to specify the amount of time the animation plays. <em>The default value depends on the animation, leave blank to use the default.</em>", GAMBIT_VC_CSS_ANIMATIONS ),
					),
					array(
						"type" => "textfield",
						"heading" => __( "Animation Delay", GAMBIT_VC_CSS_ANIMATIONS ),
						"param_name" => "delay",
						"value" => '',
						"description" => __( "Delay in seconds. You can use decimal points in the value. Use this field to delay the animation for a few seconds, this is helpful if you want to chain different effects one after another above the fold.", GAMBIT_VC_CSS_ANIMATIONS ),
					),
			        array(
			            "type" => "textfield",
			            "heading" => __( "Extra class name", "js_composer" ),
			            "param_name" => "el_class",
			            "description" => __( "If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", "js_composer" ),
			        ),
			    ),
			) );
		}

		public function cssAnimationShortcode( $atts, $content = null ) {
			extract( shortcode_atts( array(
			    'el_class'        => '',
				'animation' => '',
				'duration' => '',
				'delay' => '',
				'enable_animator' => 'all',
			), $atts ) );

			if ( empty( $animation ) ) {
				return do_shortcode( $content );
			}

			// Enqueue the animation script
			$animationGroup = substr( $animation, 0, stripos( $animation, '-' ) );
			wp_enqueue_style( 'vc-css-animation-' . $animationGroup, plugins_url( '/css/' . $animationGroup . '.css', __FILE__ ), false, VERSION_GAMBIT_VC_CSS_ANIMATIONS );

			if ( get_option( self::COMPATIBILITY_MODE ) === false ) {
				wp_enqueue_script( 'vc-css-animation-script', plugins_url( '/js/min/script-min.js', __FILE__ ), array( 'jquery' ), VERSION_GAMBIT_VC_CSS_ANIMATIONS, true );
			} else {
				wp_enqueue_script( 'vc-css-animation-script-compat', plugins_url( '/js/min/script-compat-min.js', __FILE__ ), array( 'jquery' ), VERSION_GAMBIT_VC_CSS_ANIMATIONS, true );
			}

			wp_enqueue_script( 'waypoints' );

			// Set default values
			$styles = array();
			if ( $duration != '0' && ! empty( $duration ) ) {
				$duration = (float)trim( $duration, "\n\ts" );
				$styles[] = "-webkit-animation-duration: {$duration}s";
				$styles[] = "-moz-animation-duration: {$duration}s";
				$styles[] = "-ms-animation-duration: {$duration}s";
				$styles[] = "-o-animation-duration: {$duration}s";
				$styles[] = "animation-duration: {$duration}s";
				// $styles[] = "-webkit-transition-duration: {$duration}s";
				// $styles[] = "-moz-transition-duration: {$duration}s";
				// $styles[] = "-ms-transition-duration: {$duration}s";
				// $styles[] = "-o-transition-duration: {$duration}s";
				// $styles[] = "transition-duration: {$duration}s";
			}

			// Delay all animations by 0.1. In some cases, animations may not play when the delay is 0
			if ( $delay == '0' || empty( $delay ) ) {
				$delay = '0.1';
			} else {
				$delay = (float) $delay + 0.1;
			}

			if ( $delay != '0' && ! empty( $delay ) ) {
				$delay = (float)trim( $delay, "\n\ts" );
				$styles[] = "opacity: 0";
				$styles[] = "-webkit-animation-delay: {$delay}s";
				$styles[] = "-moz-animation-delay: {$delay}s";
				$styles[] = "-ms-animation-delay: {$delay}s";
				$styles[] = "-o-animation-delay: {$delay}s";
				$styles[] = "animation-delay: {$delay}s";
				// $styles[] = "-webkit-transition-delay: {$delay}s";
				// $styles[] = "-moz-transition-delay: {$delay}s";
				// $styles[] = "-ms-transition-delay: {$delay}s";
				// $styles[] = "-o-transition-delay: {$delay}s";
				// $styles[] = "transition-delay: {$delay}s";
			}
			$styles = implode( ';', $styles );

			if ( preg_match( '/^unfold-/', $animation ) ) {
				return "<div data-enable_animator='" . $atts["enable_animator"] . "' class='wpb_animate_when_almost_visible gambit-css-animation $animation $el_class' style='$styles'><div class='unfolder-container right' style='$styles'><div class='unfolder-content'>" . do_shortcode( $content ) . "</div></div><div class='unfolder-container left' style='$styles'><div class='unfolder-content'>" . do_shortcode( $content ) . "</div></div><div class='real-content' style='$styles'>" . do_shortcode( $content ) . '</div></div>';
			}

			if ( preg_match( '/^three-unfold-/', $animation ) ) {
				return "<div data-enable_animator='" . $atts["enable_animator"] . "' class='wpb_animate_when_almost_visible gambit-css-animation $animation $el_class' style='$styles'><div class='unfolder-container top left' style='$styles'><div class='unfolder-content'>" . do_shortcode( $content ) . "</div></div><div class='unfolder-container mid' style='$styles'><div class='unfolder-content'>" . do_shortcode( $content ) . "</div></div><div class='unfolder-container bottom right' style='$styles'><div class='unfolder-content'>" . do_shortcode( $content ) . "</div></div><div class='real-content' style='$styles'>" . do_shortcode( $content ) . '</div></div>';
			}

			return "<div data-enable_animator='" . $atts["enable_animator"] . "' class='wpb_animate_when_almost_visible gambit-css-animation $animation $el_class' style='$styles'>" . do_shortcode( $content ) . '</div>';
		}


		/**
		 * Adds an enabled/disable link for toggling compatiblity mode. Compatibility mode changes the
		 * hook so that the plugin will work in impractical situations where VC is embedded into a theme
		 *
		 * @access	public
		 * @param	array $plugin_meta The current array of links
		 * @param	string $plugin_file The plugin file
		 * @return	array The current array of links together with our additions
		 * @since	1.6
		 **/
		public function addCompatibilityModeToggle( $plugin_meta, $plugin_file ) {
			if ( $plugin_file == plugin_basename( __FILE__ ) ) {
				$pluginData = get_plugin_data( __FILE__ );

				$compatibilityMode = get_option( self::COMPATIBILITY_MODE );
				$nonce = wp_create_nonce( self::COMPATIBILITY_MODE );
				if ( empty( $compatibilityMode ) ) {
					$plugin_meta[] = sprintf( "<a href='%s' target='_self'>%s</a>",
						admin_url( "plugins.php?" . self::COMPATIBILITY_MODE . "=1&nonce=" . $nonce ),
						__( "Enable Compatibility Mode", GAMBIT_VC_CSS_ANIMATIONS )
					);
				} else {
					$plugin_meta[] = sprintf( "<a href='%s' target='_self'>%s</a>",
						admin_url( "plugins.php?" . self::COMPATIBILITY_MODE . "=0&nonce=" . $nonce ),
						__( "Disable Compatibility Mode", GAMBIT_VC_CSS_ANIMATIONS )
					);
				}
			}
			return $plugin_meta;
		}


		/**
		 * Compatibility mode toggling handler
		 *
		 * @access	public
		 * @return	void
		 * @since	1.6
		 **/
		public function toggleCompatibilityMode() {
			if ( empty( $_REQUEST['nonce'] ) ) {
				return;
			}
			if ( ! wp_verify_nonce( $_REQUEST['nonce'], self::COMPATIBILITY_MODE ) ) {
				return;
			}

			if ( isset( $_REQUEST[ self::COMPATIBILITY_MODE ] ) ) {
				if ( empty( $_REQUEST[ self::COMPATIBILITY_MODE ] ) ) {
					delete_option( self::COMPATIBILITY_MODE );
				} else {
					update_option( self::COMPATIBILITY_MODE, '1' );
				}
				wp_redirect( admin_url( 'plugins.php' ) );
				die();
			}
		}
	}

	new GambitVCCSSAnimations();
}