(function ($) {
    $(function () {
        'use strict';

        // Add Color Picker to all inputs that have 'color-field' class
        $('.mct-color-picker').wpColorPicker();

        $('.mct-repeater').repeater(
            {

                show: function () {
                    $(this).slideDown();

                    $.each($(this).find('.wp-picker-container'), function (index, elem) {
                        var field = $(elem).find('.mct-color-picker').clone();
                        $(elem).before(field);
                    });
                    $(this).find('.wp-picker-container').remove();
                    $('.mct-color-picker').wpColorPicker();
                    init_repeator_dependencies();
                },
                hide: function (deleteElement) {
                    if (confirm('Are you sure you want to delete this element?')) {

                        $(this).slideUp(deleteElement);

                    }
                },
                ready: function (setIndexes) {

                }
            }
        );

        // The "Upload" button
        $('.mct_upload_image_button').click(function () {

            var send_attachment_bkp = wp.media.editor.send.attachment;
            var button = $(this);

            wp.media.editor.send.attachment = function (props, attachment) {
                $(button).parent().prev().attr('src', attachment.url);
                $(button).prev().val(attachment.id);
                wp.media.editor.send.attachment = send_attachment_bkp;
            }
            wp.media.editor.open(button);
            return false;
        });

        // The "Remove" button (remove the value from input type='hidden')
        $('.mct_remove_image_button').click(function () {
            var answer = confirm('Are you sure?');
            if (answer == true) {
                var src = $(this).parent().prev().attr('data-src');
                $(this).parent().prev().attr('src', src);
                $(this).prev().prev().val('');
            }
            return false;
        });


        $('body').on('click', '.mct-sections a', function (event) {
            event.preventDefault();
            $('.mct-section-wrapper').hide();
            $('.mct-section-content').hide();
            $($(this).attr('href')).show();
            var new_url = removeURLParams('tab');
            window.history.replaceState('', '', new_url);
            window.history.replaceState('', '', updateURLParameter(window.location.href, "section", $(this).attr('href')));

        });

        $('body').on('click', '.mct-back-btn', function (event) {
            event.preventDefault();
            $('.mct-section-content').hide();
            $('.mct-section-wrapper').show();
        });

        $('body').on('click', '.mct-tabs a', function (event) {
            event.preventDefault();
            $(this).closest('.mct-section-content').find('.mct-tab-content').hide();
            $(this).closest('.mct-section-content').find('.mct-tabs a').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            $($(this).attr('href')).show();
            window.history.replaceState('', '', updateURLParameter(window.location.href, "tab", $(this).attr('href')));
        });

        $('body').on('click', '.mct-copy-btn', function (event) {
            event.preventDefault();
            var textBox = $(this).parent().find('.mct-copy-text');
            textBox.select();
            document.execCommand("copy");
        });

        function updateURLParameter(url, param, paramVal) {
            var newAdditionalURL = "";
            var tempArray = url.split("?");
            var baseURL = tempArray[0];
            var additionalURL = tempArray[1];
            var temp = "";
            if (additionalURL) {
                tempArray = additionalURL.split("&");
                for (var i = 0; i < tempArray.length; i++) {
                    if (tempArray[i].split('=')[0] != param) {
                        newAdditionalURL += temp + tempArray[i];
                        temp = "&";
                    }
                }
            }

            var rows_txt = temp + "" + param + "=" + paramVal.replace('#', '');
            return baseURL + "?" + newAdditionalURL + rows_txt;
        }

        function removeURLParams(sParam) {
            var url = window.location.href.split('?')[0] + '?';
            var sPageURL = decodeURIComponent(window.location.search.substring(1)),
                sURLVariables = sPageURL.split('&'),
                sParameterName,
                i;

            for (i = 0; i < sURLVariables.length; i++) {
                sParameterName = sURLVariables[i].split('=');
                if (sParameterName[0] != sParam) {
                    url = url + sParameterName[0] + '=' + sParameterName[1] + '&'
                }
            }
            return url.substring(0, url.length - 1);
        }

        // Handle dependencies.
        function dependencies_handler(deps, values) {
            var result = true;
            //Single dependency
            if (typeof (deps) == 'string') {

                var input_type = $(deps).data('type'),
                    val = $(deps).val();

                if ('checkbox' === input_type) {
                    val = $(deps).is(':checked') ? '1' : '0';
                } else if ('radio' === input_type) {
                    val = $(deps).find('input[type="radio"]').filter(':checked').val();
                } else if ('checkbox-group' === input_type) {
                    var val = [];
                    $(deps).find('input[type="checkbox"]:checked').each(function () {
                        val.push($(this).val());
                    });

                }

                values = values.split(',');

                for (var i = 0; i < values.length; i++) {
                    if ($.isArray(val)) {
                        if (val.includes(values[i])) {
                            result = true;
                            break;
                        } else {
                            result = false;
                        }
                    } else {

                        if (val !== values[i]) {
                            result = false;
                        } else {
                            result = true;
                            break;
                        }
                    }

                }
            }


            return result;

        }

        function init_repeator_dependencies() {
            $('[data-repdeps]:not( .deps-initialized )').each(function () {
                var t = $(this);
                var field = t.closest('.row-options');
                // init field deps
                t.addClass('deps-initialized');

                var deps = '#' + t.data('repdeps'),
                    value = t.data('deps-value'),
                    wrapper = t.closest('.row-options');

                $(deps).on('change', function () {
                    var showing = dependencies_handler(deps, value.toString());
                    if (showing) {
                        field.show(300);
                    } else {
                        field.hide(300);
                    }
                }).trigger('change');
            });
        }

        function init_dependencies() {
            $('[data-deps]:not( .deps-initialized )').each(function () {
                var t = $(this),
                    field = t.closest('.row-options'),
                    items = $.isArray(t.data('deps')) ? t.data('deps') : [t.data('deps')];

                // init field deps
                t.addClass('deps-initialized');
                $.each(items, function (index, data) {

                    $('#' + data.id).on('change', function () {
                        var showing = true;
                        $.each(items, function (i, d) {
                            showing = (true === dependencies_handler('#' + d.id, d.value) && showing);
                        });
                        if (showing) {
                            field.show(300);
                        } else {
                            field.hide(300);
                        }

                    }).trigger('change');


                });
            });
        }

        init_dependencies();
        init_repeator_dependencies();
    });
})(jQuery);
