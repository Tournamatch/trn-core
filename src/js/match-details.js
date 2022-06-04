/**
 * Handles the click events for the match details page.
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
        let options = trn_match_details_options;

        $.event('match').addEventListener('deleted', function() {
            window.location.href = options.redirect_link;
        });

    }, false);
})(trn);