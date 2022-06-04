/**
 * Handles the click event for deleting a match.
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
        let options = trn_delete_match_options;

        let targets = document.getElementsByClassName('trn-delete-match-action');
        Array.prototype.forEach.call(targets, function (target) {
            target.addEventListener('trn.confirmed.action', function (event) {
                event.preventDefault();

                console.log(`modal was confirmed for link ${target.dataset.matchId}`);
                let xhr = new XMLHttpRequest();
                xhr.open('DELETE', `${options.api_url}matches/${target.dataset.matchId}`);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.setRequestHeader('X-WP-Nonce', options.rest_nonce);
                xhr.onload = function () {
                    if (xhr.status === 204) {
                        $.event('match').dispatchEvent(new Event('deleted'));
                    } else {
                        let response = JSON.parse(xhr.response);
                        document.getElementById('trn-delete-match-response').innerHTML = `<div class="alert alert-danger"><strong>${options.language.failure}</strong>: ${response.message}</div>`;
                    }
                };

                xhr.send();
            });
        }, false);
    }, false);
})(trn);