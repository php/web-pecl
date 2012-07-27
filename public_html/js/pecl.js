/* Global Action */
$(document).ready(function () {
    $(".chzn-select").chosen({allow_single_deselect: true, no_results_text: "No results matched"});

    $('.datepicker').datepicker();

    $('.focus').focus();

    if ($('#frmStatistics').length) {
        $('.chzn-select').chosen().change(function() {
            $('#frmStatistics').submit();
        });
    }

// fix sub nav on scroll
    var $win = $(window)
        $body = $('body')
        , $nav = $('.navbar')
        , navTop = $('.navbar').length && $('.navbar').offset().top
        , isFixed = 0;

    processScroll();

    // hack sad times - holdover until rewrite for 2.1
    $win.on('scroll', processScroll);

    function processScroll() {
        var i, scrollTop = $win.scrollTop()
        if (scrollTop >= navTop && !isFixed) {
            isFixed = 1;
            $nav.addClass('navbar-fixed-top');
            $body.addClass('hasFixedNavbar');

        } else if (scrollTop <= navTop && isFixed) {
            isFixed = 0;
            $nav.removeClass('navbar-fixed-top');
            $body.removeClass('hasFixedNavbar');
        }
    }
});