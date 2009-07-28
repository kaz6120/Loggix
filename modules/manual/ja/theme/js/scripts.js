/**
 * JavaScript for Loggix
 *
 * @since   5.6.7
 * @version 9.3.16
 */
 /*

/**
 * onload event
 */
$(document).ready(function(){
    $("#admin-dir").click(function () {
        if ($("#admin-files").is(":hidden")) {
            $("#admin-files").slideDown("fast");
        } else {
            $("#admin-files").slideUp("fast");
        }
    });
    $("#data-dir").click(function () {
        if ($("#data-files").is(":hidden")) {
            $("#data-files").slideDown("fast");
        } else {
            $("#data-files").slideUp("fast");
        }
    });
    $("#lang-dir").click(function () {
        if ($("#lang-files").is(":hidden")) {
            $("#lang-files").slideDown("fast");
        } else {
            $("#lang-files").slideUp("fast");
        }
    });
    $("#lib-dir").click(function () {
        if ($("#lib-files").is(":hidden")) {
            $("#lib-files").slideDown("fast");
        } else {
            $("#lib-files").slideUp("fast");
        }
    });
    $("#loggix-dir").click(function () {
        if ($("#loggix-files").is(":hidden")) {
            $("#loggix-files").slideDown("fast");
        } else {
            $("#loggix-files").slideUp("fast");
        }
    });
    $("#module-dir").click(function () {
        if ($("#module-files").is(":hidden")) {
            $("#module-files").slideDown("fast");
        } else {
            $("#module-files").slideUp("fast");
        }
    });
    $("#view-dir").click(function () {
        if ($("#view-files").is(":hidden")) {
            $("#view-files").slideDown("fast");
        } else {
            $("#view-files").slideUp("fast");
        }
    });
    $("#php-dir").click(function () {
        if ($("#php-files").is(":hidden")) {
            $("#php-files").slideDown("fast");
        } else {
            $("#php-files").slideUp("fast");
        }
    });
    $("#modules-dir").click(function () {
        if ($("#modules-files").is(":hidden")) {
            $("#modules-files").slideDown("fast");
        } else {
            $("#modules-files").slideUp("fast");
        }
    });
    $("#plugins-dir").click(function () {
        if ($("#plugins-files").is(":hidden")) {
            $("#plugins-files").slideDown("fast");
        } else {
            $("#plugins-files").slideUp("fast");
        }
    });
    $("#theme-dir").click(function () {
        if ($("#theme-files").is(":hidden")) {
            $("#theme-files").slideDown("fast");
        } else {
            $("#theme-files").slideUp("fast");
        }
    });
});


