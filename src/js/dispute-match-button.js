/**
 * Handles client scripting for the dispute match button.
 *
 * @link       https://www.tournamatch.com
 * @since      3.19.0
 *
 * @package    Tournamatch
 *
 */
import { trn } from './tournamatch.js';

(function ($) {
    'use strict';

    window.addEventListener('load', function () {
        let options = trn_dispute_match_button_options;

        // dispute button
        function disputeMatch(matchId) {
            let xhr = new XMLHttpRequest();
            xhr.open('POST', options.api_url + 'match-disputes');
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
            xhr.onload = function () {
                if (xhr.status === 201) {
                    $.event('match').dispatchEvent(new CustomEvent('disputed', { detail: { match_id: matchId } } ));
                } else {
                    $.event('match').dispatchEvent(new CustomEvent('error', { detail: xhr.response } ));
                }
            };

            xhr.send($.param({
                id: matchId,
            }));
        }

        let matchDisputeButtons = document.getElementsByClassName('trn-dispute-match-button');
        Array.prototype.forEach.call(matchDisputeButtons, function(disputeLink) {
            disputeLink.addEventListener('click', function (e) {
                e.preventDefault();
                disputeMatch(this.dataset.matchId);
            }, false);
        });
    }, false);
})(trn);