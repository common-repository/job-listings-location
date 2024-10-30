jQuery(document).ready(function ($) {
    'use strict';
    $('.jlt-admin-location-select').each(function (index) {
        $(this).change(function () {
            location_get_sub($(this));
        });

    });

    function location_get_sub(parent) {
        var parent_value = parent.val();
        var next = parent.next().next();
        $.post(
            JLT_Location.ajaxurl,
            {
                action: 'jlt_adv_location_ajax_get_child',
                parent: parent_value,

            }, function (respon) {
                next.prop('disabled', true);
                next.html(respon);
                next.prop('disabled', false);
            });
    }

});