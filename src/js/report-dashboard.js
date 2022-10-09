/**
 * Handles client scripting for the report dashbaord page.
 *
 * @link       https://www.tournamatch.com
 * @since      3.11.0
 *
 * @package    Tournamatch
 *
 */
import { trn } from './tournamatch.js';

(function ($) {
    'use strict';

    window.addEventListener('load', function () {
        let options = trn_report_dashboard_options;

        $.event('match').addEventListener('deleted', function() {
            window.location.href = options.redirect_link;
        });

        $.event('match').addEventListener('disputed', function(data) {
            window.location.href = options.redirect_link + `?&dispute_match_id=${data.match_id}`;
        });

    }, false);
})(trn);