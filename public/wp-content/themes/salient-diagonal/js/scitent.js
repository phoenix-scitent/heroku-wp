/** Scitent JavaScript Namespace
 * Handy Utilities and Helpers for Scitent Plugins
 *
 */
'use strict';
if(!scitent) { var scitent = {}; }
if(!scitent.utils) { scitent.utils = {}; }

/*********************************
 * Things to run after dom loads
 */
jQuery( document ).ready( function() {
  // hide the AAAS Member number until it's ready:
  scitent.aaas_hide_number_css();
  scitent.aaas_number_toggler();
  scitent.aaas_init_join_modal();
  scitent.aaas_init_number_modal();
  scitent.aaas_init_all_modal_close_x();
});

/*********************************
 * Scitent definition
 */
scitent = jQuery.extend({}, scitent, {
  aaas_hide_number_css: function() {
    var style = document.createElement('style');
    style.id = 'aaas_member_hider';
    style.type = 'text/css';
    style.innerHTML = '.userpro-field-aaas_membership_number { display: none !important; }';
    document.getElementsByTagName('head')[0].appendChild(style);
  },
  aaas_number_toggler: function() { 
    var $member_input_div = jQuery('.userpro-field-role');
    var $member_number_div = jQuery('.userpro-field-aaas_membership_number');
    var $member_num_input = jQuery('.userpro-input input').filter('[data-label="AAAS Member Number"]');
    $member_input_div.click(function(e){ 
      scitent.aaas_memberid_help();
      if('Non Member' === e.target.innerHTML) {
        scitent.aaas_role_join_help();
        $member_number_div.hide();
        $member_num_input.val('????????');
      } else if('AAAS Member' === e.target.innerHTML) {
        jQuery('#aaas_member_hider').html('.userpro-field-aaas_membership_number { display: block;}');
        $member_number_div.show();
        $member_num_input.val('');
        jQuery('#role_join_helper').hide();
      }
    });
  },
  aaas_memberid_helped: false,
  aaas_memberid_help: function() {
    if( scitent.aaas_memberid_helped ) {
      return;
    } else {
      scitent.aaas_memberid_helped = true;
    }
    var $tooltip = jQuery('.userpro-field-aaas_membership_number span.userpro-tip');
    $tooltip.html('FIND YOUR AAAS MEMBER NUMBER')
            .attr('id','member_number_helper')
            .css({'background-image':'none',
                  'width':'auto',
                  'color':'black',
                  'text-decoration':'underline'})
            .tipsy('disable');
  },
  aaas_role_join_helped: false,
  aaas_role_join_help: function() {
    if( scitent.aaas_role_join_helped ) {
      jQuery('#role_join_helper').show();
      return;
    } else {
      scitent.aaas_role_join_helped = true;
    }
    jQuery('.userpro-field-role .userpro-input').after('<div id="role_join_helper" style="float:right;">');
    var $role_join_helper = jQuery('#role_join_helper');
    $role_join_helper
            .html('<a style="text-decoration: underline; cursor: pointer;" class= "eModal-1">BECOME AN AAAS MEMBER</a>')
            .append('<span id="join_details" class="scitent_helper" style="padding: 20px;">\
              Click the following link to learn more about AAAS membership and to purchase your membership:\
             <a href="https://pubs.aaas.org/Promo/promo_setup_rd.asp?dmc=p6demo&_ga=1.42554785.581384420.1457630112"\
              class="userpro-button"\
              target="_blank">\
             JOIN NOW OR RENEW YOUR MEMBERSHIP TODAY</a></span>');
    jQuery('#join_details').hide();
    jQuery('#join_toggler').click( function(e){
      jQuery('#join_details').toggle();
    });
  },
  aaas_number_validate_via_userpro: function(e) {
    e.preventDefault();
    jQuery('#aaas_submit_spinning').removeClass('userpro-loading'); // show it
    jQuery('#aaas_upgrade_member_number_warning').hide().children('span').html('');
    var data = {
      'action': 'userpro_side_validate',
      'input_value': jQuery('#aaas_upgrade_member_number').val(),
      'ajaxcheck': 'scitent_valid_aaas_number'            
    };
    jQuery.post(ajaxurl, data, function(response) {
      jQuery('#aaas_submit_spinning').addClass('userpro-loading'); // hide it
      try {
        var resp = JSON.parse(response);
      } catch(err) {
        console.log('scitent shortcode JSON error.  Is UserPro installed?');
        return;
      }
      var errormessage = resp.error || '';
      if( '' === errormessage ) {
        scitent.aaas_number_update_in_db();
      } else {
        jQuery('#aaas_upgrade_member_number_warning').show().children('span').html(errormessage);
      }
    });
  },
  aaas_number_update_in_db: function() {
    jQuery('#aaas_submit_spinning').removeClass('userpro-loading'); // show it
    var data = {
      'action': 'aaas_member_number_update',
      'new_aas_number': jQuery('#aaas_upgrade_member_number').val()
    };
    jQuery.post(ajaxurl, data, function(response) {
      jQuery('#aaas_submit_spinning').addClass('userpro-loading'); // hide it
      jQuery('#aaas_upgrade_congrats').show();
    });
  },
  aaas_init_join_modal: function() {
    var that = this;
    jQuery('body').on('click', '#role_join_helper', function ( e ) {
      that.modal_up( jQuery( '#scitent-join-modal' ) );
      e.stopPropagation();
    });
  },
  aaas_init_number_modal: function() {
    var that = this;    
    jQuery('body').on('click', '#member_number_helper', function ( e ) {
      that.modal_up( jQuery( '#scitent-number-modal' ) );
      e.stopPropagation();
    });
  },
  aaas_init_all_modal_close_x: function() {
    var that = this;
    jQuery('span.close').click(function(e) {
      that.modal_down( jQuery(this).closest('.modal') );
    });
    window.onclick = function(e) {
      var visible_modals = jQuery('.modal').filter(':visible');
      if( visible_modals.length && !jQuery( e.target ).parent().closest('.modal').length ) {
        that.modal_down( jQuery(visible_modals[0]) );
      }
    }
  },
  aaas_show_modal_styles: function() {
    jQuery("#header-outer").addClass("z-index-5");
    jQuery("#footer-outer").addClass("z-index-5");
    jQuery(".page-header-no-bg").addClass("z-index-5");
    jQuery("#header-secondary-outer").addClass("z-index-5");
  },
  aaas_hide_modal_styles: function() {
    jQuery("#header-outer").removeClass("z-index-5");
    jQuery("#footer-outer").removeClass("z-index-5");
    jQuery(".page-header-no-bg").removeClass("z-index-5");
    jQuery("#header-secondary-outer").removeClass("z-index-5");
  },
  modal_up: function( $modal ) {
    console.log("Modal opened");
    $modal.css('display', 'block');
    this.aaas_show_modal_styles();
  },
  modal_down: function( $modal ) {
    console.log("Modal closed");
    $modal.css('display', 'none');
    this.aaas_hide_modal_styles();
  }
});

/** end of base scitent JS **/

/*********************************
 * Scitent Utilities definitions
 */

