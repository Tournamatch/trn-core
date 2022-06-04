(function($){
    function convert_time(format, value) {
        var time = moment.utc(value).local();
        return time.format(format);
    }

    $('#challenge_id option').each(function(i, e) {
        if ($(e).val() != '-1') {
            $(e).text(convert_time('lll', $(e).text()));
        }
    });
}( jQuery ));