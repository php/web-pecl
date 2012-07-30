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

    var $win = $(window);
    $body = $('body')
        , $nav = $('.navbar')
        , navTop = $('.navbar').length && $('.navbar').offset().top
        , isFixed = 0;

        checkResponsive();

        $win.on('resize', checkResponsive);

        function checkResponsive() {
            if ($win.width() < 979) {
                $body.removeClass('hasFixedNavbar');
                $nav.addClass('navbar-fixed-top');
                $body.addClass('hasFixedNavbarResponsive');
                $win.off('scroll', processScroll);
            } else {
                $body.removeClass('navbar-fixed-top');
                $body.removeClass('hasFixedNavbarResponsive');
                navTop = $('.navbar').length && $('.navbar').offset().top;
                processScroll();
                $win.on('scroll', processScroll);
            }
        }

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