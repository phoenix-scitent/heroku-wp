(function ($) {
    $(document).ready(function () {

        $('.get_shortcode').click( function ( event ) {
            event.preventDefault();
            $form = $(this).parents('form');
            create_shortcode( $form );
        });
        $(document).on('change', '.berocket_aapf_widget_sc', function() {
            $(this).data('sc_change', '1');
        });
        function create_shortcode( $form ) {
            var shortcode = {
                key:   [],
                value: [],
            };
            $form.find('.berocket_aapf_widget_sc').each( function ( i, o ) {
                if( $(o).data('sc_change') ) {
                    if ( $(o).is('[type=checkbox]') ) {
                        if( shortcode.key.indexOf( $(o).data('sc') ) == -1 ) {
                            shortcode.key.push($(o).data('sc'));
                            if ( $(o).prop('checked') ) {
                                shortcode.value.push($(o).val());
                            } else {
                                shortcode.value.push('');
                            }
                        } else {
                            index = shortcode.key.indexOf( $(o).data('sc') );
                            if ( ! Array.isArray( shortcode.value[index] ) ) {
                                firstvalue = shortcode.value[index];
                                shortcode.value[index] = [];
                                shortcode.value[index].push(firstvalue);
                            }
                            if ( $(o).prop('checked') ) {
                                shortcode.value[index].push($(o).val());
                            } else {
                                shortcode.value[index].push('');
                            }
                        }
                    } else if ( $(o).is('[type=radio]') ) {
                        if ( $(o).prop('checked') ) {
                            shortcode.key.push($(o).data('sc'));
                            shortcode.value.push($(o).val());
                        }
                    } else {
                        shortcode.key.push($(o).data('sc'));
                        shortcode.value.push($(o).val());
                    }
                }
            });
            var br_filters = '[br_filters';
            for( var i = 0; i < shortcode.key.length; i++ ) {
                br_filters += ' '+shortcode.key[i]+'="';
                if ( Array.isArray( shortcode.value[i] ) ) {
                    for( var j = 0; j < shortcode.value[i].length; j++ ) {
                        if ( j != 0 ) {
                            br_filters += '|';
                        }
                        br_filters += shortcode.value[i][j];
                    }
                } else {
                    br_filters += shortcode.value[i];
                }
                br_filters += '"';
            }
            br_filters += ']';
            alert(br_filters);
        }

        function berocket_aapf_show_hide($block, is_hide) {
            if( is_hide ) {
                $block.hide(0);
            } else {
                $block.show(0);
            }
        }
        function berocket_aapf_hide_blocks ( $parent, args ) {
			if( args.changed != undefined ) {
			    changed = args.changed;
			} else {
                changed = 'none';
            }
            filter_type = $('.berocket_aapf_widget_admin_filter_type_select', $parent).val();
            attribute = $('.berocket_aapf_widget_admin_filter_type_attribute_select', $parent).val();
            custom_taxonomy = $('.berocket_aapf_widget_admin_filter_type_custom_taxonomy_select', $parent).val();
            if ( changed != 'type' && changed != 'child_parent' ) {
                if ( filter_type == 'tag' ) {
                    $('.berocket_aapf_widget_admin_type_select', $parent).html('<option value="checkbox">Checkbox</option><option value="radio">Radio</option><option value="select">Select</option><option value="tag_cloud">Tag cloud</option>');
                } else if ( filter_type == '_stock_status' || ( filter_type == 'custom_taxonomy' && custom_taxonomy == 'product_tag' ) ) {
                    $('.berocket_aapf_widget_admin_type_select', $parent).html('<option value="checkbox">Checkbox</option><option value="radio">Radio</option><option value="select">Select</option>');
                } else if ( filter_type == 'custom_taxonomy' ) {
                    $('.berocket_aapf_widget_admin_type_select', $parent).html('<option value="checkbox">Checkbox</option><option value="radio">Radio</option><option value="select">Select</option><option value="color">Color</option><option value="image">Image</option><option value="slider">Slider</option>');
                } else if ( filter_type == 'product_cat' ) {
                    $('.berocket_aapf_widget_admin_type_select', $parent).html('<option value="checkbox">Checkbox</option><option value="radio">Radio</option><option value="select">Select</option><option value="slider">Slider</option>');
                } else if ( filter_type == 'attribute' ) {
                    if ( attribute == 'price' ) {
                        $('.berocket_aapf_widget_admin_type_select', $parent).html('<option value="slider">Slider</option><option value="ranges">Ranges</option>');
                    } else {
                        $('.berocket_aapf_widget_admin_type_select', $parent).html('<option value="checkbox">Checkbox</option><option value="radio">Radio</option><option value="select">Select</option><option value="color">Color</option><option value="image">Image</option><option value="slider">Slider</option>');
                    }
                } else if ( filter_type == 'filter_by' ) {
                    $('.berocket_aapf_widget_admin_type_select', $parent).html('<option value="checkbox">Checkbox</option><option value="radio">Radio</option><option value="color">Color</option><option value="image">Image</option><option value="select">Select</option>');
                }
            }
            type = $('.berocket_aapf_widget_admin_type_select', $parent).val();
            filter_by = $('.berocket_aapf_widget_admin_filter_type_filter_by_select', $parent).val();
            child_parent = $('.berocket_aapf_widget_child_parent_select', $parent).val();

            berocket_aapf_show_hide( $('.berocket_aapf_widget_admin_operator_select', $parent).parent(), 
                                     ( ( filter_type == 'attribute'
                                     && ( attribute == 'price' ) )
                                     || type == 'slider' ) );
            berocket_aapf_show_hide( $('.br_aapf_child_parent_selector', $parent), 
                                     ( ( filter_type == 'attribute'
                                     && ( attribute == 'price' || attribute == 'product_cat' ) )
                                     || filter_type == 'product_cat'
                                     || filter_type == '_stock_status'
                                     || filter_type == 'tag'
                                     || type == 'slider' ) );
            berocket_aapf_show_hide( $('.berocket_ranges_block', $parent), 
                                     ( filter_type != 'attribute'
                                     || attribute != 'price'
                                     || type != 'ranges' ) );
            berocket_aapf_show_hide( $('.berocket_aapf_widget_admin_values_per_row', $parent).parent(), 
                                     ( ( filter_type == 'attribute'
                                     && ( attribute == 'price' || attribute == 'product_cat' ) )
                                     || type == 'slider' 
                                     || type == 'select' 
                                     || type == 'tag_cloud' 
                                     || filter_type == 'product_cat'
                                     || filter_type == 'custom_taxonomy' && custom_taxonomy == 'product_cat' ) );
            berocket_aapf_show_hide( $('.berocket_aapf_widget_admin_non_price_tag_cloud', $parent), 
                                     ( type == 'tag_cloud'
                                     || type == 'slider' ) );
            berocket_aapf_show_hide( $('.berocket_aapf_widget_admin_price_attribute', $parent), 
                                     ( filter_type != 'attribute'
                                     || attribute != 'price' ) );
            berocket_aapf_show_hide( $('.berocket_aapf_advanced_color_pick_settings', $parent), 
                                     ( type != 'color' && type != 'image' ) );
            berocket_aapf_show_hide( $('.berocket_aapf_product_sub_cat_div', $parent), 
                                     ( filter_type != 'product_cat' ) );
            berocket_aapf_show_hide( $('.berocket_aapf_widget_admin_tag_cloud_block', $parent), 
                                     ( type != 'tag_cloud' ) );
            berocket_aapf_show_hide( $('.berocket_aapf_widget_admin_filter_type_', $parent), 
                                     true );
            berocket_aapf_show_hide( $('.berocket_aapf_icons_select_block', $parent), 
                                     ( type == 'select' ) );
            berocket_aapf_show_hide( $('.berocket_aapf_widget_child_parent_depth_block', $parent), 
                                     ( child_parent != 'child' ) );
            if ( $('.berocket_aapf_widget_admin_filter_type_'+filter_type, $parent).hasClass('berocket_aapf_widget_admin_filter_type_'+filter_type) ) {
                $('.berocket_aapf_widget_admin_filter_type_'+filter_type, $parent).show();
            }
            if ( type == 'color' || type == 'image' ) {
                var tax_color_name;
                if ( filter_type == 'attribute' ) {
                    tax_color_name = attribute;
                } else if ( filter_type == 'custom_taxonomy' ) {
                    tax_color_name = custom_taxonomy;
                }
                var data = {
                    'action': 'berocket_aapf_color_listener',
                    'tax_color_name': tax_color_name,
                    'type': type
                };
                $.post(ajaxurl, data, function(data) {
                    $('.berocket_widget_color_pick', $parent).html(data);
                    $('.berocket_aapf_advanced_color_pick_settings', $parent).show(0);
                });
            } else {
                $('.berocket_widget_color_pick', $parent).text("");
            }
            brjsf($('.berocket_aapf_widget_admin_type_select', $parent));
        }
        $(document).on('change', '.berocket_aapf_widget_admin_filter_type_select', function () {
            $parent = $(this).parents('form');
            berocket_aapf_hide_blocks ( $parent, { changed:'filter_type' } );
        });

        $(document).on('change', '.berocket_aapf_widget_admin_filter_type_attribute_select', function () {
            $parent = $(this).parents('form');
            berocket_aapf_hide_blocks ( $parent, { changed:'attribute' } );
        });

        $(document).on('change', '.berocket_aapf_widget_admin_type_select', function () {
            $parent = $(this).parents('form');
            berocket_aapf_hide_blocks ( $parent, { changed:'type' } );
        });

        $(document).on('change', '.berocket_aapf_widget_admin_filter_type_custom_taxonomy_select', function () {
            $parent = $(this).parents('form');
            berocket_aapf_hide_blocks ( $parent, { changed:'custom_taxonomy' } );
        });

        $(document).on('change', '.berocket_aapf_widget_child_parent_select', function () {
            $parent = $(this).parents('form');
            berocket_aapf_hide_blocks ( $parent, { changed:'child_parent' } );
        });

        $(document).on('change', '.berocket_aapf_checked_show_next', function () {
            if($(this).find('input[type=checkbox]').attr('checked') == 'checked') {
                $(this).next().show(0);
            } else {
                $(this).next().hide(0);
            }
        });

        $(document).on('click', '.berocket_aapf_advanced_settings_pointer', function (event) {
            event.preventDefault();
            $next = $(this).parent().next();
            if ( $next.is(':visible') ) {
                $next.slideUp(300);
            } else {
                $next.slideDown(300);
            }
        });

        $(document).on('click', '.berocket_aapf_output_limitations_pointer', function (event) {
            event.preventDefault();
            $next = $(this).parent().next();
            if ( $next.is(':visible') ) {
                $next.slideUp(300);
            } else {
                $next.slideDown(300);
            }
        });

        $('.colorpicker_field').each(function (i,o){
            $(o).css('backgroundColor', '#'+$(o).data('color'));
            $(o).colpick({
                layout: 'hex',
                submit: 0,
                color: '#'+$(o).data('color'),
                onChange: function(hsb,hex,rgb,el,bySetColor) {
                    $(el).css('backgroundColor', '#'+hex).next().val(hex);
                }
            })
        });

        $(document).on('click', '.theme_default', function (event) {
            event.preventDefault();
            $(this).prev().prev().css('backgroundColor', '#000000').colpickSetColor('#000000');
            $(this).prev().val('');
        });

        $(document).on('click', '.all_theme_default', function (event) {
            event.preventDefault();
            $table = $(this).parents('table');
            $table.find('.colorpicker_field').css('backgroundColor', '#000000').colpickSetColor('#000000');
            $table.find('.colorpicker_field').next().val('');
            $table.find('select').val("");
            $table.find('input[type=text]').val("");
        });

        $('.filter_settings_tabs').on('click', 'a', function (event) {
            event.preventDefault();
            $('#br_opened_tab').val( $(this).attr('href').replace('#', '') );
            $id = $(this).attr('href');
            $('.tab-item.current').removeClass('current');
            $($id).addClass('current');

            $('.filter_settings_tabs .nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
        });

        $(document).on('change', '.berocket_aapf_widget_admin_widget_type_select', function () {
            $parent = $(this).parents('form');
            if ( $(this).val() == 'filter' ) {
                $('.berocket_aapf_admin_filter_widget_content', $parent).show();
                $('.berocket_aapf_admin_widget_selected_area', $parent).hide();
            } else if( $(this).val() == 'update_button' ) {
                $('.berocket_aapf_admin_filter_widget_content', $parent).hide();
                $('.berocket_aapf_admin_widget_selected_area', $parent).hide();
            } else if( $(this).val() == 'selected_area' ) {
                $('.berocket_aapf_admin_filter_widget_content', $parent).hide();
                $('.berocket_aapf_admin_widget_selected_area', $parent).show();
            }
        });

        $(document).on('click', '.berocket_widget_show_color_values',function(event)
        {
            event.preventDefault();
            var show_block = $(this).next();
            if(show_block.css('display') == 'none')
            {
                show_block.show(40);
                $(this).find('span').removeClass('show_button').addClass('hide_button');
            }
            else
            {
                show_block.hide(40);
                $(this).find('span').removeClass('hide_button').addClass('show_button');
            }
        });
        $(document).on('change', '.berocket_scroll_shop_top', function () {
            if ( $(this).prop('checked') ) {
                $(this).parent().next().show();
            } else {
                $(this).parent().next().hide();
            }
        });
        $(document).on('click', '.berocket_aapf_font_awesome_icon_select',function(event) {
            event.preventDefault();
            $(this).next('.berocket_aapf_select_icon').show();
        });
        $(document).on('click', '.berocket_aapf_select_icon',function(event) {
            event.preventDefault();
            $(this).hide();
        });
        $(document).on('click', '.berocket_aapf_select_icon div p i.fa',function(event) {
            event.preventDefault();
            $(this).parents('.berocket_aapf_select_icon').hide();
        });
        $(document).on('click', '.berocket_aapf_select_icon div',function(event) {
            event.preventDefault();
            event.stopPropagation()
        });
        $(document).on('click', '.berocket_aapf_select_icon label',function(event) {
            event.preventDefault();
            $(this).parents('.berocket_aapf_select_icon').prevAll(".berocket_aapf_icon_text_value").val($(this).find('span').data('value'));
            $(this).parents('.berocket_aapf_select_icon').prevAll(".berocket_aapf_selected_icon_show").html('<i class="fa '+$(this).find('span').data('value')+'"></i>');
            $(this).parents('.berocket_aapf_select_icon').hide();
        });
        $(document).on('click', '.berocket_aapf_upload_icon', function(e) {
            e.preventDefault();
            $p = $(this);
            var custom_uploader = wp.media({
                title: 'Select custom Icon',
                button: {
                    text: 'Set Icon'
                },
                multiple: false 
            }).on('select', function() {
                var attachment = custom_uploader.state().get('selection').first().toJSON();
                $p.prevAll(".berocket_aapf_selected_icon_show").html('<i class="fa"><image src="'+attachment.url+'" alt=""></i>');
                $p.prevAll(".berocket_aapf_icon_text_value").val(attachment.url);
            }).open();
        });
        $(document).on('click', '.berocket_aapf_remove_icon',function(event) {
            event.preventDefault();
            $(this).prevAll(".berocket_aapf_icon_text_value").val("");
            $(this).prevAll(".berocket_aapf_selected_icon_show").html("");
        });
        br_widget_set();
        $(document).on( 'change', '.br_theme_set_select', function(event) {
            var $parent = $(this).parents('.br_checkbox_radio_settings');
            var $data = $(this).find('option:selected').data();
            var $color = '000000';
            if( ! $data['border_color'] ) {
                $color = '000000';
            } else {
                $color = $data['border_color'];
            }
            $parent.find('.br_border_color_set').prev().css('backgroundColor', '#' + $color).colpickSetColor('#' + $color);
            $parent.find('.br_border_color_set').val( $data['border_color'] );
            if( ! $data['font_color'] ) {
                $color = '000000';
            } else {
                $color = $data['font_color'];
            }
            $parent.find('.br_font_color_set').prev().css('backgroundColor', '#' + $color).colpickSetColor('#' + $color);
            $parent.find('.br_font_color_set').val( $data['font_color'] );
            if( ! $data['background'] ) {
                $color = '000000';
            } else {
                $color = $data['background'];
            }
            $parent.find('.br_background_set').prev().css('backgroundColor', '#' + $color).colpickSetColor('#' + $color);
            $parent.find('.br_background_set').val( $data['background'] );
            $parent.find('.br_border_width_set').val( $data['border_width'] );
            $parent.find('.br_border_radius_set').val( $data['border_radius'] );
            $parent.find('.br_size_set').val( $data['size'] );
            $parent.find('.br_icon_set').val( $data['icon'] );
        });
        $(document).on( 'change', '.br_checkbox_radio_settings input, .br_checkbox_radio_settings select', function(event) {
            if( ! $(this).is( '.br_theme_set_select' ) ) {
                $(this).parents('.br_checkbox_radio_settings').find('.br_theme_set_select').val('');
            }
        });
        $(document).on('click', '.berocket_remove_ranges',function(event) {
            event.preventDefault();
            $(this).parents('.berocket_ranges').remove();
        });
        $(document).on('click', '.berocket_add_ranges',function(event) {
            event.preventDefault();
            $(this).before($(this).data('html'));
        });
    })
})(jQuery);
var br_widget_setted = false;
function br_widget_set() {
    if ( br_widget_setted !== false ) {
        clearTimeout( br_widget_setted );
    }
    br_widget_setted = setTimeout( function () {
        brjsf(jQuery( ".widget-liquid-right .br_select_menu_left" ));
        brjsf(jQuery( ".widget-liquid-right .br_select_menu_right" ));
        brjsf_accordion(jQuery( ".widget-liquid-right .br_accordion" ));
        br_widget_setted = false
    }, 200);
}