jQuery(document).ready(function ($) {
    'use strict';
    $('.jlt-location-control').each(function (index) {
        $(this).change(function () {
            location_get_sub($(this));
        });

    });

    function location_get_sub(parent) {
        var parent_value = parent.val();
        var location_type = parent.attr('name');
        var next = parent.next();
        $.post(
            JLT_Location.ajaxurl,
            {
                action: 'jlt_adv_location_ajax_get_child',
                parent: parent_value,
                location_type: location_type,

            }, function (respon) {
                next.prop('disabled', true);
                next.html(respon);
                next.prop('disabled', false);
            });
    }

});