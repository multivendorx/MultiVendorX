jQuery(document).ready(function ($) {
    $('.question_verify_admin').on('click', function(e){
        e.preventDefault();
        var $this = $(this);
        var question_type = $(this).attr('data-verification');
        var question_id = $(this).attr('data-user_id');
        var data_action = $(this).attr('data-action');
        var product     = $(this).attr('data-product');
        var data = {
            action   : 'mvx_question_verification_approval',
            question_type : question_type,
            question_id : question_id,
            data_action : data_action,
            product      : product
        }   
        $.post(ajaxurl, data, function(response) {
            location.reload();
        });
    });
    $('.img_tip').each(function () {
        $(this).qtip({
            content: $(this).attr('data-desc'),
            position: {
                my: 'top center',
                at: 'bottom center',
                viewport: $(window)
            },
            show: {
                event: 'mouseover',
                solo: true,
            },
            hide: {
                inactive: 6000,
                fixed: true
            },
            style: {
                classes: 'qtip-dark qtip-shadow qtip-rounded qtip-dc-css'
            }
        });
    });

    $('.dc_datepicker').each(function () {
        $(this).datepicker({
            dateFormat: $(this).data('date_format'),
            changeMonth: true,
            changeYear: true
        });
    });
    
    $( '.mvx-shipping-zone-method' ).on( 'change', '.mvx-shipping-zone-method-enabled input', function() {
            if ( $( this ).is( ':checked' ) ) {
                    $( this ).closest( '.mvx-input-toggle' ).removeClass( 'woocommerce-input-toggle--disabled' );
                    $( this ).closest( '.mvx-input-toggle' ).addClass( 'checked' );
                    $( this ).closest( '.mvx-input-toggle' ).find( '.mvx-input-toggle' ).removeClass( 'hide' );
            } else {
                    $( this ).closest( '.mvx-input-toggle' ).addClass( 'woocommerce-input-toggle--disabled' );
                    $( this ).closest( '.mvx-input-toggle' ).removeClass( 'checked' );
                    $( this ).closest( '.mvx-input-toggle' ).find( '.mvx-shipping-zone-method' ).addClass( 'hide' );
            }
    } );

    $( '.mvx-shipping-zone-method' ).on( 'click', '.mvx-shipping-zone-method-enabled', function( e ) {
            var eventTarget = $( e.target );

            if ( eventTarget.is( 'input' ) ) {
                    e.stopPropagation();
                    return;
            }

            var $checkbox = $( this ).find( 'input[type="checkbox"]' );

            $checkbox.prop( 'checked', ! $checkbox.prop( 'checked' ) ).change();
    } );

    if ($('#commission_typee').val() == 'fixed_with_percentage') {
        $('#default_commissionn').closest("tr").css("display", "none");
        $('#fixed_with_percentage_qty').closest("tr").css("display", "none");
        $('#fixed_with_percentage_per_vendor').closest("tr").css("display", "none");
    } else if ($('#commission_typee').val() == 'fixed_with_percentage_qty') {
        $('#default_commissionn').closest("tr").css("display", "none");
        $('#fixed_with_percentage').closest("tr").css("display", "none");
        $('#fixed_with_percentage_per_vendor').closest("tr").css("display", "none");
    } else if ($('#commission_typee').val() == 'fixed_with_percentage_per_vendor') {
        $('#default_commissionn').closest("tr").css("display", "none");
        $('#fixed_with_percentage').closest("tr").css("display", "none");
        $('#fixed_with_percentage_qty').closest("tr").css("display", "none");
        $('#fixed_with_percentage').closest("tr").css("display", "none");
    } else {
        $('#default_percentage').closest("tr").css("display", "none");
        $('#fixed_with_percentage').closest("tr").css("display", "none");
        $('#fixed_with_percentage_qty').closest("tr").css("display", "none");
        $('#fixed_with_percentage_per_vendor').closest("tr").css("display", "none");
    }

    $('#commission_typee').change(function () {
        var commission_type = $(this).val();
        if (commission_type == 'fixed_with_percentage') {
            $('#default_commissionn').closest("tr").css("display", "none");
            $('#default_percentage').val('');
            $('#fixed_with_percentage').val('');
            $('#default_percentage').closest("tr").show();
            $('#fixed_with_percentage').closest("tr").show();
            $('#fixed_with_percentage_qty').closest("tr").hide();
            $('#fixed_with_percentage_per_vendor').closest("tr").hide();
        } else if (commission_type == 'fixed_with_percentage_qty') {
            $('#default_commissionn').closest("tr").css("display", "none");
            $('#default_percentage').closest("tr").show();
            $('#fixed_with_percentage_qty').closest("tr").show();
            $('#fixed_with_percentage').closest("tr").hide();
            $('#default_percentage').val('');
            $('#fixed_with_percentage_qty').val('');
            $('#fixed_with_percentage_per_vendor').closest("tr").hide();
        } else if (commission_type == 'fixed_with_percentage_per_vendor') {
            $('#default_commissionn').closest("tr").css("display", "none");
            $('#default_percentage').closest("tr").show();
            $('#fixed_with_percentage_per_vendor').closest("tr").show();
            $('#fixed_with_percentage').closest("tr").hide();
            $('#default_percentage').val('');
            $('#fixed_with_percentage_per_vendor').val('');
            $('#fixed_with_percentage_qty').closest("tr").hide();
            $('#fixed_with_percentage').closest("tr").hide();
        } else {
            $('#default_commissionn').closest("tr").show();
            $('#default_percentage').closest("tr").css("display", "none");
            $('#fixed_with_percentage').closest("tr").css("display", "none");
            $('#fixed_with_percentage_qty').closest("tr").css("display", "none");
            $('#fixed_with_percentage_per_vendor').closest("tr").css("display", "none");
        }
    });

    if ($('#mvx_disbursal_mode_admin').is(':checked')) {
        $('#payment_schedule').closest("tr").show();
    } else {
        $('#payment_schedule').closest("tr").css("display", "none");
    }
    
    $('#mvx_disbursal_mode_admin').change(function () {
        if ($(this).is(':checked')) {
            $('#payment_schedule').closest("tr").show();
        } else {
            $('#payment_schedule').closest("tr").css("display", "none");
        }
    });

    // distance by shipping
    if ($('#is_vendor_shipping_on').is(':checked')) {
        $('#enabled_distance_by_shipping_for_vendor').closest("tr").show();
    } else {
        $('#enabled_distance_by_shipping_for_vendor').closest("tr").css("display", "none");
    }
    
    $('#is_vendor_shipping_on').change(function () {
        if ($(this).is(':checked')) {
            $('#enabled_distance_by_shipping_for_vendor').closest("tr").show();
        } else {
            $('#enabled_distance_by_shipping_for_vendor').closest("tr").css("display", "none");
        }
    });

    $('#enabled_distance_by_shipping_for_vendor').change(function () {
        if ($(this).is(':checked')) {
            $('#is_checkout_delivery_location_on').prop('checked', true);
        } else {
            $('#is_checkout_delivery_location_on').prop('checked', false);
        }
    });    
    

    $('.mvx_country_to_select').select2(); 
    $('.mvx-select').select2();
    $('.mvx_country_to_select').each(function() {
        $(this).change(function() {
            setStateBoxforCountry( $(this) );
        }).change();
    });

    setTimeout(function() {
        $('#mvx_shipping_rates').children('.multi_input_block').children('.add_multi_input_block').click(function() {
            $('#mvx_shipping_rates').children('.multi_input_block:last').find('.mvx_country_to_select').select2();
            $('#mvx_shipping_rates').children('.multi_input_block:last').find('.mvx_country_to_select').change(function() {
                setStateBoxforCountry( $(this) );
            }).change();
        });
    }, 2000 );
    function setStateBoxforCountry( countryBox ) {
        var states_json = wc_country_select_params.countries.replace( /&quot;/g, '"' ),
        states = $.parseJSON( states_json ),
        country = countryBox.val();

        if ( states[ country ] ) {
            if ( $.isEmptyObject( states[ country ] ) ) {
                countryBox.parent().find('.mvx_state_to_select').each(function() {
                    $statebox = $(this);
                    $statebox_id = $statebox.attr('id');
                    $statebox_name = $statebox.attr('name');
                    $statebox_val = $statebox.val();
                    if( $statebox_val === null ) $statebox_val = '';
                    $statebox_dataname = $statebox.data('name');

                    if ( $statebox.is( 'select' ) ) {
                        $statebox.replaceWith( '<input type="text" name="'+$statebox_name+'" id="'+$statebox_id+'" data-name="'+$statebox_dataname+'" value="'+$statebox_val+'" class="mvx-text mvx_state_to_select multi_input_block_element" />' );
                    }
                });
            } else {
                input_selected_state = '';
                var options = '',
                state = states[ country ];
                countryBox.parent().find('.mvx_state_to_select').each(function() {

                    $statebox = $(this);
                    $statebox_id = $statebox.attr('id');
                    $statebox_name = $statebox.attr('name');
                    $statebox_val = $statebox.val();
                    if( $statebox_val === null ) $statebox_val = '';
                    $statebox_dataname = $statebox.data('name');

                    for ( var index in state ) {
                        if ( state.hasOwnProperty( index ) ) {
                            if ( $statebox_val ) {
                                if ( $statebox_val == index ) {
                                    var selected_value = 'selected="selected"';
                                } else {
                                    var selected_value = '';
                                }
                            }
                            options = options + '<option value="' + index + '"' + selected_value + '>' + state[ index ] + '</option>';
                        }
                    }


                    if ( $statebox.is( 'select' ) ) {
                        $statebox.html( '<option value="">' + wc_country_select_params.i18n_select_state_text + '</option><optgroup label="-------------------------------------"><option value="everywhere">'+mvx_admin_js_script_data.everywhere_else_option+'</option></optgroup><optgroup label="-------------------------------------">' + options + '</optgroup>' );
                    }
                    if ( $statebox.is( 'input' ) ) {
                        $statebox.replaceWith( '<select name="'+$statebox_name+'" id="'+$statebox_id+'" data-name="'+$statebox_dataname+'" class="mvx-select mvx_state_to_select multi_input_block_element"></select>' );
                        $statebox = $('#'+$statebox_id);
                        $statebox.html( '<option value="">' + wc_country_select_params.i18n_select_state_text + '</option><optgroup label="-------------------------------------"><option value="everywhere">'+mvx_admin_js_script_data.everywhere_else_option+'</option></optgroup><optgroup label="-------------------------------------">' + options + '</optgroup>' );
                    }
                    $statebox.val( $statebox_val );
                });
            }
        } else {
            countryBox.parent().find('.mvx_state_to_select').each(function() {
                $statebox = $(this);
                $statebox_id = $statebox.attr('id');
                $statebox_name = $statebox.attr('name');
                $statebox_val = $statebox.val();
                if( $statebox_val === null ) $statebox_val = '';
                $statebox_dataname = $statebox.data('name');

                if ( $statebox.is( 'select' ) ) {
                    $statebox.replaceWith( '<input type="text" name="'+$statebox_name+'" id="'+$statebox_id+'" data-name="'+$statebox_dataname+'" value="'+$statebox_val+'" class="mvx-text mvx_state_to_select multi_input_block_element" />' );
                }
            });
        }
    }

    $('.multi_input_holder').each(function() {
        var multi_input_holder = $(this);
        addMultiInputProperty(multi_input_holder);
    });

    function addMultiInputProperty(multi_input_holder) {
        var multi_input_limit = multi_input_holder.data('limit');
        if( typeof multi_input_limit == 'undefined' ) multi_input_limit = -1;
        if(multi_input_holder.children('.multi_input_block').length == 1) multi_input_holder.children('.multi_input_block').children('.remove_multi_input_block').css('display', 'none');
        if( multi_input_holder.children('.multi_input_block').length == multi_input_limit )  multi_input_holder.find('.add_multi_input_block').hide();
        else multi_input_holder.find('.add_multi_input_block').show();
        multi_input_holder.children('.multi_input_block').each(function() {
            if($(this)[0] != multi_input_holder.children('.multi_input_block:last')[0]) {
                $(this).children('.add_multi_input_block').remove();
            }
            $(this).children('.add_multi_input_block').attr( 'title', mvx_admin_js_script_data.mvx_multiblick_addnew_help );
            $(this).children('.remove_multi_input_block').attr( 'title', mvx_admin_js_script_data.mvx_multiblick_remove_help );
            $(this).children('.mvx_multiblock_sortable').remove();
        });

        multi_input_holder.children('.multi_input_block').children('.add_multi_input_block').off('click').on('click', function() {
            var holder_id = multi_input_holder.attr('id');
            var holder_name = multi_input_holder.data('name');
            var multi_input_blockCount = multi_input_holder.data('length');
            multi_input_blockCount++;
            var multi_input_blockEle = multi_input_holder.children('.multi_input_block:first').clone(false);

            multi_input_blockEle.find('textarea,input:not(input[type=button],input[type=submit],input[type=checkbox],input[type=radio])').val('');
            multi_input_blockEle.find('input[type=checkbox]').attr('checked', false);
            multi_input_blockEle.find('.select2-container').remove();
            multi_input_blockEle.find('select').select2();
            multi_input_blockEle.find('select').select2('destroy');
            multi_input_blockEle.children('.multi_input_block_element:not(.multi_input_holder)').each(function () {
                var ele = $(this);
                var ele_name = ele.data('name');
                if(ele.hasClass('mvx-wp-fields-uploader')) {} else if(ele.hasClass('wp-picker-container')) {} else {
                    ele.attr('name', holder_name+'['+multi_input_blockCount+']['+ele_name+']');
                    ele.attr('id', holder_id + '_' + ele_name + '_' + multi_input_blockCount);
                }
            });
        // Nested multi-input block property
        multi_input_blockEle.children('.multi_input_holder').each(function() {
            setNestedMultiInputIndex($(this), holder_id, holder_name, multi_input_blockCount);
        });

        multi_input_blockEle.children('.remove_multi_input_block').off('click').on('click', function() {
            var rconfirm = confirm(mvx_admin_js_script_data.multiblock_delete_confirm);
            if(rconfirm) {
                var remove_ele_parent = $(this).parent().parent();
                var addEle = remove_ele_parent.children('.multi_input_block').children('.add_multi_input_block').clone(true);
                $(this).parent().remove();
                remove_ele_parent.children('.multi_input_block').children('.add_multi_input_block').remove();
                remove_ele_parent.children('.multi_input_block:last').append(addEle);
                if( remove_ele_parent.children('.multi_input_block').length == multi_input_limit ) remove_ele_parent.find('.add_multi_input_block').hide();
                else remove_ele_parent.find('.add_multi_input_block').show();
                if(remove_ele_parent.children('.multi_input_block').length == 1) remove_ele_parent.children('.multi_input_block').children('.remove_multi_input_block').css('display', 'none');
            }
        });

        multi_input_blockEle.children('.add_multi_input_block').remove();
        multi_input_holder.append(multi_input_blockEle);
        multi_input_holder.children('.multi_input_block:last').append($(this));
        if(multi_input_holder.children('.multi_input_block').length > 1) multi_input_holder.children('.multi_input_block').children('.remove_multi_input_block').css('display', 'block');
        if( multi_input_holder.children('.multi_input_block').length == multi_input_limit ) multi_input_holder.find('.add_multi_input_block').hide();
        else multi_input_holder.find('.add_multi_input_block').show();
        multi_input_holder.data('length', multi_input_blockCount);

        // Fields Type Property
        multi_input_holder.find('.field_type_options').each(function() {
            $(this).off('change').on('change', function() {
                $(this).parent().find('.field_type_select_options').hide();
                $(this).parent().find('.field_type_html_options').hide();
                if( $(this).val() == 'select' ) $(this).parent().find('.field_type_select_options').show();
                else if( $(this).val() == 'mselect' ) $(this).parent().find('.field_type_select_options').show();
                else if( $(this).val() == 'dropdown' ) $(this).parent().find('.field_type_select_options').show();
                else if( $(this).val() == 'html' ) $(this).parent().find('.field_type_html_options').show();
            } ).change();
        } );

        // Group Name
        multi_input_holder.find('.custom_field_is_group').each( function() {
            $(this).change( function() {
                if( $(this).is(':checked') ) {
                    $(this).parent().find('.custom_field_is_group_name').css('visibility', 'visible');
                } else {
                    $(this).parent().find('.custom_field_is_group_name').css('visibility', 'hidden');
                }
            } ).change();
        } );

        });

        if(!multi_input_holder.hasClass('multi_input_block_element')) {
        //multi_input_holder.children('.multi_input_block').css('padding-bottom', '40px');
        }
        if(multi_input_holder.children('.multi_input_block').children('.multi_input_holder').length > 0) {
        //multi_input_holder.children('.multi_input_block').css('padding-bottom', '40px');
        }

        multi_input_holder.children('.multi_input_block').children('.remove_multi_input_block').off('click').on('click', function() {
            var rconfirm = confirm(mvx_admin_js_script_data.multiblock_delete_confirm);
            if(rconfirm) {
                var remove_ele_parent = $(this).parent().parent();
                var addEle = remove_ele_parent.children('.multi_input_block').children('.add_multi_input_block').clone(true);
                $(this).parent().remove();
                remove_ele_parent.children('.multi_input_block').children('.add_multi_input_block').remove();
                remove_ele_parent.children('.multi_input_block:last').append(addEle);
                if(remove_ele_parent.children('.multi_input_block').length == 1) remove_ele_parent.children('.multi_input_block').children('.remove_multi_input_block').css('display', 'none');
                if( remove_ele_parent.children('.multi_input_block').length == multi_input_limit ) remove_ele_parent.find('.add_multi_input_block').hide();
                else remove_ele_parent.find('.add_multi_input_block').show();
            }
        });

        // Fields Type Property
        multi_input_holder.find('.field_type_options').each(function() {
            $(this).off('change').on('change', function() {
                $(this).parent().find('.field_type_select_options').hide();
                $(this).parent().find('.field_type_html_options').hide();
                if( $(this).val() == 'select' ) $(this).parent().find('.field_type_select_options').show();
                else if( $(this).val() == 'mselect' ) $(this).parent().find('.field_type_select_options').show();
                else if( $(this).val() == 'dropdown' ) $(this).parent().find('.field_type_select_options').show();
                else if( $(this).val() == 'html' ) $(this).parent().find('.field_type_html_options').show();
            } ).change();
        } );

        // Group Name
        multi_input_holder.find('.custom_field_is_group').each( function() {
            $(this).change( function() {
                if( $(this).is(':checked') ) {
                    $(this).parent().find('.custom_field_is_group_name').css('visibility', 'visible');
                } else {
                    $(this).parent().find('.custom_field_is_group_name').css('visibility', 'hidden');
                }
            } ).change();
        } );
    }

    function setNestedMultiInputIndex(nested_multi_input, holder_id, holder_name, multi_input_blockCount) {
        nested_multi_input.children('.multi_input_block:not(:last)').remove();
        var multi_input_id = nested_multi_input.attr('id');
        multi_input_id = multi_input_id.replace(holder_id + '_', '');

        var multi_input_id_splited = multi_input_id.split('_');
        var multi_input_name = '';        

        for(var i = 0; i < (multi_input_id_splited.length -1); i++) {
            if(multi_input_name != '') multi_input_name += '_';
            multi_input_name += multi_input_id_splited[i];
        }
        nested_multi_input.attr('data-name', holder_name+'['+multi_input_blockCount+']['+multi_input_name+']');
        nested_multi_input.attr('id', holder_id+'_'+multi_input_name+'_'+multi_input_blockCount);
        var nested_multi_input_block_count = 0;
        nested_multi_input.children('.multi_input_block').children('.multi_input_block_element:not(.multi_input_holder)').each(function() {
            var ele = $(this);
            var ele_name = ele.data('name');
            if(ele.hasClass('mvx-wp-fields-uploader')) {} else {
                var multiple = ele.attr('multiple');
                if (typeof multiple !== typeof undefined && multiple !== false) {
                    ele.attr('name', holder_name+'['+multi_input_blockCount+']['+multi_input_name+']['+nested_multi_input_block_count+']['+ele_name+'][]');
                } else {
                    ele.attr('name', holder_name+'['+multi_input_blockCount+']['+multi_input_name+']['+nested_multi_input_block_count+']['+ele_name+']');
                }
                ele.attr('id', holder_id+'_'+multi_input_name+'_'+multi_input_blockCount + '_' + ele_name + '_' + nested_multi_input_block_count);
            }
        });

        addMultiInputProperty(nested_multi_input);

        if(nested_multi_input.children('.multi_input_block').children('.multi_input_holder').length > 0) nested_multi_input.children('.multi_input_block').css('padding-bottom', '40px');

        nested_multi_input.children('.multi_input_block').children('.multi_input_holder').each(function() {
            setNestedMultiInputIndex($(this), holder_id+'_'+multi_input_name+'_0', holder_name+'['+multi_input_blockCount+']['+multi_input_name+']', 0);
        });
    }

    $('#shipping-options').change(function () {
        var shipping_option = $(this).val();
        console.log(shipping_option);
        if (shipping_option == 'distance_by_shipping') {
            $('#mvx-vendor-shipping-by-distance-section').show();
            $('#mvx-vendor-shipping-by-zone-section').hide();
            $('#mvx-vendor-shipping-by-country-section').hide();
        } else if (shipping_option == 'distance_by_zone') {
            $('#mvx-vendor-shipping-by-distance-section').hide();
            $('#mvx-vendor-shipping-by-zone-section').show();
            $('#mvx-vendor-shipping-by-country-section').hide();
        } else if (shipping_option == 'shipping_by_country') {
            $('#mvx-vendor-shipping-by-distance-section').hide();
            $('#mvx-vendor-shipping-by-country-section').show();
            $('#mvx-vendor-shipping-by-zone-section').hide();
        } else {}
    });

    if ($('#shipping-options').val() == 'distance_by_shipping') {
        $('#mvx-vendor-shipping-by-distance-section').show();
        $('#mvx-vendor-shipping-by-zone-section').hide();
        $('#mvx-vendor-shipping-by-country-section').hide();
    } else if ($('#shipping-options').val() == 'distance_by_zone') {
        $('#mvx-vendor-shipping-by-distance-section').hide();
        $('#mvx-vendor-shipping-by-zone-section').show();
        $('#mvx-vendor-shipping-by-country-section').hide();
    } else if ($('#shipping-options').val() == 'shipping_by_country') {
        $('#mvx-vendor-shipping-by-distance-section').hide();
        $('#mvx-vendor-shipping-by-zone-section').hide();
        $('#mvx-vendor-shipping-by-country-section').show();
    } else {}
    
    if ($('#is_submit_product').is(':checked')) {
        $('#is_published_product').closest("tr").show();
        $('#is_edit_delete_published_product').closest("tr").show();
    } else {
        $('#is_published_product').closest("tr").css("display", "none");
        $('#is_edit_delete_published_product').closest("tr").css("display", "none");
    }
    
    $('#is_submit_product').change(function () {
        if ($(this).is(':checked')) {
            $('#is_published_product').closest("tr").show();
            $('#is_edit_delete_published_product').closest("tr").show();
        } else {
            $('#is_published_product').closest("tr").css("display", "none");
            $('#is_edit_delete_published_product').closest("tr").css("display", "none");
        }
    });
    
    if ($('#is_submit_coupon').is(':checked')) {
        $('#is_published_coupon').closest("tr").show();
        $('#is_edit_delete_published_coupon').closest("tr").show();
    } else {
        $('#is_published_coupon').closest("tr").css("display", "none");
        $('#is_edit_delete_published_coupon').closest("tr").css("display", "none");
    }
    
    $('#is_submit_coupon').change(function () {
        if ($(this).is(':checked')) {
            $('#is_published_coupon').closest("tr").show();
            $('#is_edit_delete_published_coupon').closest("tr").show();
        } else {
            $('#is_published_coupon').closest("tr").css("display", "none");
            $('#is_edit_delete_published_coupon').closest("tr").css("display", "none");
        }
    });
    

    if ($('#mvx_disbursal_mode_vendor').is(':checked')) {
        $('#commission_transfer').closest("tr").show();
        $('#no_of_orders').closest("tr").show();
        $('.withdrawl_order_status').show();
    } else {
        $('#commission_transfer').closest("tr").css("display", "none");
        $('#no_of_orders').closest("tr").css("display", "none");
        $('.withdrawl_order_status').css("display", "none");        
    }
    
    if ($('#mvx_disbursal_mode_admin').is(':checked')) {
        $('#payment_schedule').closest("tr").show();
    } else {
        $('#payment_schedule').closest("tr").css("display", "none");
    }

    if ($('#testmode').is(':checked')) {
        $('#test_client_id').closest("tr").show();
        $('#test_publishable_key').closest("tr").show();
        $('#test_secret_key').closest("tr").show();
        $('#live_client_id').closest("tr").hide();
        $('#live_publishable_key').closest("tr").hide();
        $('#live_secret_key').closest("tr").hide();
    } else {
        $('#test_client_id').closest("tr").hide();
        $('#test_publishable_key').closest("tr").hide();
        $('#test_secret_key').closest("tr").hide();
        $('#live_client_id').closest("tr").show();
        $('#live_publishable_key').closest("tr").show();
        $('#live_secret_key').closest("tr").show();
    }

    $('#testmode').change(function () {
        if ($(this).is(':checked')) {
            $('#test_client_id').closest("tr").show();
            $('#test_publishable_key').closest("tr").show();
            $('#test_secret_key').closest("tr").show();
            $('#live_client_id').closest("tr").hide();
            $('#live_publishable_key').closest("tr").hide();
            $('#live_secret_key').closest("tr").hide();
        } else {
            $('#test_client_id').closest("tr").hide();
            $('#test_publishable_key').closest("tr").hide();
            $('#test_secret_key').closest("tr").hide();
            $('#live_client_id').closest("tr").show();
            $('#live_publishable_key').closest("tr").show();
            $('#live_secret_key').closest("tr").show();
        }
    });

    $('#mvx_disbursal_mode_vendor').change(function () {
        if ($(this).is(':checked')) {
            $('#commission_transfer').closest("tr").show();
            $('#no_of_orders').closest("tr").show();
            $('.withdrawl_order_status').show();
        } else {
            $('#commission_transfer').closest("tr").css("display", "none");
            $('#no_of_orders').closest("tr").css("display", "none");
            $('.withdrawl_order_status').css("display", "none");
        }
    });
    // toggle check uncheck event on gatewar charge

    $('#payment_gateway_charge').change(function () {
        if ($(this).prop('checked')) {
            $('.payment_gateway_charge').show();
            $('#payment_gateway_charge_type').closest('tr').show();
            $('#gateway_charges_cost_carrier').closest('tr').show();
        } else {
            $('.payment_gateway_charge').hide();
            $('#payment_gateway_charge_type').closest('tr').hide();
            $('#gateway_charges_cost_carrier').closest('tr').hide();
        }
    }).change();

    $('#commission_include_couponn').change(function () {
        if ($(this).prop('checked')) {
            $('#admin_coupon_excluded').closest('tr').show();
        } else {
            $('#admin_coupon_excluded').closest('tr').hide();
        }
    }).change();
    
    $( "input[name^='mvx_payment_settings_name[gateway_charge_fixed_with_']" ).closest('tr').hide();
    $('#payment_gateway_charge_type').on('change', function(){
        var charge_type = $(this).val();
        if (charge_type == 'fixed_with_percentage') {
            $('.automatic_payment_method').each(function(){
                var id = $(this).attr('id');
                if (id !== undefined) {
                    var terget_id = 'gateway_charge' + id.split('payment_method')[1];
                    var terget_fixed_id = 'gateway_charge_fixed_with' + id.split('payment_method')[1];
                    if($(this).is(':checked') && $('#payment_gateway_charge').prop('checked')){
                        $('#' + terget_id).closest('tr').show();
                        $('#' + terget_id).attr('placeholder', mvx_admin_js_script_data.lang.in_percentage);
                        //$('#' + terget_id).siblings('.description').html($('#' + terget_id).siblings('.description').html()+' '+mvx_admin_js_script_data.lang.in_percentage);
                        $('#' + terget_fixed_id).closest('tr').show();
                        $('#' + terget_fixed_id).attr('placeholder', mvx_admin_js_script_data.lang.in_fixed);
                        //$('#' + terget_fixed_id).siblings('.description').html($('#' + terget_fixed_id).siblings('.description').html()+' '+mvx_admin_js_script_data.lang.in_fixed);
                    }else{
                        $('#' + terget_id).closest('tr').hide();
                        $('#' + terget_fixed_id).closest('tr').hide();
                    }
                }
            });
            
        } else {
            $('.automatic_payment_method').each(function(){
                var id = $(this).attr('id');
                if (id !== undefined) {
                    var terget_id = 'gateway_charge' + id.split('payment_method')[1];
                    var terget_fixed_id = 'gateway_charge_fixed_with' + id.split('payment_method')[1];
                    $('#' + terget_fixed_id).closest('tr').hide();
                    $('#' + terget_fixed_id).attr('placeholder', '');
                    $('#' + terget_id).attr('placeholder', '');
                    $('#' + terget_id).siblings('.description').html($('#' + terget_id).siblings('.description').html()+' '+ '');
                    $('#' + terget_fixed_id).siblings('.description').html($('#' + terget_fixed_id).siblings('.description').html()+' '+'');

                }
            });
        }
    }).trigger('change');

    $('.automatic_payment_method').change(function () {
        var id = $(this).attr('id');
        if (id !== undefined) {
            var terget_id = 'gateway_charge' + id.split('payment_method')[1];
            if ($(this).is(':checked') && $('#payment_gateway_charge').prop('checked')) {
                $('#' + terget_id).closest('tr').show();
            } else {
                $('#' + terget_id).closest('tr').hide();
            }
        }
    }).change();
    
    // For color palet
    $('#vendor_color_scheme_picker input[type=radio]').on('change', function (){
        $('#vendor_color_scheme_picker .color-option').removeClass('selected');
        $(this).closest('div').addClass('selected');
    });
    // end
    
    // Vendor Management Tab
    $("#vendor_preview_tabs").tabs();

    var getHasLoc;
    
    $('body').on("click", "#vendor_preview_tabs a.ui-tabs-anchor", function(e) {
        location.hash = '/' + $(this).attr("id");        
    });
    if (location.hash) {
        getHasLoc = location.hash.replace('#/', '');        
        $("#vendor_preview_tabs a[id='" + getHasLoc + "']").click();
    }
    
    // Disable buttons for application archive tab
    $('#vendor_preview_tabs').click('#vendor-application', function (event, ui) {
            $vendor_type = $("#vendor-application").data( 'vendor-type' );
            if($vendor_type) {
                var selectedTabIndex= $("#vendor_preview_tabs").tabs('option', 'active');
                if(selectedTabIndex == 4) $("#wc-backbone-modal-dialog").hide();
                else $("#wc-backbone-modal-dialog").show();
            }
    });
    
    $('#vendor_payment_mode').on('change', function () {
        $('.payment-gateway').hide();
        $('.payment-gateway-' + $(this).val()).show();
        if ($(this).val() == 'all_above_split_payment') {
            $.each(mvx_admin_js_script_data.multi_split_payment_options, function (key , val){ 
                $('.payment-gateway-' + val).show();
                $('.payment-gateway-' + val).append('<br>');
            });
        }
    }).change();
    
    $('.vendor-preview').click(function() {
        var $previewButton    = $( this ),
            $vendor_id         = $previewButton.data( 'vendor-id' );
            
        if ( $previewButton.data( 'vendor-data' ) ) {
            $( this ).WCBackboneModal({
                template: 'mvx-modal-view-vendor',
                variable : $previewButton.data( 'vendor-data' )
            });
        } else {
            $previewButton.addClass( 'disabled' );

            $.ajax({
                url:     mvx_admin_js_script_data.ajax_url,
                data:    {
                    vendor_id: $vendor_id,
                    action  : 'mvx_get_vendor_details',
                    nonce: mvx_admin_js_script_data.vendors_nonce
                },
                type:    'GET',
                success: function( response ) {
                    $( '.vendor-preview' ).removeClass( 'disabled' );
                    if ( response.success ) {
                        $previewButton.data( 'vendor-data', response.data );

                        $( this ).WCBackboneModal({
                            template: 'mvx-modal-view-vendor',
                            variable : response.data
                        });
                    }
                }
            });
        }
        return false;
    });
    
    $( document.body ).on('click', '#wc-backbone-modal-dialog .mvx-action-button', function(e) {
        e.preventDefault();
        $('.mvx-loader').html('<span class="dashicons dashicons-image-rotate"></span>');
        var $actionButton    = $( this ),
            $vendor_id       = $actionButton.data( 'vendor-id' ),
            $vendor_action   = $actionButton.data( 'ajax-action' );
            $pending_vendor_note = $actionButton.closest( '.mvx-vendor-modal-main' ).find( '.pending-vendor-note' ).val();
            $note_author_id = $actionButton.closest( '.mvx-vendor-modal-main' ).find( '.pending-vendor-note' ).data( 'note-author-id' );
            
        if(typeof($vendor_id) != "undefined" && $vendor_id !== null && $vendor_id > 0) {
            $.ajax({
                url:  mvx_admin_js_script_data.ajax_url,
                data: {
                    user_id: $vendor_id,
                    action : $vendor_action,
                    redirect: true,
                    custom_note: $pending_vendor_note,
                    note_by: $note_author_id
                },
                type: 'POST',
                success: function( response ) {
                    $('.mvx-loader').html('');
                    if(response.redirect) window.location = response.redirect_url;
                }
            });
        }
    });

    $('.mvx-widget-vquick-info-captcha-wrap').hide();
    $('.mvx-widget-vquick-info-captcha-type').hide();
    $('.mvx-widget-vquick-info-captcha-wrap.v2').hide();
    $(document).on( 'click', '.mvx-widget-enable-grecaptcha', function () { 
        if ($(this).is(':checked')) {
            $('.mvx-widget-vquick-info-captcha-type').show();
            $('.mvx-widget-vquick-info-captcha-wrap.v2').show();
        } else {
            $('.mvx-widget-vquick-info-captcha-type').hide();
            $('.mvx-widget-vquick-info-captcha-wrap.v2').hide();
        }
    });

    $(document).on('change', '.mvx-widget-vquick-info-captcha-type select', function () { 
        if ($(this).val() == 'v2') {
            $('.mvx-widget-vquick-info-captcha-wrap.v2').show();
            $('.mvx-widget-vquick-info-captcha-wrap.v3').hide();
        }else{
            $('.mvx-widget-vquick-info-captcha-wrap.v2').hide();
            $('.mvx-widget-vquick-info-captcha-wrap.v3').show();
        }
    }).trigger('change');

    $( '#mvx_vendor_submit_commission' ).click(function(event) {
        event.preventDefault();
        $('#mvx_vendor_submit_commission').prop("disabled", true);
        $('#mvx_vendor_submit_commission').text(mvx_admin_js_script_data.submiting);

        $.ajax({
            url:     mvx_admin_js_script_data.ajax_url,
            data:    {
                action: 'commission_variation',
                mvx_settings_form   : $('.mvx_vendors_settings').serialize(),
            },
            type:    'POST',
            success: function( response ) {
                $('#mvx_vendor_submit_commission').prop("disabled", false);// enable button after getting respone
                $('#mvx_vendor_submit_commission').text(mvx_admin_js_script_data.update);
            }
        });
    });

    // Review settings
    $( '#mvx_vendor_review_setting' ).click(function(event) {
        event.preventDefault();
        $('#mvx_vendor_review_setting').prop("disabled", true);
        $('#mvx_vendor_review_setting').val(mvx_admin_js_script_data.submiting);

        $.ajax({
            url:     mvx_admin_js_script_data.ajax_url,
            data:    {
                action: 'admin_review_setting',
                mvx_review_settings_form   : $('.mvx_vendors_settings').serialize(),
            },
            type:    'POST',
            success: function( response ) {
                $('#mvx_vendor_review_setting').prop("disabled", false);// enable button after getting respone
                $('#mvx_vendor_review_setting').val(mvx_admin_js_script_data.update);
            }
        });
    });
});