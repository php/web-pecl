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
});